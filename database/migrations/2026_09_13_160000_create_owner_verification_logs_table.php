<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('owner_verification_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_profile_id')->constrained('owner_profiles')->onDelete('cascade');
            $table->foreignId('actor_id')->nullable()->constrained('users')->onDelete('set null');
            $table->enum('action', ['submitted', 'approved', 'rejected']);
            $table->text('reason')->nullable(); // hanya diisi saat action = rejected
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('owner_verification_logs');
    }
};
