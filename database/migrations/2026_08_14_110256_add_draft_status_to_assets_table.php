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
        // Modify ENUM column
        DB::statement("ALTER TABLE assets MODIFY COLUMN status ENUM('draft', 'pending', 'approved', 'rejected', 'inactive') NOT NULL DEFAULT 'draft'");
        
        Schema::table('assets', function (Blueprint $table) {
            $table->json('draft_payload')->nullable()->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            $table->dropColumn('draft_payload');
        });
        
        // Revert ENUM column
        DB::statement("ALTER TABLE assets MODIFY COLUMN status ENUM('pending', 'approved', 'rejected', 'inactive') NOT NULL DEFAULT 'pending'");
    }
};

