<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Directly modify the ENUM values of the type column
        DB::statement("ALTER TABLE destinations MODIFY COLUMN type ENUM(
            'landmark',
            'restaurant',
            'museum',
            'shopping',
            'nature',
            'religious',
            'entertainment',
            'cultural',
            'park',
            'market'
        ) NOT NULL");

        // Add other new columns if they don't exist
        if (!Schema::hasColumn('destinations', 'category_tags')) {
            Schema::table('destinations', function (Blueprint $table) {
                $table->string('category_tags')->nullable();
            });
        }

        if (!Schema::hasColumn('destinations', 'average_price')) {
            Schema::table('destinations', function (Blueprint $table) {
                $table->decimal('average_price', 10, 2)->nullable();
            });
        }

        if (!Schema::hasColumn('destinations', 'family_friendly')) {
            Schema::table('destinations', function (Blueprint $table) {
                $table->boolean('family_friendly')->default(true);
            });
        }

        if (!Schema::hasColumn('destinations', 'recommended_visit_time')) {
            Schema::table('destinations', function (Blueprint $table) {
                $table->integer('recommended_visit_time')->nullable();
            });
        }
    }

    public function down()
    {
        // Revert the type column to original enum values
        DB::statement("ALTER TABLE destinations MODIFY COLUMN type ENUM('landmark', 'restaurant') NOT NULL");

        // Drop the new columns if they exist
        Schema::table('destinations', function (Blueprint $table) {
            if (Schema::hasColumn('destinations', 'category_tags')) {
                $table->dropColumn('category_tags');
            }
            if (Schema::hasColumn('destinations', 'average_price')) {
                $table->dropColumn('average_price');
            }
            if (Schema::hasColumn('destinations', 'family_friendly')) {
                $table->dropColumn('family_friendly');
            }
            if (Schema::hasColumn('destinations', 'recommended_visit_time')) {
                $table->dropColumn('recommended_visit_time');
            }
        });
    }
};