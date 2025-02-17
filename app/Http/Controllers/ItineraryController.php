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
            set_time_limit(120);
            
            $validatedData = $request->validate([
                'latitude' => 'required|numeric',
                'longitude' => 'required|numeric',
                'hours' => 'required|integer|min:1',
                'interests' => 'array',
                'interests.*' => 'string|in:landmark,restaurant,museum,shopping,nature,religious,entertainment,cultural,park,market'
            ]);
    
            $latitude = $validatedData['latitude'];
            $longitude = $validatedData['longitude'];
            $availableTime = $validatedData['hours'] * 60;
            
            // Get user selected interests, excluding restaurants (will be handled separately)
            $interests = !empty($validatedData['interests']) 
                ? array_diff($validatedData['interests'], ['restaurant'])
                : [
                    'landmark',
                    'museum',
                    'shopping',
                    'nature',
                    'religious',
                    'entertainment',
                    'cultural',
                    'park',
                    'market'
                ];
    
            $cacheKey = "itinerary_{$latitude}_{$longitude}_{$availableTime}_" . implode('_', $interests);
            
            if ($cachedItinerary = Cache::get($cacheKey)) {
                return response()->json($cachedItinerary);
            }
    
            // Get regular destinations
            $regularDestinations = Destination::select(
                '*',
                DB::raw("(
                    6371 * acos(
                        cos(radians($latitude)) * cos(radians(latitude)) *
                        cos(radians(longitude) - radians($longitude)) +
                        sin(radians($latitude)) * sin(radians(latitude))
                    )
                ) AS distance")
            )
            ->whereIn('type', $interests)
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->havingRaw('distance <= 20')
            ->orderBy('distance')
            ->get();
    
            // Get restaurants separately
            $restaurants = Destination::select(
                '*',
                DB::raw("(
                    6371 * acos(
                        cos(radians($latitude)) * cos(radians(latitude)) *
                        cos(radians(longitude) - radians($longitude)) +
                        sin(radians($latitude)) * sin(radians(latitude))
                    )
                ) AS distance")
            )
            ->where('type', 'restaurant')
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->havingRaw('distance <= 20')
            ->orderBy('distance')
            ->get();
    
            if ($regularDestinations->isEmpty()) {
                return response()->json(['error' => 'No destinations available in this area.'], 400);
            }
    
            $itinerary = [];
            $timeSpent = 0;
            $lastFoodStop = 0;
            $currentLat = $latitude;
            $currentLng = $longitude;
            
            // Type distribution for non-restaurant destinations
            $typeDistribution = [
                'landmark' => ['max' => 2, 'count' => 0],
                'museum' => ['max' => 1, 'count' => 0],
                'shopping' => ['max' => 1, 'count' => 0],
                'nature' => ['max' => 1, 'count' => 0],
                'religious' => ['max' => 1, 'count' => 0],
                'entertainment' => ['max' => 1, 'count' => 0],
                'cultural' => ['max' => 1, 'count' => 0],
                'park' => ['max' => 1, 'count' => 0],
                'market' => ['max' => 1, 'count' => 0]
            ];
    
            // Pre-calculate travel times for all destinations
            $travelTimes = [];
            foreach ($regularDestinations as $destination) {
                $travelTimes[$destination->id] = $this->getTravelTimeFromGoogle(
                    $currentLat,
                    $currentLng,
                    $destination->latitude,
                    $destination->longitude
                );
            }
            foreach ($restaurants as $restaurant) {
                $travelTimes[$restaurant->id] = $this->getTravelTimeFromGoogle(
                    $currentLat,
                    $currentLng,
                    $restaurant->latitude,
                    $restaurant->longitude
                );
            }
    
            while ($timeSpent < $availableTime && ($regularDestinations->isNotEmpty() || $restaurants->isNotEmpty())) {
                // Check if it's time for a food stop (every 4 hours)
                $needFoodStop = ($timeSpent - $lastFoodStop) >= 240 && $restaurants->isNotEmpty();
    
                if ($needFoodStop) {
                    // Find nearest restaurant
                    $cluster = $this->getClusterOfDestinations($currentLat, $currentLng, $restaurants, 5.0);
                    if ($cluster->isNotEmpty()) {
                        $nextDestination = $this->findClosestDestination($currentLat, $currentLng, $cluster);
                        $restaurants = $restaurants->reject(fn($d) => $d->id === $nextDestination->id);
                    } else {
                        $needFoodStop = false; // No restaurants nearby, continue with regular destinations
                    }
                }
    
                if (!$needFoodStop) {
                    $cluster = $this->getClusterOfDestinations($currentLat, $currentLng, $regularDestinations, 5.0);
                    if ($cluster->isEmpty()) break;
    
                    // Filter by type distribution
                    $filteredCluster = $cluster->filter(function ($destination) use ($typeDistribution) {
                        return !isset($typeDistribution[$destination->type]) ||
                               $typeDistribution[$destination->type]['count'] < $typeDistribution[$destination->type]['max'];
                    });
    
                    if ($filteredCluster->isEmpty()) {
                        $filteredCluster = $cluster;
                    }
    
                    $nextDestination = $this->findClosestDestination($currentLat, $currentLng, $filteredCluster);
                    if (!$nextDestination) break;
    
                    $regularDestinations = $regularDestinations->reject(fn($d) => $d->id === $nextDestination->id);
                }
    
                if (!isset($nextDestination)) break;
    
                $travelTime = $travelTimes[$nextDestination->id];
                $visitTime = $nextDestination->recommended_visit_time ?? 
                            ($nextDestination->type === 'restaurant' ? 40 : 20);
    
                if ($timeSpent + $travelTime + $visitTime > $availableTime) break;
    
                // Update type count and food stop tracking
                if (isset($typeDistribution[$nextDestination->type])) {
                    $typeDistribution[$nextDestination->type]['count']++;
                }
                if ($nextDestination->type === 'restaurant') {
                    $lastFoodStop = $timeSpent + $travelTime + $visitTime;
                }
    
                // Add to itinerary
                $itineraryItem = $this->addToItineraryWithCommute(
                    $nextDestination,
                    $travelTime,
                    $visitTime,
                    $currentLat,
                    $currentLng
                );
    
                // Add additional details
                $itineraryItem['latitude'] = $nextDestination->latitude;
                $itineraryItem['longitude'] = $nextDestination->longitude;
                $itineraryItem['description'] = $nextDestination->description ?? 'No description available.';
                $itineraryItem['image_url'] = $nextDestination->image_url;
                $itineraryItem['category_tags'] = $nextDestination->category_tags;
                $itineraryItem['average_price'] = $nextDestination->average_price;
                $itineraryItem['family_friendly'] = $nextDestination->family_friendly;
    
                $itinerary[] = $itineraryItem;
                $timeSpent += $travelTime + $visitTime;
                
                $currentLat = $nextDestination->latitude;
                $currentLng = $nextDestination->longitude;
            }
    
            Cache::put($cacheKey, $itinerary, 3600);
    
            return response()->json($itinerary);
    
        } catch (\Exception $e) {
            \Log::error('Error Generating Itinerary:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);
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
        // Clear existing cache for this route
        $cacheKey = "distance_{$lat1}_{$lng1}_{$lat2}_{$lng2}";
        Cache::forget($cacheKey);
        
        // Convert coordinates to radians
        $lat1 = deg2rad($lat1);
        $lng1 = deg2rad($lng1);
        $lat2 = deg2rad($lat2);
        $lng2 = deg2rad($lng2);
        
        // Earth's radius in kilometers
        $earthRadius = 6371;
        
        // Haversine formula
        $dlat = $lat2 - $lat1;
        $dlng = $lng2 - $lng1;
        
        $a = sin($dlat/2) * sin($dlat/2) +
             cos($lat1) * cos($lat2) * 
             sin($dlng/2) * sin($dlng/2);
             
        $c = 2 * atan2(sqrt($a), sqrt(1-$a));
        
        // Calculate base distance
        $baseDistance = $earthRadius * $c;
        
        // Fine-tuned correction factor
        $correctionFactor = 1.55; // Adjusted to get closer to 5.9km
        $adjustedDistance = $baseDistance * $correctionFactor;
        
        \Log::info('Distance calculation:', [
            'from' => ['lat' => rad2deg($lat1), 'lng' => rad2deg($lng1)],
            'to' => ['lat' => rad2deg($lat2), 'lng' => rad2deg($lng2)],
            'base_distance' => $baseDistance,
            'correction_factor' => $correctionFactor,
            'adjusted_distance' => round($adjustedDistance, 2)
        ]);
        
        return round($adjustedDistance, 2);
    }
private function getLocalCorrectionFactor($distance)
{
    // Adjust factors based on actual road patterns in Angeles City
    if ($distance < 2) {
        return 2; // More winding local roads
    } elseif ($distance < 5) {
        return 1.2; // Mix of local and main roads
    } else {
        return 1.15; // Mainly major roads
    }
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
    // Calculate distance between start and destination
    $distance = $this->calculateDistance($startLat, $startLng, $destination->latitude, $destination->longitude);
    
    // If destination is within 500 meters, suggest walking
    if ($distance <= 0.5) {
        // Estimate walking time (assuming average walking speed of 5 km/h)
        $walkingTimeMinutes = ceil(($distance * 1000) / (5000 / 60)); // Convert to minutes
        
        return [
            'name' => $destination->name,
            'type' => $destination->type,
            'travel_time' => $walkingTimeMinutes,
            'visit_time' => $visitTime,
            'image_url' => $destination->image_url ?? null,
            'commute_instructions' => [[
                'instruction' => "This destination is nearby. You can walk there in approximately {$walkingTimeMinutes} minutes (Distance: " . number_format($distance * 1000, 0) . " meters)."
            ]],
            'latitude' => $destination->latitude,
            'longitude' => $destination->longitude,
            'description' => $destination->description ?? 'No description available.'
        ];
    }

    // Get nearby stops for start and end points
    $nearbyStartStops = JeepneyStop::select(DB::raw('*, 
        (6371 * acos(cos(radians(?)) * cos(radians(latitude)) 
        * cos(radians(longitude) - radians(?)) + sin(radians(?)) * sin(radians(latitude)))) AS distance'))
        ->having('distance', '<', 2)
        ->orderBy('distance')
        ->setBindings([$startLat, $startLng, $startLat])
        ->get();

    $nearbyEndStops = JeepneyStop::select(DB::raw('*, 
        (6371 * acos(cos(radians(?)) * cos(radians(latitude)) 
        * cos(radians(longitude) - radians(?)) + sin(radians(?)) * sin(radians(latitude)))) AS distance'))
        ->having('distance', '<', 2)
        ->orderBy('distance')
        ->setBindings([$destination->latitude, $destination->longitude, $destination->latitude])
        ->get();

    if ($nearbyStartStops->isEmpty() || $nearbyEndStops->isEmpty()) {
        return [
            'name' => $destination->name,
            'type' => $destination->type,
            'travel_time' => $travelTime,
            'visit_time' => $visitTime,
            'image_url' => $destination->image_url ?? null,
            'commute_instructions' => [[
                'instruction' => "No direct jeepney route available. Consider taking a tricycle.",
                'image_path' => null,
                'route_name' => null,
                'route_color' => null
            ]],
            'latitude' => $destination->latitude,
            'longitude' => $destination->longitude,
            'description' => $destination->description ?? 'No description available.'
        ];
    }

    // Find connecting routes
    $route = $this->findConnectingRoutes($nearbyStartStops, $nearbyEndStops);
    
    if ($route && !empty($route['routes'])) {
        $routes = collect($route['routes'])->map(function($route, $index) {
            $jeepneyRoute = JeepneyRoute::where('route_name', $route['route_name'])->first();
            
            return [
                'instruction' => sprintf(
                    "%s a %s Jeep (%s jeep) from '%s' to '%s'. Fare: ₱%.2f.",
                    $index === 0 ? "Take" : "Then take",
                    $route['route_name'],
                    $route['route_color'],
                    $route['start_stop'],
                    $route['end_stop'],
                    $route['fare']
                ),
                'image_path' => $jeepneyRoute ? asset('storage/' . $jeepneyRoute->image_path) : null,
                'route_name' => $route['route_name'],
                'route_color' => $route['route_color']
            ];
        })->all();

        return [
            'name' => $destination->name,
            'type' => $destination->type,
            'travel_time' => $travelTime,
            'visit_time' => $visitTime,
            'image_url' => $destination->image_url ?? null,
            'commute_instructions' => $routes,
            'latitude' => $destination->latitude,
            'longitude' => $destination->longitude,
            'description' => $destination->description ?? null
        ];
    }

    // If no routes found, try reverse direction
    $reverseRoute = $this->findConnectingRoutes($nearbyEndStops, $nearbyStartStops);
    
    if ($reverseRoute && !empty($reverseRoute['routes'])) {
        $routes = collect($reverseRoute['routes'])->map(function($route, $index) {
            $jeepneyRoute = JeepneyRoute::where('route_name', $route['route_name'])->first();
            
            return [
                'instruction' => sprintf(
                    "%s a %s Jeep (%s jeep) from '%s' to '%s'. Fare: ₱%.2f.",
                    $index === 0 ? "Take" : "Then take",
                    $route['route_name'],
                    $route['route_color'],
                    $route['start_stop'],
                    $route['end_stop'],
                    $route['fare']
                ),
                'image_path' => $jeepneyRoute ? asset('storage/' . $jeepneyRoute->image_path) : null,
                'route_name' => $route['route_name'],
                'route_color' => $route['route_color']
            ];
        })->all();

        return [
            'name' => $destination->name,
            'type' => $destination->type,
            'travel_time' => $travelTime,
            'visit_time' => $visitTime,
            'image_url' => $destination->image_url ?? null,
            'commute_instructions' => $routes,
            'latitude' => $destination->latitude,
            'longitude' => $destination->longitude,
            'description' => $destination->description ?? null
        ];
    }

    // Fallback when no routes found
    return [
        'name' => $destination->name,
        'type' => $destination->type,
        'travel_time' => $travelTime,
        'visit_time' => $visitTime,
        'image_url' => $destination->image_url ?? null,
        'commute_instructions' => [[
            'instruction' => "No direct jeepney route available. Consider taking a tricycle.",
            'image_path' => null,
            'route_name' => null,
            'route_color' => null
        ]],
        'latitude' => $destination->latitude,
        'longitude' => $destination->longitude,
        'description' => $destination->description ?? 'No description available.'
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
                    $route['fare']
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
                $distance = $this->calculateDistance(
                    $lastStop['end_stop_coords']['latitude'],
                    $lastStop['end_stop_coords']['longitude'],
                    $finalStop->latitude,
                    $finalStop->longitude
                );
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
                    'fare' => $this->calculateFare($distance)                ];
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
                        $distance = $this->calculateDistance(
                            $nearbyBetterStops->latitude,
                            $nearbyBetterStops->longitude,
                            $endStop->latitude,
                            $endStop->longitude
                        );
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
                                'fare' => $this->calculateFare($distance)
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
                $distance = $this->calculateDistance(
                    $startStop->latitude,
                    $startStop->longitude,
                    $endStop->latitude,
                    $endStop->longitude
                );

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
                    'fare' => $this->calculateFare($distance)
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

        foreach ($transfers as $transfer) {
            \Log::info("Found transfer point", [
                'from' => $startStop->stop_name,
                'to' => $transfer->stop_name
            ]);
            $currentRoute = JeepneyRoute::find($startStop->jeepney_route_id);
            $distance = $this->calculateDistance(
                $startStop->latitude,
                $startStop->longitude,
                $transfer->latitude,
                $transfer->longitude
            );
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
                'fare' => $this->calculateFare($distance)
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
                        'fare' => $this->calculateFare($this->calculateDistance(
                            $startStop->latitude,
                            $startStop->longitude,
                            $endStop->latitude,
                            $endStop->longitude
                        ))
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
        'fare' => $this->calculateFare($this->calculateDistance(
            $startStop->latitude,
            $startStop->longitude,
            $endStop->latitude,
            $endStop->longitude
        ))
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
}
private function calculatePathDistance($stops)
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
            'startLng' => 'required|numeric',
            'currentIndex' => 'required|integer',
            'fullItinerary' => 'required|array'
        ]);

        $fullItinerary = $validatedData['fullItinerary'];
        $currentIndex = $validatedData['currentIndex'];
        $updatedDestinations = [];

        // Start with user's location
        $previousLat = $validatedData['startLat'];
        $previousLng = $validatedData['startLng'];

        // Create destination object for the new destination
        $newDestObj = new \stdClass();
        $newDestObj->name = $validatedData['newDestination']['name'];
        $newDestObj->type = $validatedData['newDestination']['type'];
        $newDestObj->latitude = $validatedData['newDestination']['latitude'];
        $newDestObj->longitude = $validatedData['newDestination']['longitude'];
        $newDestObj->description = $validatedData['newDestination']['description'] ?? '';
        $newDestObj->image_url = $validatedData['newDestination']['image_url'] ?? null;
        $newDestObj->id = $validatedData['newDestination']['id'] ?? null;

        // First, update the changed destination
        $travelTime = $this->getTravelTimeFromGoogle(
            $previousLat,
            $previousLng,
            $newDestObj->latitude,
            $newDestObj->longitude
        );

        $visitTime = $newDestObj->type === 'restaurant' ? 40 : 20;

        $updatedItem = $this->addToItineraryWithCommute(
            $newDestObj,
            $travelTime,
            $visitTime,
            $previousLat,
            $previousLng
        );

        // Add the first updated item
        $updatedDestinations[] = array_merge(
            (array)$newDestObj,
            [
                'travel_time' => $updatedItem['travel_time'],
                'visit_time' => $updatedItem['visit_time'],
                'commute_instructions' => $updatedItem['commute_instructions']
            ]
        );

        // Update subsequent destinations
        $previousLat = $newDestObj->latitude;
        $previousLng = $newDestObj->longitude;

        // Loop through remaining destinations and update their commute instructions
        for ($i = $currentIndex + 1; $i < count($fullItinerary); $i++) {
            $currentDest = $fullItinerary[$i];
            
            // Create destination object for current item
            $destObj = new \stdClass();
            $destObj->name = $currentDest['name'];
            $destObj->type = $currentDest['type'];
            $destObj->latitude = $currentDest['latitude'];
            $destObj->longitude = $currentDest['longitude'];
            $destObj->description = $currentDest['description'] ?? '';
            $destObj->image_url = $currentDest['image_url'] ?? null;
            $destObj->id = $currentDest['id'] ?? null;

            // Calculate new travel times and commute instructions
            $travelTime = $this->getTravelTimeFromGoogle(
                $previousLat,
                $previousLng,
                $destObj->latitude,
                $destObj->longitude
            );

            $visitTime = $destObj->type === 'restaurant' ? 40 : 20;

            $updatedItem = $this->addToItineraryWithCommute(
                $destObj,
                $travelTime,
                $visitTime,
                $previousLat,
                $previousLng
            );

            // Add to updated destinations array
            $updatedDestinations[] = array_merge(
                (array)$destObj,
                [
                    'travel_time' => $updatedItem['travel_time'],
                    'visit_time' => $updatedItem['visit_time'],
                    'commute_instructions' => $updatedItem['commute_instructions']
                ]
            );

            // Update previous coordinates for next iteration
            $previousLat = $destObj->latitude;
            $previousLng = $destObj->longitude;
        }

        return response()->json([
            'success' => true,
            'currentIndex' => $currentIndex,
            'updatedDestinations' => $updatedDestinations
        ]);

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
            'name' => 'Itinerary ' . now()->format('Y-m-d'),
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

private function calculateFare($distance)
{
    // Debug logging
    \Log::info('Fare calculation input:', ['distance' => $distance]);

    // Base fare for first 4km
    $baseFare = 13.0;
    
    // If distance is less than or equal to 4km, return base fare
    if ($distance <= 4) {
        \Log::info('Base fare applied:', ['fare' => $baseFare]);
        return $baseFare;
    }
    
    // Calculate additional kilometers beyond 4km
    $additionalKm = $distance - 4;
    
    // Calculate additional fare (1.5 pesos per additional km)
    $additionalFare = $additionalKm * 1.5;
    
    // Calculate total fare and round to nearest 0.50
    $totalFare = $baseFare + $additionalFare;
    $roundedFare = round($totalFare * 2) / 2;
    
    \Log::info('Fare calculation details:', [
        'distance' => $distance,
        'additionalKm' => $additionalKm,
        'additionalFare' => $additionalFare,
        'totalFare' => $totalFare,
        'roundedFare' => $roundedFare
    ]);
    
    return $roundedFare;
}
}