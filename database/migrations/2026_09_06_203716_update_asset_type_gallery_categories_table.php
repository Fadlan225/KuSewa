<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Hapus tabel lama
        Schema::dropIfExists('asset_type_mandatory_gallery_categories');

        // Buat tabel baru dengan fungsionalitas tambahan (is_mandatory, sort_order)
        Schema::create('asset_type_gallery_categories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('asset_type_id');
            $table->unsignedBigInteger('galery_category_id');
            $table->enum('scope', ['asset', 'unit'])->default('asset');
            $table->boolean('is_mandatory')->default(false);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            // FK dengan nama pendek
            $table->foreign('asset_type_id', 'atgc_at_fk')
                  ->references('id')->on('asset_types')->onDelete('cascade');
            $table->foreign('galery_category_id', 'atgc_gc_fk')
                  ->references('id')->on('galery_categories')->onDelete('cascade');

            $table->unique(['asset_type_id', 'galery_category_id', 'scope'], 'atgc_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asset_type_gallery_categories');

        // Kembalikan ke struktur lama jika di-rollback
        Schema::create('asset_type_mandatory_gallery_categories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('asset_type_id');
            $table->unsignedBigInteger('galery_category_id');
            $table->enum('scope', ['asset', 'unit'])->default('asset');
            $table->timestamps();

            $table->foreign('asset_type_id', 'atmgc_at_fk')
                  ->references('id')->on('asset_types')->onDelete('cascade');
            $table->foreign('galery_category_id', 'atmgc_gc_fk')
                  ->references('id')->on('galery_categories')->onDelete('cascade');

            $table->unique(['asset_type_id', 'galery_category_id', 'scope'], 'atmgc_unique');
        });
    }
};
