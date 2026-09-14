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
            $table->string('name')->default('Default Fee')->after('id');
            $table->text('description')->nullable()->after('name');
            $table->integer('sort_order')->default(1)->after('fee_value');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('service_fees', function (Blueprint $table) {
            $table->dropColumn(['name', 'description', 'sort_order']);
        });
    }
};
