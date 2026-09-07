<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('asset_type_mandatory_categories', function (Blueprint $table) {
            // Tambah kolom scope
            $table->enum('scope', ['asset', 'unit'])->default('asset')->after('facility_category_id');

            // Tambah unique constraint baru yang include scope
            $table->unique(['asset_type_id', 'facility_category_id', 'scope'], 'atmc_scope_unique');
        });
    }

    public function down(): void
    {
        Schema::table('asset_type_mandatory_categories', function (Blueprint $table) {
            $table->dropUnique('atmc_scope_unique');
            $table->dropColumn('scope');
        });
    }
};
