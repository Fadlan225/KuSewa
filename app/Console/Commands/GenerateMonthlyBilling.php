<?php

namespace App\Console\Commands;

use App\Models\asset as Asset;
use App\Models\booking as Booking;
use App\Models\OwnerBilling;
use App\Models\service_fee as ServiceFee;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class GenerateMonthlyBilling extends Command
{
    /**
     * The name and signature of the console command.
     * Bisa dijalankan dengan opsi --year dan --month untuk generate periode tertentu.
     * Contoh: php artisan billing:generate --year=2026 --month=8
     */
    protected $signature = 'billing:generate
                            {--year= : Tahun periode (default: bulan lalu)}
                            {--month= : Bulan periode (default: bulan lalu)}
                            {--owner= : ID owner spesifik (opsional)}
                            {--force : Paksa generate ulang meski sudah ada}';

    /**
     * The console command description.
     */
    protected $description = 'Membuat tagihan biaya layanan bulanan untuk semua Owner aktif';

    public function handle(): int
    {
        $targetDate = Carbon::now()->subMonth();
        $year  = (int) ($this->option('year')  ?: $targetDate->year);
        $month = (int) ($this->option('month') ?: $targetDate->month);
        $ownerId = $this->option('owner');

        $this->info("📋 Generating billing untuk periode: " . $this->getPeriodLabel($month, $year));

        // Ambil tarif biaya layanan dari tabel service_fees (ambil yang aktif / terakhir)
        $serviceFeeRecord = ServiceFee::orderByDesc('id')->first();
        if (!$serviceFeeRecord) {
            $this->error('Tidak ada data service fee di database! Tambahkan dulu di tabel service_fees.');
            return Command::FAILURE;
        }
        $feeValue = (float) $serviceFeeRecord->fee_value;
        $feeType  = $serviceFeeRecord->fee_type; // 'fixed' atau 'percentage'

        $this->info("💰 Tarif biaya layanan: " . ($feeType === 'fixed' ? 'Rp ' . number_format($feeValue) : $feeValue . '%'));

        // Tentukan rentang tanggal periode
        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate   = $startDate->copy()->endOfMonth();

        // Ambil semua owner yang punya booking completed di periode ini
        $query = Booking::query()
            ->select(
                'assets.owner_profile_id',
                'owner_profiles.user_id as owner_user_id',
                DB::raw('COUNT(bookings.id) as total_transactions'),
                DB::raw('SUM(bookings.service_fee) as total_service_fee')
            )
            ->join('assets', 'bookings.asset_id', '=', 'assets.id')
            ->join('owner_profiles', 'assets.owner_profile_id', '=', 'owner_profiles.id')
            ->where('bookings.booking_status', 'completed')
            ->whereBetween('bookings.updated_at', [$startDate, $endDate])
            ->groupBy('assets.owner_profile_id', 'owner_profiles.user_id');

        if ($ownerId) {
            $query->where('owner_profiles.user_id', $ownerId);
        }

        $ownerSummaries = $query->get();

        if ($ownerSummaries->isEmpty()) {
            $this->warn('Tidak ada booking completed di periode ini.');
            return Command::SUCCESS;
        }

        $created = 0;
        $skipped = 0;

        foreach ($ownerSummaries as $summary) {
            $ownerUserId = $summary->owner_user_id;
            $totalTrx    = (int) $summary->total_transactions;

            // Hitung total tagihan berdasarkan fee_type
            if ($feeType === 'fixed') {
                $totalAmount = $feeValue * $totalTrx;
            } else {
                // percentage: gunakan akumulasi service_fee dari booking
                $totalAmount = (float) $summary->total_service_fee;
            }

            // Cek apakah sudah ada invoice untuk periode ini
            $exists = OwnerBilling::where('owner_id', $ownerUserId)
                ->where('period_year', $year)
                ->where('period_month', $month)
                ->exists();

            if ($exists && !$this->option('force')) {
                $this->line("  ⏭ Owner #$ownerUserId sudah punya invoice periode ini, skip.");
                $skipped++;
                continue;
            }

            // Generate nomor invoice unik
            $invoiceNumber = sprintf(
                'INV/%d%02d/KSW/%04d',
                $year,
                $month,
                OwnerBilling::count() + 1
            );

            // Buat atau update invoice
            OwnerBilling::updateOrCreate(
                [
                    'owner_id'     => $ownerUserId,
                    'period_year'  => $year,
                    'period_month' => $month,
                ],
                [
                    'invoice_number'      => $invoiceNumber,
                    'total_transactions'  => $totalTrx,
                    'fee_per_transaction' => $feeValue,
                    'total_amount'        => $totalAmount,
                    'status'              => 'unpaid',
                    'due_date'            => Carbon::create($year, $month, 10),
                ]
            );

            $this->line("  ✅ Owner #$ownerUserId → {$totalTrx} transaksi → Rp " . number_format($totalAmount));
            $created++;
        }

        $this->newLine();
        $this->info("Selesai! $created invoice dibuat, $skipped dilewati.");
        return Command::SUCCESS;
    }

    private function getPeriodLabel(int $month, int $year): string
    {
        $months = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret',
            4 => 'April',   5 => 'Mei',       6 => 'Juni',
            7 => 'Juli',    8 => 'Agustus',   9 => 'September',
            10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];
        return ($months[$month] ?? '?') . ' ' . $year;
    }
}
