<?php
namespace App\Http\Controllers;

use App\Models\Destination;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
            $validatedData = $request->validate([
                'latitude' => 'required|numeric',
                'longitude' => 'required|numeric',
                'hours' => 'required|integer|min:1',
                'selected_location' => 'nullable|string',
            ]);

            $latitude = $validatedData['latitude'];
            $longitude = $validatedData['longitude'];
            $availableTime = $validatedData['hours'] * 60;

            // Handle predefined starting locations
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
                    case 'Auf':
                        $latitude = 15.1453018;
                        $longitude = 120.5948856;
                            break;
                    default:
                        break;
                }
            }

            // Fetch destinations with valid coordinates
            $destinations = Destination::whereNotNull('latitude')
                ->whereNotNull('longitude')
                ->get();

            if ($destinations->isEmpty()) {
                return response()->json(['error' => 'No destinations available with valid coordinates.'], 400);
            }

            $itinerary = [];
            $timeSpent = 0;
            $foodStopsAdded = 0;
            $landmarkCount = 0;
            $maxLandmarksBeforeFoodStop = 3;
            $maxFoodStops = floor($availableTime / 240);

            while ($timeSpent < $availableTime && $destinations->isNotEmpty()) {
                $cluster = $this->getClusterOfDestinations($latitude, $longitude, $destinations);

                if ($cluster->isEmpty()) {
                    break;
                }

                $shouldAddFoodStop = $foodStopsAdded < $maxFoodStops && $landmarkCount >= $maxLandmarksBeforeFoodStop;
                $filteredCluster = $cluster->filter(function ($destination) use ($shouldAddFoodStop) {
                    if ($shouldAddFoodStop && $destination->type === 'restaurant') {
                        return true;
                    }
                    return $destination->type === 'landmark';
                });

                $nextDestination = $this->findClosestDestination($latitude, $longitude, $filteredCluster);

                if (!$nextDestination) {
                    break;
                }

                $travelTime = $this->getTravelTimeFromGoogle(
                    $latitude,
                    $longitude,
                    $nextDestination->latitude,
                    $nextDestination->longitude
                );

                $visitTime = $nextDestination->type === 'restaurant' ? 40 : 20;

                if ($timeSpent + $travelTime + $visitTime > $availableTime) {
                    break;
                }

                $itineraryItem = $this->addToItineraryWithCommute(
                    $nextDestination,
                    $travelTime,
                    $visitTime,
                    $latitude,
                    $longitude
                );

                // Include latitude and longitude explicitly in the response
                $itineraryItem['latitude'] = $nextDestination->latitude;
                $itineraryItem['longitude'] = $nextDestination->longitude;
                $itineraryItem['description'] = $nextDestination->description ?? 'No description available for this destination.';

                $itinerary[] = $itineraryItem;

                $timeSpent += $travelTime + $visitTime;

                $latitude = $nextDestination->latitude;
                $longitude = $nextDestination->longitude;

                $destinations = $destinations->reject(fn($d) => $d->id === $nextDestination->id);

                if ($nextDestination->type === 'restaurant') {
                    $foodStopsAdded++;
                    $landmarkCount = 0;
                } else {
                    $landmarkCount++;
                }
            }

            return response()->json($itinerary);

        } catch (\Exception $e) {
            \Log::error('Error Generating Itinerary:', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'An error occurred while generating the itinerary.'], 500);
        }
    }

    private function getTravelTimeFromGoogle($startLat, $startLng, $endLat, $endLng)
    {
        $apiKey = env('GOOGLE_MAPS_API_KEY');
        $url = "https://maps.googleapis.com/maps/api/directions/json?origin={$startLat},{$startLng}&destination={$endLat},{$endLng}&mode=driving&key={$apiKey}";

        $response = Http::get($url);

        if ($response->successful()) {
            $data = $response->json();
            if (!empty($data['routes'])) {
                $durationInSeconds = $data['routes'][0]['legs'][0]['duration']['value'] ?? 0;
                return ceil($durationInSeconds / 60);
            }
        }

        return 0;
    }
    private function findConnectingRoutes($startStop, $endStop)
    {
        // Query stops that are common between routes
        $connectingStops = DB::table('jeepney_stops AS s1')
            ->join('jeepney_stops AS s2', 's1.jeepney_route_id', '=', 's2.jeepney_route_id')
            ->where('s1.stop_name', $startStop)
            ->where('s2.stop_name', $endStop)
            ->select('s1.jeepney_route_id')
            ->get();
    
        return $connectingStops;
    }
    
    private function findClosestDestination($lat, $lng, $destinations) 
    {
        $closest = null;
        $minDistance = PHP_FLOAT_MAX;

        foreach ($destinations as $destination) {
            $distance = $this->calculateDistance($lat, $lng, 
                                               $destination->latitude, 
                                               $destination->longitude);
            if ($distance < $minDistance) {
                $minDistance = $distance;
                $closest = $destination;
            }
        }

        return $closest;
    }

    private function calculateDistance($lat1, $lng1, $lat2, $lng2)
    {
        $earthRadius = 6371;
        $latDelta = deg2rad($lat2 - $lat1);
        $lngDelta = deg2rad($lng2 - $lng1);

        $a = sin($latDelta / 2) ** 2 +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($lngDelta / 2) ** 2;

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return $earthRadius * $c;
    }

    private function getClusterOfDestinations($latitude, $longitude, $destinations, $maxDistance = 5.0)
    {
        return $destinations->filter(function ($destination) use ($latitude, $longitude, $maxDistance) {
            $distance = $this->calculateDistance($latitude, $longitude, $destination->latitude, $destination->longitude);
            return $distance <= $maxDistance;
        })->values();
    }
    

    private function getJeepneyRoute($startLat, $startLng, $destinationLat, $destinationLng)
    {
        $startStop = JeepneyStop::select(DB::raw('*, 
            (6371 * acos(cos(radians(?)) * cos(radians(latitude)) 
            * cos(radians(longitude) - radians(?)) + sin(radians(?)) * sin(radians(latitude)))) AS distance'))
            ->having('distance', '<', 2)
            ->orderBy('distance')
            ->setBindings([$startLat, $startLng, $startLat])
            ->first();

        $endStop = JeepneyStop::select(DB::raw('*, 
            (6371 * acos(cos(radians(?)) * cos(radians(latitude)) 
            * cos(radians(longitude) - radians(?)) + sin(radians(?)) * sin(radians(latitude)))) AS distance'))
            ->having('distance', '<', 2)
            ->orderBy('distance')
            ->setBindings([$destinationLat, $destinationLng, $destinationLat])
            ->first();

        if (!$startStop || !$endStop) {
            return null;
        }

        $route = JeepneyRoute::find($startStop->jeepney_route_id);

        if (!$route) {
            return null;
        }

        $travelTime = RouteSegment::where('jeepney_route_id', $route->id)
            ->where('start_stop_id', $startStop->id)
            ->where('end_stop_id', $endStop->id)
            ->sum('estimated_travel_time');

        $fare = FareStructure::where('jeepney_route_id', $route->id)->first();

        return [
            'route_name' => $route->route_name,
            'route_color' => $route->route_color,
            'start_stop' => $startStop->stop_name,
            'end_stop' => $endStop->stop_name,
            'base_fare' => $fare ? $fare->base_fare : 0,
            'estimated_time' => $travelTime,
        ];
    }

    private function addToItineraryWithCommute($destination, $travelTime, $visitTime, $startLat, $startLng)
    {
        $distance = $this->calculateDistance($startLat, $startLng, $destination->latitude, $destination->longitude);

        if ($distance < 1) {
            $walkingTime = ceil($distance * 15);
            $commuteInstructions = sprintf(
                "This destination is nearby. It's only %.2f km away. Consider walking (approximately %d minutes walk).",
                $distance,
                $walkingTime
            );
            $travelTime = $walkingTime;
        } else {
            $jeepneyInfo = $this->getJeepneyRoute($startLat, $startLng, $destination->latitude, $destination->longitude);

            if ($jeepneyInfo) {
                $commuteInstructions = sprintf(
                    "Take a %s Jeep (%s jeep) from '%s' to '%s'. Fare: ₱%.2f.",
                    $jeepneyInfo['route_name'],
                    $jeepneyInfo['route_color'],
                    $jeepneyInfo['start_stop'],
                    $jeepneyInfo['end_stop'],
                    $jeepneyInfo['base_fare']
                );
                $travelTime = $jeepneyInfo['estimated_time'];
            } else {
                $commuteInstructions = "No direct jeepney route available. Consider taking a tricycle.";
            }
        }

        return [
            'name' => $destination->name,
            'type' => $destination->type,
            'travel_time' => $travelTime,
            'visit_time' => $visitTime,
            'commute_instructions' => $commuteInstructions,
        ];
    }
}
