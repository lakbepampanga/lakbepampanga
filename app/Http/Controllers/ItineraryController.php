<?php

namespace App\Http\Controllers;

use App\Models\Destination;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\JeepneyRoute;
use App\Models\JeepneyStop;
use App\Models\FareStructure;
use App\Models\RouteSegment;
use App\Models\TrafficData;

class ItineraryController extends Controller
{
    public function generateItinerary(Request $request)
    {
        try {
            // Validate inputs
            $validatedData = $request->validate([
                'latitude' => 'required|numeric',
                'longitude' => 'required|numeric',
                'hours' => 'required|integer|min:1',
                'selected_location' => 'nullable|string',
            ]);

            $latitude = $validatedData['latitude'];
            $longitude = $validatedData['longitude'];
            $availableTime = $validatedData['hours'] * 60;

            if ($validatedData['selected_location']) {
                switch ($validatedData['selected_location']) {
                    case 'Angeles':
                        $latitude = 15.1347621;
                        $longitude = 120.5903796;
                        break;
                    case 'Mabalacat':
                        $latitude = 15.2443337;
                        $longitude = 120.5642501;
                        break;
                    case 'Magalang':
                        $latitude = 15.2144206;
                        $longitude = 120.6612414;
                        break;
                    case 'Clark':
                        $latitude = 15.1674883;
                        $longitude = 120.5801295;
                        break;
                    default:
                        break;
                }
            }

            // Fetch all destinations
            $destinations = Destination::all();

            if ($destinations->isEmpty()) {
                return response()->json(['error' => 'No destinations available.'], 400);
            }

            $itinerary = [];
            $timeSpent = 0;
            $foodStopsAdded = 0;
            $landmarkCount = 0;
            $maxLandmarksBeforeFoodStop = 3;

            while ($timeSpent < $availableTime && $destinations->isNotEmpty()) {
                $cluster = $this->getClusterOfDestinations($latitude, $longitude, $destinations);

                if ($cluster->isEmpty()) {
                    break;
                }

                $shouldAddFoodStop = $foodStopsAdded < floor($availableTime / 240);
                $filteredCluster = $cluster->filter(function ($destination) use ($shouldAddFoodStop, &$landmarkCount) {
                    if ($shouldAddFoodStop && $destination->type === 'restaurant' && $landmarkCount >= 3) {
                        return true;
                    }
                    return $destination->type === 'landmark';
                });

                $nextDestination = $this->getClosestDestinationFromGoogle($latitude, $longitude, $filteredCluster);

                if (!$nextDestination) {
                    break;
                }

                $travelTime = $nextDestination['duration'] / 60;
                $visitTime = $nextDestination['destination']->type === 'restaurant' ? 40 : 20;

                if ($timeSpent + $travelTime + $visitTime > $availableTime) {
                    break;
                }

                // Add the destination to the itinerary with commute instructions
                $itinerary[] = $this->addToItineraryWithCommute($nextDestination['destination'], $travelTime, $visitTime, $latitude, $longitude);

                $timeSpent += $travelTime + $visitTime;
                $latitude = $nextDestination['destination']->latitude;
                $longitude = $nextDestination['destination']->longitude;

                // Remove visited destination from all lists
                $destinations = $destinations->reject(fn($d) => $d->id === $nextDestination['destination']->id)->values();

                // Track added food stops
                if ($nextDestination['destination']->type === 'restaurant') {
                    $foodStopsAdded++;
                    $landmarkCount = 0;
                } else {
                    $landmarkCount++;
                }
            }

            return response()->json($itinerary);

        } catch (\Exception $e) {
            \Log::error('Error Generating Itinerary:', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'An error occurred while generating the itinerary. Please try again later.'], 500);
        }
    }

    // Function to get human-readable address from coordinates using Google Geocoding API
    private function getAddressFromCoordinates($latitude, $longitude)
    {
        $apiKey = env('GOOGLE_MAPS_API_KEY');
        $url = 'https://maps.googleapis.com/maps/api/geocode/json';

        try {
            $response = Http::get($url, [
                'latlng' => "{$latitude},{$longitude}",
                'key' => $apiKey,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                if (!empty($data['results'])) {
                    // Return the formatted address
                    return $data['results'][0]['formatted_address'];
                }
            }
        } catch (\Exception $e) {
            \Log::error('Error calling Google Geocoding API:', ['error' => $e->getMessage()]);
        }

        return null;  // Return null if the address cannot be fetched
    }

    // Function to get destinations within a certain distance from the user
    private function getClusterOfDestinations($latitude, $longitude, $destinations, $maxDistance = 5.0)
    {
        return $destinations->filter(function ($destination) use ($latitude, $longitude, $maxDistance) {
            $distance = $this->calculateDistance($latitude, $longitude, $destination->latitude, $destination->longitude);
            return $distance <= $maxDistance;
        })->values();
    }

    // Function to calculate travel time between the user's location and destination using Google Directions API
    private function getClosestDestinationFromGoogle($latitude, $longitude, $destinations)
    {
        $apiKey = env('GOOGLE_MAPS_API_KEY');
        $url = 'https://maps.googleapis.com/maps/api/directions/json';

        $closestDestination = null;
        $shortestDuration = PHP_INT_MAX;

        foreach ($destinations as $destination) {
            try {
                $response = Http::get($url, [
                    'origin' => "{$latitude},{$longitude}",
                    'destination' => "{$destination->latitude},{$destination->longitude}",
                    'key' => $apiKey,
                    'mode' => 'driving',  // You can change this to walking, bicycling, or transit if needed
                ]);

                if ($response->successful()) {
                    $data = $response->json();

                    if (!empty($data['routes'])) {
                        $duration = $data['routes'][0]['legs'][0]['duration']['value'];  // Duration in seconds

                        if ($duration < $shortestDuration) {
                            $shortestDuration = $duration;
                            $closestDestination = [
                                'destination' => $destination,
                                'duration' => $duration,
                            ];
                        }
                    }
                }
            } catch (\Exception $e) {
                \Log::error('Error calling Google Directions API:', ['error' => $e->getMessage()]);
            }
        }

        return $closestDestination;
    }

    // Function to add destination to itinerary along with commute instructions
    private function addToItineraryWithCommute($destination, $travelTime, $visitTime, $latitude, $longitude)
    {
        $startAddress = $this->getAddressFromCoordinates($latitude, $longitude);
        $endAddress = $this->getAddressFromCoordinates($destination->latitude, $destination->longitude);

        $routeName = "Route from {$startAddress} to {$endAddress}";
        $startStop = 'Start Location'; // Adjust based on your destination's first stop
        $endStop = $destination->name;  // Destination name

        return [
            'name' => $destination->name,
            'description' => $destination->description,
            'city' => $destination->city,
            'latitude' => $destination->latitude,
            'longitude' => $destination->longitude,
            'travel_time' => round($travelTime, 2),
            'time_to_spend' => $visitTime,
            'type' => $destination->type,
            'route_name' => $routeName,
            'start_stop' => $startStop,
            'end_stop' => $endStop,
            'estimated_travel_time' => $destination->travel_time,  // Assume this is available
            'commute_instructions' => "Take jeepney route {$routeName} from {$startStop} to {$endStop}. Estimated travel time: {$destination->travel_time} minutes."
        ];
    }

    // Function to calculate the distance between two points (in kilometers)
    private function calculateDistance($lat1, $lng1, $lat2, $lng2)
    {
        $earthRadius = 6371; // Earth's radius in kilometers
        $latDelta = deg2rad($lat2 - $lat1);
        $lngDelta = deg2rad($lng2 - $lng1);

        $a = sin($latDelta / 2) ** 2 +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($lngDelta / 2) ** 2;

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return $earthRadius * $c; // Returns distance in kilometers
    }
}
