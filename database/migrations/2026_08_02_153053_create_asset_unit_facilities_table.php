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
        Schema::create('asset_unit_facilities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_unit_id')->constrained()->onDelete('cascade');
            $table->foreignId('facility_id')->constrained()->onDelete('restrict');
            $table->timestamps();

            $table->unique(['asset_unit_id', 'facility_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asset_unit_facilities');
    }
};
