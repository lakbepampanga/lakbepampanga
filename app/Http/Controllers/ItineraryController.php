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
use App\Models\SavedItinerary;
use App\Models\ItineraryCompletion;  // Add this line
use App\Models\DestinationVisit;  // Add this line


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
                $itineraryItem['image_url'] = $nextDestination->image_url;
    
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
        'image_url' => $destination->image_url ?? null,
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
// In your generateCommuteGuide function, modify the $jeepneyInfo part:
    if ($jeepneyInfo) {
        $routes = collect($jeepneyInfo['routes'])->map(function($route, $index) {
            // Fetch the image path from jeepney_routes table
            $jeepneyRoute = \App\Models\JeepneyRoute::where('route_name', $route['route_name'])->first();
            $imagePath = $jeepneyRoute ? asset('storage/' . $jeepneyRoute->image_path) : null;
            
            // Add "Then take" prefix for instructions after the first one
            $prefix = $index === 0 ? "Take" : "Then take";
            
            return [
                'instruction' => sprintf(
                    "%s a %s Jeep (%s jeep) from '%s' to '%s'. Fare: ₱%.2f.",
                    $prefix,
                    $route['route_name'],
                    $route['route_color'],
                    $route['start_stop'],
                    $route['end_stop'],
                    $route['base_fare']
                ),
                'image_path' => $imagePath,
                'route_name' => $route['route_name'],
                'route_color' => $route['route_color']
            ];
        });
    
        $response = [
            'start' => $validatedData['start'],
            'end' => $validatedData['end'],
            'distance' => $distance,
            'travel_time' => $this->getGoogleMapsTime($startLat, $startLng, $endLat, $endLng),
            'commute_instructions' => $routes,
            'path' => $this->generateRoutePath($jeepneyInfo, $startLat, $startLng, $endLat, $endLng),
        ];
        return response()->json($response);
    }
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

    // Try forward direction first
    $forwardRoute = $this->tryFindRoute($startLat, $startLng, $endLat, $endLng);
    if ($forwardRoute) {
        return $forwardRoute;
    }

    // If no forward route found, try reverse direction
    $reverseRoute = $this->tryFindRoute($endLat, $endLng, $startLat, $startLng, true);
    if ($reverseRoute) {
        return $reverseRoute;
    }

    \Log::warning('No Jeepney Routes Found in either direction', [
        'start' => ['lat' => $startLat, 'lng' => $startLng],
        'end' => ['lat' => $endLat, 'lng' => $endLng]
    ]);

    return null;
}
private function tryFindRoute($fromLat, $fromLng, $toLat, $toLng, $isReverse = false)
{
    $nearbyStartStops = JeepneyStop::select(DB::raw('*, 
        (6371 * acos(cos(radians(?)) * cos(radians(latitude)) 
        * cos(radians(longitude) - radians(?)) + sin(radians(?)) * sin(radians(latitude)))) AS distance'))
        ->having('distance', '<', 2)
        ->orderBy('distance')
        ->setBindings([$fromLat, $fromLng, $fromLat])
        ->get();

    $nearbyEndStops = JeepneyStop::select(DB::raw('*, 
        (6371 * acos(cos(radians(?)) * cos(radians(latitude)) 
        * cos(radians(longitude) - radians(?)) + sin(radians(?)) * sin(radians(latitude)))) AS distance'))
        ->having('distance', '<', 2)
        ->orderBy('distance')
        ->setBindings([$toLat, $toLng, $toLat])
        ->get();

    if ($nearbyStartStops->isEmpty() || $nearbyEndStops->isEmpty()) {
        return null;
    }

    $distanceBetweenPoints = $this->calculateDistance($fromLat, $fromLng, $toLat, $toLng);
    $route = $this->findConnectingRoutes($nearbyStartStops, $nearbyEndStops);
    
    if ($route) {
        $routes = $route['routes'];
        
        // Check for and merge same route segments
        $processedRoutes = [];
        $currentRoute = null;
        
        foreach ($routes as $segment) {
            if (!$currentRoute) {
                $currentRoute = $segment;
                continue;
            }
            
            if ($currentRoute['route_name'] === $segment['route_name']) {
                // Update the end point of the current route instead of adding a new segment
                $currentRoute['end_stop'] = $segment['end_stop'];
                $currentRoute['end_stop_coords'] = $segment['end_stop_coords'];
            } else {
                $processedRoutes[] = $currentRoute;
                $currentRoute = $segment;
            }
        }
        
        if ($currentRoute) {
            $processedRoutes[] = $currentRoute;
        }
        
        // Check if we need an additional route to reach the final destination
        $lastStop = end($processedRoutes);
        if ($distanceBetweenPoints > 1 && 
            $this->calculateDistance(
                $lastStop['end_stop_coords']['latitude'],
                $lastStop['end_stop_coords']['longitude'],
                $toLat,
                $toLng
            ) > 0.5) {
            
            $finalStop = $nearbyEndStops->first();
            $finalRoute = JeepneyRoute::find($finalStop->jeepney_route_id);
            
            // Only add if it's a different route
            if ($finalRoute && $finalRoute->route_name !== $lastStop['route_name']) {
                $processedRoutes[] = [
                    'route_name' => $finalRoute->route_name,
                    'route_color' => $finalRoute->route_color,
                    'start_stop' => $lastStop['end_stop'],
                    'end_stop' => $finalStop->stop_name,
                    'start_stop_coords' => [
                        'latitude' => $lastStop['end_stop_coords']['latitude'],
                        'longitude' => $lastStop['end_stop_coords']['longitude']
                    ],
                    'end_stop_coords' => [
                        'latitude' => $finalStop->latitude,
                        'longitude' => $finalStop->longitude
                    ],
                    'base_fare' => FareStructure::where('jeepney_route_id', $finalRoute->id)->value('base_fare') ?? 0
                ];
            }
        }
        
        return $this->formatRouteResponse([
            'type' => 'connecting',
            'routes' => $processedRoutes
        ]);
    }

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
private function findConnectingRoutes($nearbyStartStops, $nearbyEndStops, $maxConnections = 3, $depth = 0, $visitedRoutes = [], $currentPath = [])
{
    if ($depth >= $maxConnections) return null;

    foreach ($nearbyStartStops as $startStop) {
        if (in_array($startStop->jeepney_route_id, $visitedRoutes)) continue;

        // If this is the first iteration (depth = 0), check if there are better routes nearby
        if ($depth == 0) {
            // Look for nearby stops of routes that go directly to the destination
            foreach ($nearbyEndStops as $endStop) {
                $nearbyBetterStops = JeepneyStop::where('jeepney_route_id', $endStop->jeepney_route_id)
                    ->select('*')
                    ->selectRaw('(
                        6371 * acos(
                            cos(radians(?)) * cos(radians(latitude)) * 
                            cos(radians(longitude) - radians(?)) + 
                            sin(radians(?)) * sin(radians(latitude))
                        )
                    ) as distance', [$startStop->latitude, $startStop->longitude, $startStop->latitude])
                    ->havingRaw('distance < ?', [0.3])
                    ->orderBy('distance')
                    ->first();

                if ($nearbyBetterStops) {
                    if ($nearbyBetterStops->order_in_route < $endStop->order_in_route) {
                        $route = JeepneyRoute::find($nearbyBetterStops->jeepney_route_id);
                    if ($route) {
                        return [
                            'type' => 'connecting',
                            'routes' => [[
                                'route_name' => $route->route_name,
                                'route_color' => $route->route_color,
                                'start_stop' => $nearbyBetterStops->stop_name,
                                'end_stop' => $endStop->stop_name,
                                'start_stop_coords' => [
                                    'latitude' => $nearbyBetterStops->latitude,
                                    'longitude' => $nearbyBetterStops->longitude
                                ],
                                'end_stop_coords' => [
                                    'latitude' => $endStop->latitude,
                                    'longitude' => $endStop->longitude
                                ],
                                'base_fare' => FareStructure::where('jeepney_route_id', $route->id)->value('base_fare') ?? 0
                            ]]
                        ];
                    }
                }
            }
            }
            
        }

        // Rest of your existing code remains exactly the same...
        \Log::info("Processing stop at depth $depth", [
            'stop' => $startStop->stop_name,
            'route_id' => $startStop->jeepney_route_id
        ]);

        foreach ($nearbyEndStops as $endStop) {
            if ($endStop->jeepney_route_id === $startStop->jeepney_route_id) {
                $route = JeepneyRoute::find($startStop->jeepney_route_id);
                $currentSegment = [
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
                    'base_fare' => FareStructure::where('jeepney_route_id', $route->id)->value('base_fare') ?? 0
                ];

                return [
                    'type' => 'connecting',
                    'routes' => array_merge($currentPath, [$currentSegment])
                ];
            }
        }

        // Continue with your existing transfer points code...
        $transfers = JeepneyStop::select('*')
            ->whereNotIn('jeepney_route_id', array_merge($visitedRoutes, [$startStop->jeepney_route_id]))
            ->whereRaw('(6371 * acos(
                cos(radians(?)) * cos(radians(latitude)) * 
                cos(radians(longitude) - radians(?)) + 
                sin(radians(?)) * sin(radians(latitude))
            )) < 1.5', [$startStop->latitude, $startStop->longitude, $startStop->latitude])
            ->orderByRaw('(6371 * acos(
                cos(radians(?)) * cos(radians(latitude)) * 
                cos(radians(longitude) - radians(?)) + 
                sin(radians(?)) * sin(radians(latitude))
            ))', [$nearbyEndStops->first()->latitude, $nearbyEndStops->first()->longitude, $nearbyEndStops->first()->latitude])
            ->limit(5)
            ->get();

        $currentRoute = JeepneyRoute::find($startStop->jeepney_route_id);

        foreach ($transfers as $transfer) {
            \Log::info("Found transfer point", [
                'from' => $startStop->stop_name,
                'to' => $transfer->stop_name
            ]);

            $segment = [
                'route_name' => $currentRoute->route_name,
                'route_color' => $currentRoute->route_color,
                'start_stop' => $startStop->stop_name,
                'end_stop' => $transfer->stop_name,
                'start_stop_coords' => [
                    'latitude' => $startStop->latitude,
                    'longitude' => $startStop->longitude
                ],
                'end_stop_coords' => [
                    'latitude' => $transfer->latitude,
                    'longitude' => $transfer->longitude
                ],
                'base_fare' => FareStructure::where('jeepney_route_id', $currentRoute->id)->value('base_fare') ?? 0
            ];

            $result = $this->findConnectingRoutes(
                collect([$transfer]),
                $nearbyEndStops,
                $maxConnections,
                $depth + 1,
                array_merge($visitedRoutes, [$startStop->jeepney_route_id]),
                array_merge($currentPath, [$segment])
            );

            if ($result) return $result;
        }
    }

    return null;
}
private function findDirectRoute($startStops, $endStops)
{
    foreach ($startStops as $startStop) {
        foreach ($endStops as $endStop) {
            if ($endStop->jeepney_route_id === $startStop->jeepney_route_id) {
                $route = JeepneyRoute::find($startStop->jeepney_route_id);
                return [
                    'type' => 'connecting',
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
                        'base_fare' => FareStructure::where('jeepney_route_id', $route->id)->value('base_fare') ?? 0
                    ]]
                ];
            }
        }
    }
    return null;
}

private function findTransferPoints($startStop, $endStop, $visitedRoutes)
{
    return JeepneyStop::select('*')
        ->whereNotIn('jeepney_route_id', array_merge($visitedRoutes, [$startStop->jeepney_route_id]))
        ->whereRaw('(6371 * acos(
            cos(radians(?)) * cos(radians(latitude)) * 
            cos(radians(longitude) - radians(?)) + 
            sin(radians(?)) * sin(radians(latitude))
        )) < 2', [$startStop->latitude, $startStop->longitude, $startStop->latitude])
        ->orderByRaw('(6371 * acos(
            cos(radians(?)) * cos(radians(latitude)) * 
            cos(radians(longitude) - radians(?)) + 
            sin(radians(?)) * sin(radians(latitude))
        ))', [$endStop->latitude, $endStop->longitude, $endStop->latitude])
        ->limit(10)
        ->get();
}

private function createRouteSegment($startStop, $endStop, $route)
{
    return [
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
        'base_fare' => FareStructure::where('jeepney_route_id', $route->id)->value('base_fare') ?? 0
    ];
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
public function getAlternativeDestinations(Request $request)
{
    try {
        $validatedData = $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'radius' => 'required|numeric|max:10.0'
        ]);

        $destinations = Destination::select(
            'destinations.*', // Select all columns from destinations table
            DB::raw('(
                6371 * acos(
                    cos(radians(?)) * cos(radians(latitude)) * 
                    cos(radians(longitude) - radians(?)) + 
                    sin(radians(?)) * sin(radians(latitude))
                )
            ) AS distance')
        )
        ->whereNotNull('latitude')
        ->whereNotNull('longitude')
        ->setBindings([
            $validatedData['latitude'], 
            $validatedData['longitude'], 
            $validatedData['latitude']
        ])
        ->having('distance', '<=', $validatedData['radius'])
        ->orderBy('distance')
        ->get()
        ->map(function ($destination) {
            // Transform the image URL if it exists
            if ($destination->image_url) {
                $destination->image_url = asset('storage/' . $destination->image_url);
            }
            return $destination;
        });

        return response()->json($destinations);
    } catch (\Exception $e) {
        \Log::error('Error fetching alternative destinations:', ['error' => $e->getMessage()]);
        return response()->json(['error' => 'An error occurred while fetching alternatives.'], 500);
    }
}
public function updateItineraryItem(Request $request)
{
    try {
        \Log::info('Received update request:', $request->all());

        $validatedData = $request->validate([
            'previousDestination' => 'nullable|array',
            'newDestination' => 'required|array',
            'nextDestination' => 'nullable|array',
            'startLat' => 'required|numeric',
            'startLng' => 'required|numeric'
        ]);

        $startLat = $validatedData['previousDestination'] 
            ? $validatedData['previousDestination']['latitude']
            : $validatedData['startLat'];
            
        $startLng = $validatedData['previousDestination']
            ? $validatedData['previousDestination']['longitude']
            : $validatedData['startLng'];

        $newDestination = $validatedData['newDestination'];
        
        \Log::info('New destination data:', ['destination' => $newDestination]);

        // Create a proper destination object with all required properties
        $destinationObject = new \stdClass();
        $destinationObject->name = $newDestination['name'];
        $destinationObject->type = $newDestination['type'];
        $destinationObject->latitude = $newDestination['latitude'];
        $destinationObject->longitude = $newDestination['longitude'];
        $destinationObject->description = $newDestination['description'] ?? '';
        $destinationObject->image_url = $newDestination['image_url'] ?? null;

        \Log::info('Created destination object:', ['object' => $destinationObject]);

        // Calculate new travel times and commute instructions
        $travelTime = $this->getTravelTimeFromGoogle(
            $startLat,
            $startLng,
            $newDestination['latitude'],
            $newDestination['longitude']
        );

        $visitTime = $newDestination['type'] === 'restaurant' ? 40 : 20;

        $updatedItem = $this->addToItineraryWithCommute(
            $destinationObject,
            $travelTime,
            $visitTime,
            $startLat,
            $startLng
        );

        // Make sure these properties are included in the response
        $updatedItem['latitude'] = $newDestination['latitude'];
        $updatedItem['longitude'] = $newDestination['longitude'];
        $updatedItem['description'] = $newDestination['description'] ?? '';
        $updatedItem['image_url'] = $newDestination['image_url'] ?? null;

        \Log::info('Sending updated item:', ['item' => $updatedItem]);

        return response()->json($updatedItem);
    } catch (\Exception $e) {
        \Log::error('Error updating itinerary item:', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        return response()->json(['error' => 'An error occurred while updating the itinerary.'], 500);
    }
}
// ItineraryController.php
public function saveItinerary(Request $request)
{
    try {
        \Log::info('Received save request', $request->all());

        $validatedData = $request->validate([
            'itinerary_data' => 'required|array',
            'start_lat' => 'required|numeric',
            'start_lng' => 'required|numeric',
            'duration_hours' => 'required|integer'
        ]);

        \Log::info('User ID: ' . auth()->id());

        $savedItinerary = \App\Models\SavedItinerary::create([
            'user_id' => auth()->id(),
            'name' => 'Itinerary ' . now()->format('Y-m-d H:i'),
            'itinerary_data' => $validatedData['itinerary_data'],
            'start_lat' => $validatedData['start_lat'],
            'start_lng' => $validatedData['start_lng'],
            'duration_hours' => $validatedData['duration_hours']
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Itinerary saved successfully',
            'data' => $savedItinerary
        ]);

    } catch (\Exception $e) {
        \Log::error('Save itinerary error:', [
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        
        return response()->json([
            'success' => false,
            'error' => $e->getMessage()
        ], 500);
    }
}

}