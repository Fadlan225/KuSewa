<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $categories = ['Sampul Utama', 'Lainnya'];
        foreach ($categories as $cat) {
            $exists = DB::table('galery_categories')->where('name', $cat)->exists();
            if (!$exists) {
                DB::table('galery_categories')->insert([
                    'name' => $cat,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        } 
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Don't remove in down() just in case it breaks existing data
    }
};
