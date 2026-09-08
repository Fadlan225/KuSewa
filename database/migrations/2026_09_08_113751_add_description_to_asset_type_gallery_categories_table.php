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
            $table->text('description')->nullable()->after('sort_order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('asset_type_gallery_categories', function (Blueprint $table) {
            $table->dropColumn('description');
        });
    }
};
