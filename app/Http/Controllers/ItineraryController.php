<?php

namespace App\Http\Controllers;

use App\Models\Destination;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
    
            $destinations = Destination::all();
            if ($destinations->isEmpty()) {
                return response()->json(['error' => 'No destinations available.'], 400);
            }
    
            $itinerary = [];
            $timeSpent = 0;
            $foodStopsAdded = 0;
            $landmarkCount = 0;
            $maxLandmarksBeforeFoodStop = 3; // Maximum landmarks before adding a food stop
            $maxFoodStops = floor($availableTime / 240); // Max food stops based on 4-hour intervals
    
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
    
                $travelTime = $this->estimateTravelTime(
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
    
                // Add description explicitly to the itinerary response
                $itineraryItem['description'] = $nextDestination->description ?? 'No description available for this destination.';
                $itinerary[] = $itineraryItem;
    
                $timeSpent += $travelTime + $visitTime;
    
                $latitude = $nextDestination->latitude;
                $longitude = $nextDestination->longitude;
    
                $destinations = $destinations->reject(fn($d) => $d->id === $nextDestination->id);
    
                // Track food stops and landmarks
                if ($nextDestination->type === 'restaurant') {
                    $foodStopsAdded++;
                    $landmarkCount = 0; // Reset landmark count after a food stop
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

    private function estimateTravelTime($startLat, $startLng, $endLat, $endLng)
    {
        $distance = $this->calculateDistance($startLat, $startLng, $endLat, $endLng);
    
        // Assume an average speed of 20 km/h in city traffic
        $averageSpeed = 20; // in km/h
        $travelTime = ($distance / $averageSpeed) * 60; // Convert hours to minutes
    
        return ceil($travelTime); // Round up to the nearest minute
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
            $commuteInstructions = sprintf(
                "Take a %s Jeep (%s jeep) from '%s' to '%s'. Fare: ₱%.2f.",
                $jeepneyInfo['route_name'],
                $jeepneyInfo['route_color'],
                $jeepneyInfo['start_stop'],
                $jeepneyInfo['end_stop'],
                $jeepneyInfo['base_fare']
            );

            // Use the jeepney's estimated travel time
            $travelTime = $jeepneyInfo['estimated_time'];
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
}
