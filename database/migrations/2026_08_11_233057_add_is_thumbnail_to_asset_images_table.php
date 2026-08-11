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
        Schema::table('asset_images', function (Blueprint $table) {
            $table->boolean('is_thumbnail')->default(false)->after('gallery_category_id');
            $table->unsignedBigInteger('gallery_category_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('asset_images', function (Blueprint $table) {
            $table->dropColumn('is_thumbnail');
            $table->unsignedBigInteger('gallery_category_id')->nullable(false)->change();
        });
    }
};
