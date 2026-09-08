<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('asset_type_gallery_categories', function (Blueprint $table) {
            $table->integer('min_photos')->default(0)->after('description');
            $table->integer('max_photos')->nullable()->after('min_photos');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('asset_type_gallery_categories', function (Blueprint $table) {
            //
        });
    }
};
