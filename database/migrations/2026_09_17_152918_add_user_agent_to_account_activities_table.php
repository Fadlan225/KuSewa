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
        Schema::table('account_activities', function (Blueprint $table) {
            $table->text('user_agent')->nullable()->after('ip_address');
            $table->string('os')->nullable()->after('user_agent');
            $table->string('device_name')->nullable()->after('os');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('account_activities', function (Blueprint $table) {
            $table->dropColumn(['user_agent', 'os', 'device_name']);
        });
    }
};
