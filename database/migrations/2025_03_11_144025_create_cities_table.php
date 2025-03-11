<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateCitiesTable extends Migration
{
    public function up()
    {
        Schema::create('cities', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });
        
        // Insert default cities
        DB::table('cities')->insert([
            ['name' => 'Angeles', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Mabalacat', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Magalang', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down()
    {
        Schema::dropIfExists('cities');
    }
}