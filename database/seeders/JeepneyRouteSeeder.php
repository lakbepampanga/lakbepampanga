<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class JeepneyRouteSeeder extends Seeder
{
    public function run()
    {
        // Insert jeepney routes
        DB::table('jeepney_routes')->insert([
            ['route_name' => 'Route 1 - Angeles Main Avenue'],
            ['route_name' => 'Route 2 - Clark to Dau Terminal'],
        ]);

        // Insert jeepney stops
        DB::table('jeepney_stops')->insert([
            ['jeepney_route_id' => 1, 'stop_name' => 'Holy Rosary Parish Church', 'latitude' => 15.1347621, 'longitude' => 120.5903796, 'order_in_route' => 1],
            ['jeepney_route_id' => 1, 'stop_name' => 'Angeles University Foundation', 'latitude' => 15.1445623, 'longitude' => 120.5874456, 'order_in_route' => 2],
            ['jeepney_route_id' => 2, 'stop_name' => 'SM Clark', 'latitude' => 15.175053, 'longitude' => 120.581648, 'order_in_route' => 1],
            ['jeepney_route_id' => 2, 'stop_name' => 'Dau Bus Terminal', 'latitude' => 15.1878, 'longitude' => 120.5897, 'order_in_route' => 2],
        ]);
    }
}
