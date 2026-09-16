<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Tambahkan field ke tabel users
        Schema::table('users', function (Blueprint $table) {
            $table->string('marital_status')->nullable()->after('gender');
            $table->string('occupation')->nullable()->after('marital_status');
            $table->string('nationality')->nullable()->after('occupation');
        });

        // 2. Pindahkan data dari owner_profiles ke users
        // Karena ada constraint foreign key user_id di owner_profiles
        DB::table('owner_profiles')->orderBy('id')->chunk(100, function ($ownerProfiles) {
            foreach ($ownerProfiles as $profile) {
                DB::table('users')
                    ->where('id', $profile->user_id)
                    ->update([
                        'marital_status' => $profile->marital_status ?? null,
                        'occupation' => $profile->occupation ?? null,
                        'nationality' => $profile->nationality ?? null,
                    ]);
            }
        });

        // 3. Hapus field dari owner_profiles termasuk agama
        Schema::table('owner_profiles', function (Blueprint $table) {
            $table->dropColumn(['religion', 'marital_status', 'occupation', 'nationality']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 1. Tambahkan kembali field ke owner_profiles
        Schema::table('owner_profiles', function (Blueprint $table) {
            $table->string('religion')->nullable()->after('ktp_photo');
            $table->string('marital_status')->nullable()->after('religion');
            $table->string('occupation')->nullable()->after('marital_status');
            $table->string('nationality')->nullable()->after('occupation');
        });

        // 2. Pindahkan kembali data dari users ke owner_profiles
        DB::table('users')->whereNotNull('marital_status')->orderBy('id')->chunk(100, function ($users) {
            foreach ($users as $user) {
                DB::table('owner_profiles')
                    ->where('user_id', $user->id)
                    ->update([
                        'marital_status' => $user->marital_status,
                        'occupation' => $user->occupation,
                        'nationality' => $user->nationality,
                    ]);
            }
        });

        // 3. Hapus field dari users
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['marital_status', 'occupation', 'nationality']);
        });
    }
};
