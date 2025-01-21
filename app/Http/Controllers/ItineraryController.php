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
use Illuminate\Support\Facades\Cache;

class ItineraryController extends Controller
{
    public function generateItinerary(Request $request)
    {
        try {
            // Increase timeout limit for this specific request
            set_time_limit(120);
            
            $validatedData = $request->validate([
                'latitude' => 'required|numeric',
                'longitude' => 'required|numeric',
                'hours' => 'required|integer|min:1',
                'selected_location' => 'nullable|string',
            ]);
    
            $latitude = $validatedData['latitude'];
            $longitude = $validatedData['longitude'];
            $availableTime = $validatedData['hours'] * 60;
    
            // Cache key based on input parameters
            $cacheKey = "itinerary_{$latitude}_{$longitude}_{$availableTime}";
            
            // Try to get cached itinerary first
            if ($cachedItinerary = Cache::get($cacheKey)) {
                return response()->json($cachedItinerary);
            }
    
            // Fetch destinations with pagination and distance calculation
            $destinations = Destination::select(
                    '*',
                    DB::raw('(
                        6371 * acos(
                            cos(radians(?)) * cos(radians(latitude)) * 
                            cos(radians(longitude) - radians(?)) + 
                            sin(radians(?)) * sin(radians(latitude))
                        )
                    ) AS distance'),
                )
                ->whereNotNull('latitude')
                ->whereNotNull('longitude')
                ->setBindings([$latitude, $longitude, $latitude])
                ->having('distance', '<=', 20) // Limit to destinations within 20km
                ->orderBy('distance')
                ->limit(50) // Limit the number of destinations to process
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
            $currentLat = $latitude;
            $currentLng = $longitude;
    
            // Pre-calculate travel times for optimization
            $travelTimes = [];
            foreach ($destinations as $destination) {
                $travelTime = $this->getTravelTimeFromGoogle(
                    $currentLat,
                    $currentLng,
                    $destination->latitude,
                    $destination->longitude
                );
                $travelTimes[$destination->id] = $travelTime;
            }
    
            while ($timeSpent < $availableTime && $destinations->isNotEmpty()) {
                $cluster = $this->getClusterOfDestinations($currentLat, $currentLng, $destinations, 5.0);
                
                if ($cluster->isEmpty()) {
                    break;
                }
    
                $shouldAddFoodStop = $foodStopsAdded < $maxFoodStops && $landmarkCount >= $maxLandmarksBeforeFoodStop;
                
                $filteredCluster = $cluster->filter(function ($destination) use ($shouldAddFoodStop) {
                    return $shouldAddFoodStop ? $destination->type === 'restaurant' : $destination->type === 'landmark';
                });
    
                if ($filteredCluster->isEmpty()) {
                    break;
                }
    
                $nextDestination = $this->findClosestDestination($currentLat, $currentLng, $filteredCluster);
                
                if (!$nextDestination) {
                    break;
                }
    
                $travelTime = $travelTimes[$nextDestination->id];
                $visitTime = $nextDestination->type === 'restaurant' ? 40 : 20;
    
                if ($timeSpent + $travelTime + $visitTime > $availableTime) {
                    break;
                }
    
                $itineraryItem = $this->addToItineraryWithCommute(
                    $nextDestination,
                    $travelTime,
                    $visitTime,
                    $currentLat,
                    $currentLng
                );
    
                $itineraryItem['latitude'] = $nextDestination->latitude;
                $itineraryItem['longitude'] = $nextDestination->longitude;
                $itineraryItem['description'] = $nextDestination->description ?? 'No description available.';
    
                $itinerary[] = $itineraryItem;
                $timeSpent += $travelTime + $visitTime;
                
                $currentLat = $nextDestination->latitude;
                $currentLng = $nextDestination->longitude;
                
                $destinations = $destinations->reject(fn($d) => $d->id === $nextDestination->id);
    
                if ($nextDestination->type === 'restaurant') {
                    $foodStopsAdded++;
                    $landmarkCount = 0;
                } else {
                    $landmarkCount++;
                }
            }
    
            // Cache the result for 1 hour
            Cache::put($cacheKey, $itinerary, 3600);
    
            return response()->json($itinerary);
    
        } catch (\Exception $e) {
            \Log::error('Error Generating Itinerary:', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'An error occurred while generating the itinerary.'], 500);
        }
    }
    private function getTravelTimeFromGoogle($startLat, $startLng, $endLat, $endLng)
    {
        $cacheKey = "travel_time_{$startLat}_{$startLng}_{$endLat}_{$endLng}";
        
        return Cache::remember($cacheKey, 3600, function() use ($startLat, $startLng, $endLat, $endLng) {
            try {
                $apiKey = env('GOOGLE_MAPS_API_KEY');
                $url = "https://maps.googleapis.com/maps/api/directions/json?origin={$startLat},{$startLng}&destination={$endLat},{$endLng}&mode=driving&key={$apiKey}";
                
                $response = Http::timeout(5)->get($url);
                
                if ($response->successful()) {
                    $data = $response->json();
                    if (!empty($data['routes'])) {
                        return ceil($data['routes'][0]['legs'][0]['duration']['value'] / 60);
                    }
                }
                
                // Fallback to distance-based estimation if API fails
                $distance = $this->calculateDistance($startLat, $startLng, $endLat, $endLng);
                return ceil(($distance / 30) * 60); // Assuming 30 km/h average speed
                
            } catch (\Exception $e) {
                \Log::error('Google Maps API Error:', ['error' => $e->getMessage()]);
                return 30; // Default 30 minutes if all else fails
            }
        });
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
    private function getCoordinatesFromAddress($address)
{
    $apiKey = env('GOOGLE_MAPS_API_KEY');
    $url = "https://maps.googleapis.com/maps/api/geocode/json?address=" . urlencode($address) . "&key={$apiKey}";

    $response = Http::get($url);

    if ($response->successful()) {
        $data = $response->json();
        if (!empty($data['results'])) {
            return [
                'lat' => $data['results'][0]['geometry']['location']['lat'],
                'lng' => $data['results'][0]['geometry']['location']['lng'],
            ];
        } elseif ($data['status'] === 'ZERO_RESULTS') {
            return null; // No results found
        }
    }

    \Log::error('Failed to call Geocoding API', ['status' => $response->status(), 'body' => $response->body()]);
    return null;
}
private function addToItineraryWithCommute($destination, $travelTime, $visitTime, $startLat, $startLng)
{
    $distance = $this->calculateDistance($startLat, $startLng, $destination->latitude, $destination->longitude);
    if ($distance < 1) {
        $walkingTime = ceil($distance * 15); // Estimate 15 minutes per km for walking
        $commuteInstructions = sprintf(
            "This destination is nearby. It's only %.2f km away. Consider walking (approximately %d minutes walk).",
            $distance,
            $walkingTime
        );
        // Set travel time to walking time
        $travelTime = $walkingTime;
    } else {
        $jeepneyInfo = $this->getJeepneyRoute($startLat, $startLng, $destination->latitude, $destination->longitude);
        if ($jeepneyInfo) {
            // Use Google Maps time if available
            $travelTime = $this->getGoogleMapsTime(
                $jeepneyInfo['start_stop_coords']['latitude'],
                $jeepneyInfo['start_stop_coords']['longitude'],
                $jeepneyInfo['end_stop_coords']['latitude'],
                $jeepneyInfo['end_stop_coords']['longitude']
            );

            $commuteInstructions = sprintf(
                "Take a %s Jeep (%s jeep) from '%s' to '%s'. Fare: ₱%.2f.",
                $jeepneyInfo['route_name'],
                $jeepneyInfo['route_color'],
                $jeepneyInfo['start_stop'],
                $jeepneyInfo['end_stop'],
                $jeepneyInfo['base_fare']
            );
        } else {
            $commuteInstructions = "No direct jeepney route available. Consider taking a tricycle.";
        }
    }

    return [
        'name' => $destination->name,
        'type' => $destination->type,
        'travel_time' => $travelTime,
        'visit_time' => $visitTime, // Include visit time here
        'commute_instructions' => $commuteInstructions,
    ];
}

public function generateCommuteGuide(Request $request)
{
    try {
        $validatedData = $request->validate([
            'start' => 'required|string',
            'end' => 'required|string',
        ]);

        // Fetch start and end coordinates
        $startCoords = $this->getCoordinatesFromAddress($validatedData['start']);
        $endCoords = $this->getCoordinatesFromAddress($validatedData['end']);

        if (!$startCoords || !$endCoords) {
            return response()->json(['error' => 'Unable to locate one or both addresses.'], 400);
        }

        $startLat = $startCoords['lat'];
        $startLng = $startCoords['lng'];
        $endLat = $endCoords['lat'];
        $endLng = $endCoords['lng'];

        // Calculate distance between the start and end points
        $distance = $this->calculateDistance($startLat, $startLng, $endLat, $endLng);

        if ($distance < 1) {
            $walkingTime = ceil($distance * 15);
            $response = [
                'start' => $validatedData['start'],
                'end' => $validatedData['end'],
                'distance' => $distance,
                'travel_time' => $walkingTime,
                'commute_instructions' => sprintf(
                    "This destination is nearby. It's only %.2f km away. Consider walking (approximately %d minutes walk).",
                    $distance,
                    $walkingTime
                ),
                'path' => [
                    ['latitude' => $startLat, 'longitude' => $startLng],
                    ['latitude' => $endLat, 'longitude' => $endLng],
                ],
            ];
            return response()->json($response);
        }

        // Find the best jeepney route
        $jeepneyInfo = $this->getJeepneyRoute($startLat, $startLng, $endLat, $endLng);

        if ($jeepneyInfo) {
            $response = [
                'start' => $validatedData['start'],
                'end' => $validatedData['end'],
                'distance' => $distance,
                'travel_time' => $this->getGoogleMapsTime($startLat, $startLng, $endLat, $endLng),
                'commute_instructions' => sprintf(
                    "Take a %s Jeep (%s jeep) from '%s' to '%s'. Fare: ₱%.2f.",
                    $jeepneyInfo['route_name'],
                    $jeepneyInfo['route_color'],
                    $jeepneyInfo['start_stop'],
                    $jeepneyInfo['end_stop'],
                    $jeepneyInfo['base_fare']
                ),
                'path' => [
                    ['latitude' => $startLat, 'longitude' => $startLng],
                    ['latitude' => $jeepneyInfo['start_stop_coords']['latitude'], 
                     'longitude' => $jeepneyInfo['start_stop_coords']['longitude']],
                    ['latitude' => $jeepneyInfo['end_stop_coords']['latitude'], 
                     'longitude' => $jeepneyInfo['end_stop_coords']['longitude']],
                    ['latitude' => $endLat, 'longitude' => $endLng],
                ],
            ];
            return response()->json($response);
        }

        return response()->json(['error' => 'No valid jeepney routes available.'], 400);

    } catch (\Exception $e) {
        \Log::error('Error generating commute guide:', ['error' => $e->getMessage()]);
        return response()->json(['error' => 'An error occurred while generating the commute guide.'], 500);
    }
}

private function getJeepneyRoute($startLat, $startLng, $endLat, $endLng)
{
    // Find nearby stops with reduced logging
    $nearbyStartStops = JeepneyStop::select(DB::raw('*, 
        (6371 * acos(cos(radians(?)) * cos(radians(latitude)) 
        * cos(radians(longitude) - radians(?)) + sin(radians(?)) * sin(radians(latitude)))) AS distance'))
        ->having('distance', '<', 1)
        ->orderBy('distance')
        ->setBindings([$startLat, $startLng, $startLat])
        ->get();

    $nearbyEndStops = JeepneyStop::select(DB::raw('*, 
        (6371 * acos(cos(radians(?)) * cos(radians(latitude)) 
        * cos(radians(longitude) - radians(?)) + sin(radians(?)) * sin(radians(latitude)))) AS distance'))
        ->having('distance', '<', 1)
        ->orderBy('distance')
        ->setBindings([$endLat, $endLng, $endLat])
        ->get();

    if ($nearbyStartStops->isEmpty() || $nearbyEndStops->isEmpty()) {
        return null;
    }

    $startRouteIds = $nearbyStartStops->pluck('jeepney_route_id')->unique();
    $endRouteIds = $nearbyEndStops->pluck('jeepney_route_id')->unique();
    $possibleRoutes = JeepneyRoute::whereIn('id', $startRouteIds->merge($endRouteIds))->get();

    $validRoutes = [];

    foreach ($possibleRoutes as $route) {
        $routeStops = JeepneyStop::where('jeepney_route_id', $route->id)
            ->orderBy('order_in_route')
            ->get();

        foreach ($nearbyStartStops as $startStop) {
            foreach ($nearbyEndStops as $endStop) {
                if ($startStop->jeepney_route_id != $route->id || 
                    $endStop->jeepney_route_id != $route->id || 
                    $startStop->id === $endStop->id) {
                    continue;
                }

                $startStopOnRoute = $routeStops->where('stop_name', $startStop->stop_name)->first();
                $endStopOnRoute = $routeStops->where('stop_name', $endStop->stop_name)->first();

                if ($startStopOnRoute && $endStopOnRoute) {
                    $pathDistance = $this->calculateDistance(
                        $startStopOnRoute->latitude,
                        $startStopOnRoute->longitude,
                        $endStopOnRoute->latitude,
                        $endStopOnRoute->longitude
                    );
                    
                    $validRoutes[] = [
                        'route' => $route,
                        'startStop' => $startStop,
                        'endStop' => $endStop,
                        'pathDistance' => $pathDistance
                    ];
                }
            }
        }
    }

    if (!empty($validRoutes)) {
        usort($validRoutes, function ($a, $b) {
            return $a['pathDistance'] <=> $b['pathDistance'];
        });

        $bestRoute = $validRoutes[0];
        $fare = FareStructure::where('jeepney_route_id', $bestRoute['route']->id)
            ->value('base_fare') ?? 0;

        return [
            'route_name' => $bestRoute['route']->route_name,
            'route_color' => $bestRoute['route']->route_color,
            'start_stop' => $bestRoute['startStop']->stop_name,
            'end_stop' => $bestRoute['endStop']->stop_name,
            'start_stop_coords' => [
                'latitude' => $bestRoute['startStop']->latitude,
                'longitude' => $bestRoute['startStop']->longitude
            ],
            'end_stop_coords' => [
                'latitude' => $bestRoute['endStop']->latitude,
                'longitude' => $bestRoute['endStop']->longitude
            ],
            'base_fare' => $fare
        ];
    }

    return null;
}

private function getGoogleMapsTime($startLat, $startLng, $endLat, $endLng)
{
    $response = Http::get("https://maps.googleapis.com/maps/api/directions/json", [
        'origin' => "{$startLat},{$startLng}",
        'destination' => "{$endLat},{$endLng}",
        'mode' => 'driving',
        'key' => env('GOOGLE_MAPS_API_KEY')
    ]);

    if ($response->successful()) {
        $data = $response->json();
        if (!empty($data['routes'][0]['legs'][0]['duration']['value'])) {
            return ceil($data['routes'][0]['legs'][0]['duration']['value'] / 60);
        }
    }

    // Fallback to a simple estimation if Google API fails
    $distance = $this->calculateDistance($startLat, $startLng, $endLat, $endLng);
    return ceil(($distance / 15) * 60); // Assuming 15 km/h average speed
}private function calculatePathDistance($stops)
{
    $totalDistance = 0;
    for ($i = 0; $i < $stops->count() - 1; $i++) {
        $distance = $this->calculateDistance(
            $stops[$i]->latitude,
            $stops[$i]->longitude,
            $stops[$i + 1]->latitude,
            $stops[$i + 1]->longitude
        );
        \Log::info("Distance between stops", [
            'from' => $stops[$i]->stop_name,
            'to' => $stops[$i + 1]->stop_name,
            'distance' => $distance
        ]);
        $totalDistance += $distance;
    }
    return $totalDistance;
}
}