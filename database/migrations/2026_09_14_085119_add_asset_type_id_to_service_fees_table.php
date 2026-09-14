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
            $table->foreignId('asset_type_id')
                ->nullable()
                ->after('id')
                ->constrained('asset_types')
                ->nullOnDelete();

            $table->index(['asset_type_id', 'sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('service_fees', function (Blueprint $table) {
            $table->dropForeign(['asset_type_id']);
            $table->dropIndex(['asset_type_id', 'sort_order']);
            $table->dropColumn('asset_type_id');
        });
    }
};
