<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->string('property_name')->nullable();
            $table->string('property_type')->nullable();
            $table->string('sub_category')->nullable();
            $table->string('rental_scheme')->nullable();
            $table->text('description')->nullable();
            $table->unsignedInteger('room_count')->nullable();
            $table->unsignedInteger('capacity')->nullable();
            $table->unsignedInteger('floor_count')->nullable();
            $table->decimal('land_area', 12, 2)->nullable();
            $table->decimal('building_area', 12, 2)->nullable();
            $table->string('dimensions')->nullable();
            $table->json('room_types')->nullable();
            $table->string('district')->nullable();
            $table->string('country')->nullable();
            $table->string('province')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->json('facilities')->nullable();
            $table->decimal('deposit', 15, 2)->nullable();
            $table->json('property_photos')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->dropColumn([
                'property_name', 'property_type', 'sub_category', 'rental_scheme', 'description',
                'room_count', 'capacity', 'floor_count', 'land_area', 'building_area', 'dimensions',
                'room_types', 'district', 'country', 'province', 'latitude', 'longitude', 'facilities',
                'deposit', 'property_photos',
            ]);
        });
    }
};