<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRouteSegmentsTable extends Migration
{
    public function up()
    {
        // Create the route_segments table
        Schema::create('route_segments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('jeepney_route_id');  // Foreign key to jeepney_routes
            $table->unsignedBigInteger('start_stop_id');  // Foreign key to jeepney_stops
            $table->unsignedBigInteger('end_stop_id');  // Foreign key to jeepney_stops
            $table->decimal('distance', 8, 2);  // Distance between the start and end stops (in km)
            $table->integer('estimated_travel_time');  // Estimated travel time (in minutes)
            $table->timestamps();

            // Add foreign key constraints
            $table->foreign('jeepney_route_id')
                  ->references('id')
                  ->on('jeepney_routes')
                  ->onDelete('cascade');
            $table->foreign('start_stop_id')
                  ->references('id')
                  ->on('jeepney_stops')
                  ->onDelete('cascade');
            $table->foreign('end_stop_id')
                  ->references('id')
                  ->on('jeepney_stops')
                  ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('route_segments');
    }
}

