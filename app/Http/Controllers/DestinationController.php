<?php

namespace App\Http\Controllers;

use App\Models\Destination;
use Illuminate\Http\Request;
use App\Models\DestinationVisit;
use App\Models\ItineraryCompletion;

class DestinationController extends Controller
{
    // Fetch nearby destinations and calculate distances
    public function getRoutes(Request $request)
    {
        $userLat = $request->input('latitude');
        $userLng = $request->input('longitude');
        $interests = $request->input('interests', []);
    
        $query = Destination::select('*')
            ->selectRaw('(
                6371 * acos(
                    cos(radians(?)) * cos(radians(latitude)) *
                    cos(radians(longitude) - radians(?)) +
                    sin(radians(?)) * sin(radians(latitude))
                )
            ) as distance', [$userLat, $userLng, $userLat]);
    
        if (!empty($interests)) {
            $query->whereIn('type', $interests);
        }
    
        $destinations = $query
            ->orderBy('distance')
            ->get()
            ->map(function ($destination) {
                return [
                    'id' => $destination->id,
                    'name' => $destination->name,
                    'latitude' => $destination->latitude,
                    'longitude' => $destination->longitude,
                    'description' => $destination->description,
                    'image_url' => $destination->image_url,
                    'travel_time' => $destination->travel_time,
                    'city' => $destination->city,
                    'type' => $destination->type,
                    'priority' => $destination->priority,
                    'opening_time' => $destination->opening_time,
                    'closing_time' => $destination->closing_time,
                    'route_id' => $destination->route_id,
                    'distance' => $destination->distance,
                    'category_tags' => $destination->category_tags,
                    'average_price' => $destination->average_price,
                    'family_friendly' => $destination->family_friendly,
                    'recommended_visit_time' => $destination->recommended_visit_time
                ];
            });
    
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

    // Helper method to validate destination types
    private function getValidDestinationTypes()
    {
        return [
            'landmark',
            'restaurant',
            'museum',
            'shopping',
            'nature',
            'religious',
            'entertainment',
            'cultural',
            'park',
            'market'
        ];
    }

    // Helper method to check if a type is valid
    private function isValidType($type)
    {
        return in_array($type, $this->getValidDestinationTypes());
    }

    // Helper method to get type-specific icon and color
    private function getTypeConfig($type)
    {
        $configs = [
            'landmark' => ['icon' => 'bi-geo-alt', 'color' => 'bg-primary'],
            'restaurant' => ['icon' => 'bi-shop', 'color' => 'bg-success'],
            'museum' => ['icon' => 'bi-bank', 'color' => 'bg-info'],
            'shopping' => ['icon' => 'bi-bag', 'color' => 'bg-warning'],
            'nature' => ['icon' => 'bi-tree', 'color' => 'bg-success'],
            'religious' => ['icon' => 'bi-building', 'color' => 'bg-primary'],
            'entertainment' => ['icon' => 'bi-film', 'color' => 'bg-danger'],
            'cultural' => ['icon' => 'bi-people', 'color' => 'bg-secondary'],
            'park' => ['icon' => 'bi-flower1', 'color' => 'bg-success'],
            'market' => ['icon' => 'bi-shop-window', 'color' => 'bg-warning']
        ];

        return $configs[$type] ?? ['icon' => 'bi-geo-alt', 'color' => 'bg-primary'];
    }
}