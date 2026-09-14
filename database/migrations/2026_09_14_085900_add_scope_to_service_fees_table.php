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
        Schema::table('service_fees', function (Blueprint $table) {
            $table->enum('scope', ['asset', 'unit'])->default('asset')->after('asset_type_id');
            $table->index(['asset_type_id', 'scope', 'sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('service_fees', function (Blueprint $table) {
            $table->dropIndex(['asset_type_id', 'scope', 'sort_order']);
            $table->dropColumn('scope');
        });
    }
};
