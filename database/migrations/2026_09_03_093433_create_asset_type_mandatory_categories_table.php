<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asset_type_mandatory_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_type_id')->constrained('asset_types')->onDelete('cascade');
            $table->foreignId('facility_category_id')->constrained('facility_categories')->onDelete('cascade');
            $table->timestamps();

            $table->unique(['asset_type_id', 'facility_category_id'], 'atmc_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asset_type_mandatory_categories');
    }
};
