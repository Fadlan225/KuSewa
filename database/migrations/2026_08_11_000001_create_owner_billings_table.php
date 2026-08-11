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
        Schema::create('owner_billings', function (Blueprint $table) {
            $table->id();

            // Pemilik yang ditagih (berdasarkan user_id owner)
            $table->foreignId('owner_id')->constrained('users')->onDelete('restrict');

            // Nomor invoice unik, e.g. INV/202608/KSW/0091
            $table->string('invoice_number')->unique();

            // Periode tagihan
            $table->smallInteger('period_year');  // e.g. 2026
            $table->tinyInteger('period_month');  // e.g. 8 (Agustus)

            // Rincian tagihan
            $table->integer('total_transactions')->default(0);  // jumlah booking completed
            $table->decimal('fee_per_transaction', 15, 2);       // tarif saat invoice dibuat (dikunci)
            $table->decimal('total_amount', 15, 2);              // = fee_per_transaction × total_transactions

            // Status & tenggat
            $table->enum('status', [
                'unpaid',               // belum dibayar
                'waiting_verification', // sudah upload bukti, menunggu admin
                'paid',                 // sudah dikonfirmasi lunas
                'rejected',             // bukti ditolak admin
                'overdue',              // melewati jatuh tempo
            ])->default('unpaid');
            $table->date('due_date');   // tanggal jatuh tempo

            // Detail pembayaran dari owner
            $table->string('payment_method')->nullable();  // qris, bca, mandiri, manual
            $table->string('payment_proof')->nullable();   // path file bukti bayar

            // Konfirmasi
            $table->timestamp('paid_at')->nullable();      // waktu dikonfirmasi lunas
            $table->text('note')->nullable();              // catatan admin saat reject/approve

            $table->timestamps();

            // Index untuk pencarian cepat
            $table->unique(['owner_id', 'period_year', 'period_month'], 'unique_owner_period');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('owner_billings');
    }
};
