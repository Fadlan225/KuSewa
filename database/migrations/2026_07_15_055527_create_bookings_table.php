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
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_id')->constrained()->onDelete('restrict')->nullable();
            $table->foreignId('asset_unit_id')->constrained()->onDelete('restrict')->nullable();
            $table->string('booking_code');
            $table->foreignId('user_id')->constrained()->onDelete('restrict');
            $table->date('start_date');
            $table->date('end_date');
            $table->decimal('subtotal',15,2);
            $table->decimal('service_fee',15,2);
            $table->decimal('total',15,2);
            $table->enum('booking_status', ['pending','accepted', 'rejected'])->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
