<?php

namespace App\Http\Controllers;

use App\Models\Destination;
use Illuminate\Http\Request;
use App\Models\DestinationVisit;
use App\Models\ItineraryCompletion;  // Add this line

class DestinationController extends Controller
{
    // Fetch nearby destinations and calculate distances
    public function getRoutes(Request $request)
    {
        $userLat = $request->input('latitude');
        $userLng = $request->input('longitude');

        $destinations = Destination::all();

        // Calculate distances using Haversine formula
        $destinations = $destinations->map(function ($destination) use ($userLat, $userLng) {
            $distance = $this->haversineGreatCircleDistance(
                $userLat,
                $userLng,
                $destination->latitude,
                $destination->longitude
            );
            
            // Include the distance and image_url in the destination data
            return [
                'id' => $destination->id,
                'name' => $destination->name,
                'latitude' => $destination->latitude,
                'longitude' => $destination->longitude,
                'description' => $destination->description,
                'image_url' => $destination->image_url, // This will use your accessor
                'travel_time' => $destination->travel_time,
                'city' => $destination->city,
                'type' => $destination->type,
                'priority' => $destination->priority,
                'opening_time' => $destination->opening_time,
                'closing_time' => $destination->closing_time,
                'route_id' => $destination->route_id,
                'distance' => $distance
            ];
        })->sortBy('distance'); // Sort by nearest distance

        return response()->json($destinations);
    }

    // Haversine formula for distance calculation
    private function haversineGreatCircleDistance($lat1, $lng1, $lat2, $lng2)
    {
        $earthRadius = 6371; // Earth radius in kilometers

        $latDelta = deg2rad($lat2 - $lat1);
        $lngDelta = deg2rad($lng2 - $lng1);

        $a = sin($latDelta / 2) ** 2 +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($lngDelta / 2) ** 2;

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c; // Distance in kilometers
    }

}
