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