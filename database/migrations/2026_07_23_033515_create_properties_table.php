<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('properties', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // ID Owner/Pemilik
            $table->string('title');
            $table->string('category'); // Kos, Apartemen, Rumah Kontrakan, Kendaraan, dll.
            $table->string('type')->nullable(); // Putra, Putri, Pasutri, Mobil, dll.
            $table->decimal('price', 15, 2);
            $table->string('rent_period')->default('Bulan'); // Hari, Bulan, Tahun
            $table->string('city');
            $table->text('address');
            $table->enum('status', ['Tersedia', 'Tersewa', 'Maintenance'])->default('Tersedia');
            $table->string('tenant')->nullable(); // Nama penyewa aktif (optional)
            $table->string('image')->nullable();
            $table->string('occupancy')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('properties');
    }
};