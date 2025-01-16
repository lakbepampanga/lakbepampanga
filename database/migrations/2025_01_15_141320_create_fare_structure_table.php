<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFareStructureTable extends Migration
{
    public function up()
    {
        // Create the fare_structure table
        Schema::create('fare_structure', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('jeepney_route_id');  // Foreign key to jeepney_routes
            $table->decimal('base_fare', 8, 2);  // Base fare for the route
            $table->decimal('fare_per_km', 8, 2);  // Additional fare per km
            $table->timestamps();

            // Add foreign key constraint to jeepney_route_id
            $table->foreign('jeepney_route_id')
                  ->references('id')
                  ->on('jeepney_routes')
                  ->onDelete('cascade');  // Cascade delete when the associated jeepney route is deleted
        });
    }

    public function down()
    {
        Schema::dropIfExists('fare_structure');
    }
}

