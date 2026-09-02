<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('policy_templates', function (Blueprint $table) {
            $table->id();
            // null = template global (semua tipe aset)
            $table->foreignId('asset_type_id')->nullable()->constrained('asset_types')->onDelete('cascade');
            $table->string('group_key');         // 'persyaratan' | 'akses' | 'larangan' | 'tamu'
            $table->string('group_label');       // 'Persyaratan dan Dokumen'
            $table->string('key');               // 'allow_children'
            $table->string('label');             // 'Boleh bawa anak'
            $table->enum('input_type', ['toggle', 'checkbox', 'radio'])->default('toggle');
            $table->string('radio_group')->nullable(); // nama grup radio, mis: 'night_access'
            $table->string('radio_value')->nullable(); // nilai saat radio ini dipilih, mis: '24jam'
            $table->string('parent_key')->nullable();  // syarat muncul, mis: 'allow_children'
            $table->unsignedTinyInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('policy_templates');
    }
};
