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
        Schema::create('asset_type_facilities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_type_id')->constrained()->onDelete('cascade');
            $table->foreignId('facility_id')->constrained()->onDelete('restrict');
            $table->enum('scope', ['asset', 'unit']);
            $table->timestamps();

            $table->unique(['asset_type_id', 'facility_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asset_type_facilities');
    }
};
