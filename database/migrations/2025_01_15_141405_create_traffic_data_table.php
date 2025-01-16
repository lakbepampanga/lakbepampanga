<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTrafficDataTable extends Migration
{
    public function up()
    {
        // Create the traffic_data table
        Schema::create('traffic_data', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('jeepney_route_id');  // Foreign key to jeepney_routes
            $table->unsignedBigInteger('segment_id');  // Foreign key to route_segments
            $table->integer('average_traffic_delay');  // Average delay due to traffic (in minutes)
            $table->timestamps();

            // Add foreign key constraints
            $table->foreign('jeepney_route_id')
                  ->references('id')
                  ->on('jeepney_routes')
                  ->onDelete('cascade');
            $table->foreign('segment_id')
                  ->references('id')
                  ->on('route_segments')
                  ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('traffic_data');
    }
}

