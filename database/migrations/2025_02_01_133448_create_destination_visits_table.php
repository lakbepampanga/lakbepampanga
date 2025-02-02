<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('destination_visits', function (Blueprint $table) {
            // Primary key; Laravel's increments() creates an unsigned integer.
            // This is independent of the foreign keys.
            $table->increments('id');
            
            // Use plain integer here because destinations.id is defined as int(11) (signed)
            $table->integer('destination_id');
            
            // Use unsignedBigInteger to match the type of users.id (bigint(20) UNSIGNED)
            $table->unsignedBigInteger('user_id');
            
            // Use unsignedBigInteger to match the type of saved_itineraries.id (bigint(20) UNSIGNED)
            $table->unsignedBigInteger('saved_itinerary_id');
            
            $table->timestamp('visited_at')->nullable();
            $table->timestamps();

            // Define foreign key constraints
            $table->foreign('destination_id')
                  ->references('id')
                  ->on('destinations')
                  ->onDelete('cascade');
                  
            $table->foreign('user_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');
                  
            $table->foreign('saved_itinerary_id')
                  ->references('id')
                  ->on('saved_itineraries')
                  ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('destination_visits');
    }
};
