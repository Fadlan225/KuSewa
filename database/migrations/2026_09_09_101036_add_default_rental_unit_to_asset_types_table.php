<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('asset_types', function (Blueprint $table) {
            $table->enum('default_rental_unit', ['hour', 'night', 'day', 'week', 'month'])
                  ->default('month')
                  ->after('payment_countdown_minutes');
        });
    }

    public function down(): void
    {
        Schema::table('asset_types', function (Blueprint $table) {
            $table->dropColumn('default_rental_unit');
        });
    }
};
