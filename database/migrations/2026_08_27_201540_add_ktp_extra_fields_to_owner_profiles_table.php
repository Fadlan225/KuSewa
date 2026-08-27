<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Menambahkan field dari KTP ke tabel owner_profiles.
     * Semua nullable agar data lama tidak terpengaruh.
     */
    public function up(): void
    {
        Schema::table('owner_profiles', function (Blueprint $table) {
            $table->string('religion')->nullable()->after('ktp_photo');
            $table->string('marital_status')->nullable()->after('religion');
            $table->string('occupation')->nullable()->after('marital_status');
            $table->string('nationality')->nullable()->default('WNI')->after('occupation');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('owner_profiles', function (Blueprint $table) {
            $table->dropColumn(['religion', 'marital_status', 'occupation', 'nationality']);
        });
    }
};
