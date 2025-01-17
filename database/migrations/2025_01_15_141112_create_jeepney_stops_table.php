<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateJeepneyStopsTable extends Migration
{
    public function up()
    {
        // Creating the jeepney_stops table
        Schema::create('jeepney_stops', function (Blueprint $table) {
            $table->id(); // Auto increments the primary key
            $table->unsignedBigInteger('jeepney_route_id');  // Ensure the foreign key column is unsignedBigInteger
            $table->string('stop_name');
            $table->decimal('latitude', 10, 7)->nullable(); // Latitude column
            $table->decimal('longitude', 10, 7)->nullable(); // Longitude column
            $table->integer('order_in_route')->default(0); // Order of stop in the route
            $table->timestamps();

            // Defining the foreign key relationship
            $table->foreign('jeepney_route_id')
                ->references('id')
                ->on('jeepney_routes')
                ->onDelete('cascade');  // Ensures cascading delete if the related jeepney route is deleted
        });
    }

    public function down()
    {
        Schema::dropIfExists('jeepney_stops');
    }
}
