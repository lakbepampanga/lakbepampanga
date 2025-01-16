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
        // Check if the 'destinations' table exists, if not, create it
        if (!Schema::hasTable('destinations')) {
            Schema::create('destinations', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->decimal('latitude', 10, 7);
                $table->decimal('longitude', 10, 7);
                $table->text('description')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Drop the 'destinations' table if it exists
        Schema::dropIfExists('destinations');
    }
};
