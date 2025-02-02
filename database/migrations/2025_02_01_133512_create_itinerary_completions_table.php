<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('itinerary_completions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('saved_itinerary_id');
            $table->unsignedBigInteger('user_id');
            $table->timestamp('completed_at');
            $table->timestamps();

            $table->foreign('saved_itinerary_id')
                  ->references('id')
                  ->on('saved_itineraries')
                  ->onDelete('cascade');

            $table->foreign('user_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('itinerary_completions');
    }
};
