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
        Schema::create('assets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_profile_id')->constrained('owner_profiles')->onDelete('restrict');
            $table->string('title');
            $table->string('description');
            $table->json('detail');
            $table->string('province');
            $table->string('city');
            $table->string('address');
            $table->string('latitude');
            $table->string('longitude');
            $table->enum('status', ['active','inactive', 'pending'])->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assets');
    }
};
