<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFareStructuresTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('fare_structures', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('jeepney_route_id'); // Foreign key to jeepney_routes
            $table->decimal('base_fare', 8, 2);            // Base fare
            $table->decimal('fare_per_km', 8, 2)->nullable(); // Optional fare per km
            $table->timestamps();

            // Add foreign key constraint
            $table->foreign('jeepney_route_id')
                  ->references('id')->on('jeepney_routes')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('fare_structures');
    }
}
