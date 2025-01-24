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
        $travelTime = $walkingTime; // Update travel time
    } else {
        $jeepneyInfo = $this->getJeepneyRoute($startLat, $startLng, $destination->latitude, $destination->longitude);

        if ($jeepneyInfo) {
            // Safely access the coordinates, defaulting to current/destination lat-lng
            $startCoords = $jeepneyInfo['routes'][0]['start_stop_coords'] ?? ['latitude' => $startLat, 'longitude' => $startLng];
            $endCoords = $jeepneyInfo['routes'][0]['end_stop_coords'] ?? ['latitude' => $destination->latitude, 'longitude' => $destination->longitude];

            $travelTime = $this->getGoogleMapsTime(
                $startCoords['latitude'],
                $startCoords['longitude'],
                $endCoords['latitude'],
                $endCoords['longitude']
            );

            $commuteInstructions = sprintf(
                "Take a %s Jeep (%s jeep) from '%s' to '%s'. Fare: ₱%.2f.",
                $jeepneyInfo['routes'][0]['route_name'] ?? 'Unknown Route',
                $jeepneyInfo['routes'][0]['route_color'] ?? 'N/A',
                $jeepneyInfo['routes'][0]['start_stop'] ?? 'Unknown Stop',
                $jeepneyInfo['routes'][0]['end_stop'] ?? 'Unknown Stop',
                $jeepneyInfo['routes'][0]['base_fare'] ?? 0
            );
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

public function generateCommuteGuide(Request $request)
{
    set_time_limit(120);
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
                'commute_instructions' => $jeepneyInfo['routes'] ? 
                    collect($jeepneyInfo['routes'])->map(function($route) {
                        return sprintf(
                            "Take a %s Jeep (%s jeep) from '%s' to '%s'. Fare: ₱%.2f.",
                            $route['route_name'],
                            $route['route_color'],
                            $route['start_stop'],
                            $route['end_stop'],
                            $route['base_fare']
                        );
                    })->implode(' Then ') : 
                    'No route information available',
                'path' => $this->generateRoutePath($jeepneyInfo, $startLat, $startLng, $endLat, $endLng),
            ];
            return response()->json($response);
        }
        return response()->json(['error' => 'No valid jeepney routes available.'], 400);

    } catch (\Exception $e) {
        \Log::error('Error generating commute guide:', ['error' => $e->getMessage()]);
        return response()->json(['error' => 'An error occurred while generating the commute guide.'], 500);
    }
}
private function generateRoutePath($jeepneyInfo, $startLat, $startLng, $endLat, $endLng)
{
    if (!$jeepneyInfo || empty($jeepneyInfo['routes'])) {
        return [];
    }

    $path = [
        ['latitude' => $startLat, 'longitude' => $startLng]
    ];

    foreach ($jeepneyInfo['routes'] as $route) {
        $path[] = $route['start_stop_coords'];
        $path[] = $route['end_stop_coords'];
    }

    $path[] = ['latitude' => $endLat, 'longitude' => $endLng];

    return $path;
}

private function getJeepneyRoute($startLat, $startLng, $endLat, $endLng)
{
    \Log::info('Jeepney Route Search Debug', [
        'start' => ['lat' => $startLat, 'lng' => $startLng],
        'end' => ['lat' => $endLat, 'lng' => $endLng]
    ]);

    $nearbyStartStops = JeepneyStop::select(DB::raw('*, 
        (6371 * acos(cos(radians(?)) * cos(radians(latitude)) 
        * cos(radians(longitude) - radians(?)) + sin(radians(?)) * sin(radians(latitude)))) AS distance'))
        ->having('distance', '<', 2)
        ->orderBy('distance')
        ->limit(10)
        ->setBindings([$startLat, $startLng, $startLat])
        ->get();

    $nearbyEndStops = JeepneyStop::select(DB::raw('*, 
        (6371 * acos(cos(radians(?)) * cos(radians(latitude)) 
        * cos(radians(longitude) - radians(?)) + sin(radians(?)) * sin(radians(latitude)))) AS distance'))
        ->having('distance', '<', 2)
        ->orderBy('distance')
        ->limit(10)
        ->setBindings([$endLat, $endLng, $endLat])
        ->get();

    $directRoutes = [];
    foreach ($nearbyStartStops as $startStop) {
        foreach ($nearbyEndStops as $endStop) {
            if ($startStop->jeepney_route_id === $endStop->jeepney_route_id) {
                $route = JeepneyRoute::find($startStop->jeepney_route_id);
                
                $directRoutes[] = [
                    'type' => 'direct',
                    'routes' => [[
                        'route_name' => $route->route_name,
                        'route_color' => $route->route_color,
                        'start_stop' => $startStop->stop_name,
                        'end_stop' => $endStop->stop_name,
                        'start_stop_coords' => [
                            'latitude' => (float)$startStop->latitude,
                            'longitude' => (float)$startStop->longitude
                        ],
                        'end_stop_coords' => [
                            'latitude' => (float)$endStop->latitude,
                            'longitude' => (float)$endStop->longitude
                        ],
                        'base_fare' => FareStructure::where('jeepney_route_id', $route->id)->value('base_fare') ?? 0
                    ]]
                ];
            }
        }
    }

    \Log::info('Direct Routes Found Debug', [
        'count' => count($directRoutes),
        'routes_sample' => array_slice($directRoutes, 0, 2)
    ]);

    if (!empty($directRoutes)) {
        // Ensure the first route has all necessary data
        $firstRoute = $directRoutes[0];
        
        // Explicitly cast to float and provide default values
        $firstRoute['routes'][0]['start_stop_coords']['latitude'] = 
            (float)($firstRoute['routes'][0]['start_stop_coords']['latitude'] ?? $startLat);
        $firstRoute['routes'][0]['start_stop_coords']['longitude'] = 
            (float)($firstRoute['routes'][0]['start_stop_coords']['longitude'] ?? $startLng);
        $firstRoute['routes'][0]['end_stop_coords']['latitude'] = 
            (float)($firstRoute['routes'][0]['end_stop_coords']['latitude'] ?? $endLat);
        $firstRoute['routes'][0]['end_stop_coords']['longitude'] = 
            (float)($firstRoute['routes'][0]['end_stop_coords']['longitude'] ?? $endLng);

        return $this->formatRouteResponse($firstRoute);
    }

    $connectingRoutes = $this->findConnectingRoutes($nearbyStartStops, $nearbyEndStops);
    if ($connectingRoutes) {
        // Ensure connecting routes have coordinates
        foreach ($connectingRoutes['routes'] as &$route) {
            $route['start_stop_coords']['latitude'] = 
                (float)($route['start_stop_coords']['latitude'] ?? $startLat);
            $route['start_stop_coords']['longitude'] = 
                (float)($route['start_stop_coords']['longitude'] ?? $startLng);
            $route['end_stop_coords']['latitude'] = 
                (float)($route['end_stop_coords']['latitude'] ?? $endLat);
            $route['end_stop_coords']['longitude'] = 
                (float)($route['end_stop_coords']['longitude'] ?? $endLng);
        }
        return $this->formatRouteResponse($connectingRoutes);
    }

    \Log::warning('No Jeepney Routes Found', [
        'start' => ['lat' => $startLat, 'lng' => $startLng],
        'end' => ['lat' => $endLat, 'lng' => $endLng]
    ]);

    return null;
}
private function formatRouteResponse($routeInfo)
{
    if (!$routeInfo || !isset($routeInfo['routes']) || empty($routeInfo['routes'])) {
        \Log::error('Invalid Route Info', ['route_info' => $routeInfo]);
        return null;
    }

    // Ensure the first route has all necessary coordinates
    $firstRoute = $routeInfo['routes'][0];
    
    // Validate and ensure coordinates exist
    if (!isset($firstRoute['start_stop_coords']['latitude']) || 
        !isset($firstRoute['start_stop_coords']['longitude']) ||
        !isset($firstRoute['end_stop_coords']['latitude']) || 
        !isset($firstRoute['end_stop_coords']['longitude'])) {
        
        \Log::error('Missing Coordinates in Route', [
            'route' => $firstRoute,
            'start_stop_coords' => $firstRoute['start_stop_coords'] ?? 'Not set',
            'end_stop_coords' => $firstRoute['end_stop_coords'] ?? 'Not set'
        ]);
        
        return null;
    }

    return [
        'type' => $routeInfo['type'] ?? 'unknown',
        'routes' => $routeInfo['routes']
    ];
}
private function findConnectingRoutes($nearbyStartStops, $nearbyEndStops)
{
    foreach ($nearbyStartStops as $startStop) {
        $potentialTransferStops = JeepneyStop::where('jeepney_route_id', '!=', $startStop->jeepney_route_id)
            ->whereRaw('(6371 * acos(
                cos(radians(?)) * cos(radians(latitude)) 
                * cos(radians(longitude) - radians(?)) + 
                sin(radians(?)) * sin(radians(latitude))
            )) < 3', [$startStop->latitude, $startStop->longitude, $startStop->latitude])
            ->limit(5)
            ->get();

        foreach ($potentialTransferStops as $transferStop) {
            $endStop = $nearbyEndStops->first(function($stop) use ($transferStop) {
                return $stop->jeepney_route_id === $transferStop->jeepney_route_id;
            });

            if ($endStop) {
                $firstRoute = JeepneyRoute::find($startStop->jeepney_route_id);
                $secondRoute = JeepneyRoute::find($transferStop->jeepney_route_id);
                
                return [
                    'type' => 'connecting',
                    'routes' => [
                        [
                            'route_name' => $firstRoute->route_name,
                            'route_color' => $firstRoute->route_color,
                            'start_stop' => $startStop->stop_name,
                            'end_stop' => $transferStop->stop_name,
                            'start_stop_coords' => [
                                'latitude' => $startStop->latitude,
                                'longitude' => $startStop->longitude
                            ],
                            'end_stop_coords' => [
                                'latitude' => $transferStop->latitude,
                                'longitude' => $transferStop->longitude
                            ],
                            'base_fare' => FareStructure::where('jeepney_route_id', $startStop->jeepney_route_id)->value('base_fare') ?? 0
                        ],
                        [
                            'route_name' => $secondRoute->route_name,
                            'route_color' => $secondRoute->route_color,
                            'start_stop' => $transferStop->stop_name,
                            'end_stop' => $endStop->stop_name,
                            'start_stop_coords' => [
                                'latitude' => $transferStop->latitude,
                                'longitude' => $transferStop->longitude
                            ],
                            'end_stop_coords' => [
                                'latitude' => $endStop->latitude,
                                'longitude' => $endStop->longitude
                            ],
                            'base_fare' => FareStructure::where('jeepney_route_id', $endStop->jeepney_route_id)->value('base_fare') ?? 0
                        ]
                    ]
                ];
            }
        }
    }

    return null;
}

private function findDirectRoute($nearbyStartStops, $nearbyEndStops)
{
    $directRoutes = [];

    foreach ($nearbyStartStops as $startStop) {
        foreach ($nearbyEndStops as $endStop) {
            if ($startStop->jeepney_route_id === $endStop->jeepney_route_id) {
                $route = JeepneyRoute::find($startStop->jeepney_route_id);
                
                $fare = FareStructure::where('jeepney_route_id', $route->id)->value('base_fare') ?? 0;
                
                $directRoutes[] = [
                    'type' => 'direct',
                    'routes' => [[
                        'route_name' => $route->route_name,
                        'route_color' => $route->route_color,
                        'start_stop' => $startStop->stop_name,
                        'end_stop' => $endStop->stop_name,
                        'start_stop_coords' => [
                            'latitude' => $startStop->latitude,
                            'longitude' => $startStop->longitude
                        ],
                        'end_stop_coords' => [
                            'latitude' => $endStop->latitude,
                            'longitude' => $endStop->longitude
                        ],
                        'base_fare' => $fare
                    ]]
                ];
            }
        }
    }

    // Return the most direct route (closest stops)
    return $directRoutes ? 
        collect($directRoutes)->sortBy(function($route) {
            $startCoords = $route['routes'][0]['start_stop_coords'];
            $endCoords = $route['routes'][0]['end_stop_coords'];
            return $this->calculateDistance(
                $startCoords['latitude'], 
                $startCoords['longitude'], 
                $endCoords['latitude'], 
                $endCoords['longitude']
            );
        })->first() : null;
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