<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\owner_billing;
use App\Models\booking;
use App\Models\service_fee;
use App\Models\User;
use Carbon\Carbon;

class GenerateOwnerBilling extends Command
{
  protected $signature = 'billing:generate {--periode= : Periode Y-m (default: bulan ini)} {--dry-run : Simulasi tanpa menyimpan}';
  protected $description = 'Generate tagihan bulanan untuk semua owner berdasarkan jumlah transaksi confirmed/completed';

  public function handle(): int
  {
    $periode = $this->option('periode') ?: Carbon::now()->format('Y-m');
    $dryRun = $this->option('dry-run');

    $this->info("Generate tagihan bulanan untuk periode: $periode");
    if ($dryRun) {
      $this->warn('Mode DRY-RUN: tidak ada data yang disimpan.');
    }

    // Ambil biaya layanan per transaksi
    $serviceFee = service_fee::first();
    $feePerTransaction = $serviceFee ? (float) $serviceFee->fee_value : 5000;
    $this->info("Biaya layanan per transaksi: Rp " . number_format($feePerTransaction, 0, ',', '.'));

    // Ambil semua user dengan role owner
    $owners = User::where('role', 'owner')->get();

    if ($owners->isEmpty()) {
      $this->warn('Tidak ada owner ditemukan.');
      return self::SUCCESS;
    }

    $bar = $this->output->createProgressBar($owners->count());
    $bar->start();

    $generatedCount = 0;
    $skippedCount = 0;

    foreach ($owners as $owner) {
      // Cek apakah sudah ada tagihan untuk periode ini
      $existingBilling = owner_billing::where('user_id', $owner->id)
        ->where('periode', $periode)
        ->exists();

      if ($existingBilling) {
        $skippedCount++;
        $bar->advance();
        continue;
      }

      // Hitung jumlah transaksi confirmed + completed bulan ini
      $transactionCount = booking::whereHas('asset', function ($q) use ($owner) {
        $q->where('user_id', $owner->id);
      })
        ->where('booking_status', 'completed')
        ->whereRaw("DATE_FORMAT(created_at, '%Y-%m') = ?", [$periode])
        ->count();

      // Hanya buat tagihan jika ada transaksi
      if ($transactionCount > 0) {
        $totalAmount = $transactionCount * $feePerTransaction;

        if (!$dryRun) {
          // Generate billing code: INV-YYYYMM-XXXX
          $lastBilling = owner_billing::where('periode', $periode)
            ->orderBy('id', 'desc')
            ->first();
          $sequence = $lastBilling ? ((int) substr($lastBilling->billing_code, -4)) + 1 : 1;

          $billingCode = 'INV-' . str_replace('-', '', $periode) . '-' . str_pad($sequence, 4, '0', STR_PAD_LEFT);

          // Due date: 7 hari dari sekarang
          $dueDate = Carbon::now()->addDays(7)->toDateString();

          owner_billing::create([
            'user_id' => $owner->id,
            'billing_code' => $billingCode,
            'periode' => $periode,
            'service_fee_per_transaction' => $feePerTransaction,
            'total_transactions' => $transactionCount,
            'total_amount' => $totalAmount,
            'status' => 'unpaid',
            'due_date' => $dueDate,
          ]);
        }

        $generatedCount++;
      }

      $bar->advance();
    }

    $bar->finish();
    $this->newLine(2);

    $this->info("✅ Tagihan berhasil digenerate: $generatedCount");
    $this->info("⏭️ Tagihan yang sudah ada (dilewati): $skippedCount");

    if ($dryRun) {
      $this->warn('Mode dry-run: tidak ada data yang disimpan ke database.');
    }

    return self::SUCCESS;
  }
}