<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::create('owner_billings', function (Blueprint $table) {
      $table->id();
      $table->foreignId('user_id')->constrained()->onDelete('restrict');
      $table->string('billing_code')->unique();           // e.g. INV-2026-08-0001
      $table->string('periode');                            // e.g. 2026-08
      $table->decimal('service_fee_per_transaction', 15, 2); // biaya layanan per transaksi saat itu
      $table->integer('total_transactions');                // jumlah transaksi confirmed+completed
      $table->decimal('total_amount', 15, 2);               // service_fee × total_transactions
      $table->enum('status', ['unpaid', 'paid', 'overdue'])->default('unpaid');
      $table->date('due_date');                             // jatuh tempo
      $table->dateTime('paid_at')->nullable();
      $table->timestamps();
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('owner_billings');
  }
};