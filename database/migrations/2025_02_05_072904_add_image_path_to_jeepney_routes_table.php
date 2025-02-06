<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('jeepney_routes', function (Blueprint $table) {
            $table->string('image_path')->nullable()->after('description'); // Adds column after 'description'
        });
    }

    public function down()
    {
        Schema::table('jeepney_routes', function (Blueprint $table) {
            $table->dropColumn('image_path');
        });
    }
};
