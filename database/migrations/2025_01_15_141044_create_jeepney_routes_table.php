<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateJeepneyRoutesTable extends Migration
{
    public function up()
    {
        // Creating the jeepney_routes table
        Schema::create('jeepney_routes', function (Blueprint $table) {
            $table->id(); // Auto increments the primary key and creates unsignedBigInteger
            $table->string('route_name');
            $table->text('description')->nullable();
            $table->timestamps(); // Created_at and updated_at columns
        });
    }

    public function down()
    {
        Schema::dropIfExists('jeepney_routes');
    }
}

