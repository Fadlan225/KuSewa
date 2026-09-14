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
        Schema::table('asset_type_mandatory_categories', function (Blueprint $table) {
            // Drop foreign keys that rely on the old unique index
            $table->dropForeign(['asset_type_id']);
            $table->dropForeign(['facility_category_id']);

            // Drop the old unique constraint that was forgotten in previous migration
            $table->dropUnique('atmc_unique');

            // Re-add foreign keys
            $table->foreign('asset_type_id')->references('id')->on('asset_types')->onDelete('cascade');
            $table->foreign('facility_category_id')->references('id')->on('facility_categories')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('asset_type_mandatory_categories', function (Blueprint $table) {
            $table->unique(['asset_type_id', 'facility_category_id'], 'atmc_unique');
        });
    }
};
