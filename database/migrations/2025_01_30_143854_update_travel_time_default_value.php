<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('destinations', function (Blueprint $table) {
            $table->integer('travel_time')->default(1)->change();
        });
    }
    
    public function down()
    {
        Schema::table('destinations', function (Blueprint $table) {
            $table->integer('travel_time')->change();
        });
    }
    
};
