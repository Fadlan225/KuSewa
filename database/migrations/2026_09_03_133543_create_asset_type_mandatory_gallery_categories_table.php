<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asset_type_mandatory_gallery_categories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('asset_type_id');
            $table->unsignedBigInteger('galery_category_id');
            // scope: 'asset' = galeri wajib aset, 'unit' = galeri wajib unit
            $table->enum('scope', ['asset', 'unit'])->default('asset');
            $table->timestamps();

            // FK dengan nama pendek agar tidak melebihi limit MySQL (64 char)
            $table->foreign('asset_type_id', 'atmgc_at_fk')
                  ->references('id')->on('asset_types')->onDelete('cascade');
            $table->foreign('galery_category_id', 'atmgc_gc_fk')
                  ->references('id')->on('galery_categories')->onDelete('cascade');

            $table->unique(['asset_type_id', 'galery_category_id', 'scope'], 'atmgc_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asset_type_mandatory_gallery_categories');
    }
};
