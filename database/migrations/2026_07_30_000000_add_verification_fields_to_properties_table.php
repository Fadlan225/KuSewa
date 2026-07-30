<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            if (!Schema::hasColumn('properties', 'verification_status')) {
                $table->enum('verification_status', ['pending', 'approved', 'rejected'])
                    ->default('pending')
                    ->after('status');
            }

            if (!Schema::hasColumn('properties', 'verification_note')) {
                $table->text('verification_note')->nullable()->after('verification_status');
            }

            if (!Schema::hasColumn('properties', 'verified_by')) {
                $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete()->after('verification_note');
            }

            if (!Schema::hasColumn('properties', 'verified_at')) {
                $table->timestamp('verified_at')->nullable()->after('verified_by');
            }
        });
    }

    public function down(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            if (Schema::hasColumn('properties', 'verified_by')) {
                $table->dropConstrainedForeignId('verified_by');
            }

            $columns = array_filter([
                Schema::hasColumn('properties', 'verification_status') ? 'verification_status' : null,
                Schema::hasColumn('properties', 'verification_note') ? 'verification_note' : null,
                Schema::hasColumn('properties', 'verified_at') ? 'verified_at' : null,
            ]);

            if ($columns) {
                $table->dropColumn($columns);
            }
        });
    }
};
