<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // asset_categories: tambah description + is_active
        Schema::table('asset_categories', function (Blueprint $table) {
            $table->string('description')->nullable()->after('name');
            $table->boolean('is_active')->default(true)->after('icon');
        });

        // asset_types: tambah description + is_active
        Schema::table('asset_types', function (Blueprint $table) {
            $table->string('description')->nullable()->after('name');
            $table->boolean('is_active')->default(true)->after('description');
        });

        // facility_categories: tambah is_active
        Schema::table('facility_categories', function (Blueprint $table) {
            $table->boolean('is_active')->default(true)->after('sort_order');
        });
    }

    public function down(): void
    {
        Schema::table('asset_categories', function (Blueprint $table) {
            $table->dropColumn(['description', 'is_active']);
        });

        Schema::table('asset_types', function (Blueprint $table) {
            $table->dropColumn(['description', 'is_active']);
        });

        Schema::table('facility_categories', function (Blueprint $table) {
            $table->dropColumn('is_active');
        });
    }
};
