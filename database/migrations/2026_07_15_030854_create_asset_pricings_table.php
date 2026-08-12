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
        Schema::create('asset_pricings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_id')->nullable()->constrained()->onDelete('restrict');
            $table->foreignId('asset_unit_id')->nullable()->constrained()->onDelete('restrict');
            $table->integer('duration');
            $table->enum('rental_unit',['hour', 'night', 'day','week','month']);
            $table->decimal('price',15,2);
            $table->unique(['asset_id', 'asset_unit_id', 'duration', 'rental_unit'], 'asset_pricings_unique');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asset_pricings');
    }
};
