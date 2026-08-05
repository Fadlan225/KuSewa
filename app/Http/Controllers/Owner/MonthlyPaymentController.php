<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use App\Models\owner_billing;

class MonthlyPaymentController extends Controller
{
    /**
     * Menampilkan Halaman Tagihan & Pembayaran Biaya Bulanan Owner
     */
    public function index()
    {
        $user = Auth::user();

        // Ambil semua tagihan owner, order by periode terbaru
        $billings = owner_billing::where('user_id', $user->id)
            ->orderBy('periode', 'desc')
            ->get();

        // Tagihan aktif (bulan ini yang belum paid)
        $activeBill = $billings->firstWhere('status', 'unpaid');

        // Jika ada tagihan aktif, gunakan itu; jika tidak, beri fallback
        if ($activeBill) {
            $billInfo = [
                'id' => $activeBill->id,
                'invoiceId' => $activeBill->billing_code,
                'period' => $this->formatPeriode($activeBill->periode),
                'dueDate' => $activeBill->due_date->format('d F Y'),
                'amount' => (int) $activeBill->total_amount,
                'serviceFee' => (int) $activeBill->service_fee_per_transaction,
                'totalTransactions' => $activeBill->total_transactions,
                'status' => 'Belum Dibayar',
            ];
        } else {
            // Fallback: hitung estimasi tagihan bulan ini
            $currentPeriode = date('Y-m');
            $serviceFee = \App\Models\service_fee::first();
            $feeValue = $serviceFee ? (int) $serviceFee->fee_value : 5000;

            $transactionCount = \App\Models\booking::whereHas('asset', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })
                ->where('booking_status', 'completed')
                ->whereRaw("DATE_FORMAT(created_at, '%Y-%m') = ?", [$currentPeriode])
                ->count();

            $billInfo = [
                'id' => null,
                'invoiceId' => 'INV/' . date('Ym') . '/KSW/' . str_pad($user->id ?? 1, 4, '0', STR_PAD_LEFT),
                'period' => $this->formatPeriode($currentPeriode),
                'dueDate' => date('d F Y', strtotime('+7 days')),
                'amount' => $transactionCount * $feeValue,
                'serviceFee' => $feeValue,
                'totalTransactions' => $transactionCount,
                'status' => 'Belum Ditagihkan',
            ];
        }

        // Riwayat tagihan sebelumnya
        $billingHistory = $billings->map(function ($bill) {
            return [
                'id' => $bill->id,
                'invoiceId' => $bill->billing_code,
                'period' => $this->formatPeriode($bill->periode),
                'amount' => (int) $bill->total_amount,
                'totalTransactions' => $bill->total_transactions,
                'status' => $bill->status === 'paid' ? 'Lunas' : ($bill->status === 'overdue' ? 'Terlambat' : 'Belum Dibayar'),
                'paidAt' => $bill->paid_at ? $bill->paid_at->format('d F Y') : null,
            ];
        });

        return Inertia::render('owner/MonthlyPayment', [
            'billInfo' => $billInfo,
            'billingHistory' => $billingHistory,
        ]);
    }

    /**
     * Memproses Bukti Pembayaran yang Diunggah oleh Owner
     */
    public function store(Request $request)
    {
        $request->validate([
            'billing_id' => 'required|exists:owner_billings,id',
            'payment_method' => 'required|string|in:qris,bca,mandiri,manual',
            'payment_proof' => 'required|file|mimes:jpeg,png,jpg,pdf|max:2048',
        ], [
            'payment_method.required' => 'Pilih metode pembayaran terlebih dahulu.',
            'payment_proof.required' => 'Harap unggah bukti transfer/pembayaran Anda.',
            'payment_proof.mimes' => 'File harus berupa gambar JPG, PNG, atau PDF.',
            'payment_proof.max' => 'Ukuran file maksimal adalah 2MB.',
        ]);

        // Simpan bukti pembayaran
        $filePath = $request->file('payment_proof')->store('payment-proofs', 'public');

        // Update status billing menjadi pending verification
        $billing = owner_billing::findOrFail($request->billing_id);
        $billing->update([
            'status' => 'paid',
            'paid_at' => now(),
        ]);

        // Simpan ke payment bukti (bisa juga dibuat tabel terpisah payment_proofs)
        // Untuk sekarang langsung update

        return redirect()->back()->with('success', 'Bukti pembayaran berhasil diunggah! Tim kusewa.id akan memverifikasi transaksi Anda max 1x24 jam.');
    }

    /**
     * Format periode dari Y-m menjadi "Bulan Tahun"
     */
    private function formatPeriode(string $periode): string
    {
        $months = [
            '01' => 'Januari',
            '02' => 'Februari',
            '03' => 'Maret',
            '04' => 'April',
            '05' => 'Mei',
            '06' => 'Juni',
            '07' => 'Juli',
            '08' => 'Agustus',
            '09' => 'September',
            '10' => 'Oktober',
            '11' => 'November',
            '12' => 'Desember',
        ];
        $parts = explode('-', $periode);
        $month = $months[$parts[1] ?? '01'] ?? $parts[1];
        $year = $parts[0] ?? date('Y');
        return "$month $year";
    }
}
