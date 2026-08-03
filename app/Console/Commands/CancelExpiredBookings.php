<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\booking;

class CancelExpiredBookings extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'bookings:cancel-expired';

    /**
     * The console command description.
     */
    protected $description = 'Batalkan booking pending yang payment-nya sudah kedaluwarsa atau tidak punya payment';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        // 1. Batalkan booking pending yang payment-nya sudah expired
        $expiredCount = booking::where('booking_status', 'pending')
            ->whereHas('payment', function ($q) {
                $q->where('payment_status', 'pending')
                  ->where('expires_at', '<', now());
            })
            ->update(['booking_status' => 'cancelled']);

        // 2. Batalkan booking pending yang tidak punya payment sama sekali (orphan)
        $orphanCount = booking::where('booking_status', 'pending')
            ->whereDoesntHave('payment')
            ->update(['booking_status' => 'cancelled']);

        $total = $expiredCount + $orphanCount;

        $this->info("✓ Cancelled {$expiredCount} expired-payment bookings.");
        $this->info("✓ Cancelled {$orphanCount} orphan bookings (no payment).");
        $this->info("Total: {$total} bookings cancelled.");

        if ($total > 0) {
            \Log::info("bookings:cancel-expired ran. Cancelled {$total} bookings (expired:{$expiredCount}, orphan:{$orphanCount}).");
        }

        return Command::SUCCESS;
    }
}
