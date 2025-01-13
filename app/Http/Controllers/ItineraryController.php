<?php

namespace App\Http\Controllers;

use App\Models\Destination;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

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
            ]);

            $latitude = $validatedData['latitude'];
            $longitude = $validatedData['longitude'];
            $availableTime = $validatedData['hours'] * 60; // Convert hours to minutes

            // Fetch all destinations
            $destinations = Destination::all();

            if ($destinations->isEmpty()) {
                return response()->json(['error' => 'No destinations available.'], 400);
            }

            $itinerary = [];
            $timeSpent = 0;
            $foodStopAdded = false;

            while ($timeSpent < $availableTime && $destinations->isNotEmpty()) {
                $isMidpoint = ($timeSpent >= $availableTime / 2) && !$foodStopAdded;

                // Get a cluster of nearby destinations
                $cluster = $this->getClusterOfDestinations($latitude, $longitude, $destinations);

                if ($cluster->isEmpty()) {
                    break; // Stop if no cluster is found
                }

                // Prioritize a restaurant if it's the midpoint
                $nextDestination = null;
                if ($isMidpoint) {
                    $restaurants = $cluster->where('type', 'restaurant');
                    if ($restaurants->isNotEmpty()) {
                        $nextDestination = $this->getClosestDestinationFromGoogle($latitude, $longitude, $restaurants);
                        $foodStopAdded = true;
                    }
                }

                // If no restaurant is chosen, prioritize landmarks
                if (!$nextDestination) {
                    $landmarks = $cluster->where('type', 'landmark');
                    $nextDestination = $this->getClosestDestinationFromGoogle($latitude, $longitude, $landmarks);
                }

                if (!$nextDestination) {
                    break; // No valid next destination found
                }

                $travelTime = $nextDestination['duration'] / 60; // Convert seconds to minutes
                $visitTime = $nextDestination['destination']->type === 'restaurant' ? 40 : 20;

                if ($timeSpent + $travelTime + $visitTime > $availableTime) {
                    break; // Stop if adding this destination exceeds the available time
                }

                // Add the destination to the itinerary
                $itinerary[] = $this->addToItinerary($nextDestination['destination'], $travelTime, $visitTime);

                // Update time spent and current location
                $timeSpent += $travelTime + $visitTime;
                $latitude = $nextDestination['destination']->latitude;
                $longitude = $nextDestination['destination']->longitude;

                // Remove the visited destination from the list
                $destinations = $destinations->reject(fn($d) => $d->id === $nextDestination['destination']->id)->values();
            }

            return response()->json($itinerary);

        } catch (\Exception $e) {
            \Log::error('Error Generating Itinerary:', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'An error occurred while generating the itinerary.'], 500);
        }
    }

    private function getClusterOfDestinations($latitude, $longitude, $destinations, $maxDistance = 5.0)
    {
        return $destinations->filter(function ($destination) use ($latitude, $longitude, $maxDistance) {
            $distance = $this->calculateDistance($latitude, $longitude, $destination->latitude, $destination->longitude);
            return $distance <= $maxDistance; // Filter destinations within the max distance (in km)
        })->values();
    }

    private function getClosestDestinationFromGoogle($latitude, $longitude, $destinations)
    {
        $apiKey = env('GOOGLE_MAPS_API_KEY');
        $url = 'https://maps.googleapis.com/maps/api/directions/json';

        $closestDestination = null;
        $shortestDuration = PHP_INT_MAX;

        foreach ($destinations as $destination) {
            try {
                // Call Google Directions API
                $response = Http::get($url, [
                    'origin' => "{$latitude},{$longitude}",
                    'destination' => "{$destination->latitude},{$destination->longitude}",
                    'key' => $apiKey,
                ]);

                if ($response->successful()) {
                    $data = $response->json();

                    if (!empty($data['routes'])) {
                        $duration = $data['routes'][0]['legs'][0]['duration']['value']; // Duration in seconds

                        if ($duration < $shortestDuration) {
                            $shortestDuration = $duration;
                            $closestDestination = [
                                'destination' => $destination,
                                'duration' => $duration,
                            ];
                        }
                    } else {
                        \Log::error('Google API returned no routes:', ['response' => $data]);
                    }
                } else {
                    \Log::error('Google API Error:', [
                        'status' => $response->status(),
                        'response' => $response->body(),
                    ]);
                }
            } catch (\Exception $e) {
                \Log::error('Error calling Google API:', ['error' => $e->getMessage()]);
            }
        }

        return $closestDestination;
    }

    private function calculateDistance($lat1, $lng1, $lat2, $lng2)
    {
        $earthRadius = 6371; // Earth radius in kilometers
        $latDelta = deg2rad($lat2 - $lat1);
        $lngDelta = deg2rad($lng2 - $lng1);

        $a = sin($latDelta / 2) ** 2 +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($lngDelta / 2) ** 2;

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return $earthRadius * $c; // Distance in kilometers
    }

    private function addToItinerary($destination, $travelTime, $visitTime)
    {
        return [
            'name' => $destination->name,
            'description' => $destination->description,
            'city' => $destination->city,
            'latitude' => $destination->latitude,
            'longitude' => $destination->longitude,
            'travel_time' => round($travelTime, 2),
            'time_to_spend' => $visitTime,
            'type' => $destination->type,
        ];
    }
}
