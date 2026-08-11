<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\OwnerBilling;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class MonthlyPaymentController extends Controller
{
    /**
     * Menampilkan halaman tagihan bulanan owner.
     * Menampilkan tagihan aktif (unpaid/waiting/overdue) + riwayat (paid/rejected).
     */
    public function index()
    {
        $ownerId = Auth::id();

        // Tandai tagihan yang sudah melewati jatuh tempo sebagai 'overdue'
        OwnerBilling::where('owner_id', $ownerId)
            ->where('status', 'unpaid')
            ->where('due_date', '<', now()->toDateString())
            ->update(['status' => 'overdue']);

        // Ambil tagihan aktif terbaru (yang perlu dibayar, termasuk bulan berjalan)
        // Urutkan: overdue dulu, lalu unpaid terbaru, lalu waiting
        $activeBilling = OwnerBilling::where('owner_id', $ownerId)
            ->active()
            ->orderByRaw("FIELD(status, 'overdue', 'unpaid', 'waiting_verification')")
            ->orderByDesc('period_year')
            ->orderByDesc('period_month')
            ->first();

        // Ambil riwayat tagihan yang sudah selesai
        $billingHistory = OwnerBilling::where('owner_id', $ownerId)
            ->history()
            ->orderByDesc('period_year')
            ->orderByDesc('period_month')
            ->get()
            ->map(fn ($bill) => [
                'id'                => $bill->id,
                'invoiceId'         => $bill->invoice_number,
                'period'            => $bill->period_label,
                'totalTransactions' => $bill->total_transactions,
                'amount'            => (float) $bill->total_amount,
                'status'            => $bill->status_label,
                'paidAt'            => $bill->paid_at?->format('d M Y'),
            ]);

        // Format data tagihan aktif untuk frontend
        $billInfo = null;
        if ($activeBilling) {
            $now = Carbon::now();
            // Solusi 1: Tagihan bulan berjalan hanya tampil sebagai estimasi real-time,
            // tidak bisa dibayar sampai memasuki bulan berikutnya.
            $isCurrentMonth = ($activeBilling->period_year === $now->year
                && $activeBilling->period_month === $now->month);

            $billInfo = [
                'id'                => $activeBilling->id,
                'invoiceId'         => $activeBilling->invoice_number,
                'period'            => $activeBilling->period_label,
                'dueDate'           => $activeBilling->due_date_label,
                'amount'            => (float) $activeBilling->total_amount,
                'serviceFee'        => (float) $activeBilling->fee_per_transaction,
                'totalTransactions' => $activeBilling->total_transactions,
                'status'            => $activeBilling->status_label,
                'paymentMethod'     => $activeBilling->payment_method,
                'paymentProof'      => $activeBilling->payment_proof
                    ? Storage::url($activeBilling->payment_proof)
                    : null,
                // Flag: apakah owner sudah boleh membayar tagihan ini?
                // false = masih bulan berjalan (akumulasi belum selesai)
                'canPay'            => !$isCurrentMonth,
                'isCurrentMonth'    => $isCurrentMonth,
            ];
        }

        return Inertia::render('owner/MonthlyPayment', [
            'billInfo'       => $billInfo,
            'billingHistory' => $billingHistory,
        ]);
    }

    /**
     * Owner mengirim bukti pembayaran.
     */
    public function store(Request $request)
    {
        $request->validate([
            'billing_id'     => ['required', 'exists:owner_billings,id'],
            'payment_method' => ['required', 'string'],
            'payment_proof'  => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:2048'],
        ], [
            'billing_id.required'     => 'Tagihan tidak valid.',
            'billing_id.exists'       => 'Tagihan tidak ditemukan.',
            'payment_method.required' => 'Pilih metode pembayaran.',
            'payment_proof.required'  => 'Bukti pembayaran wajib diupload.',
            'payment_proof.mimes'     => 'Format file harus JPG, PNG, atau PDF.',
            'payment_proof.max'       => 'Ukuran file maksimal 2MB.',
        ]);

        $billing = OwnerBilling::where('id', $request->billing_id)
            ->where('owner_id', Auth::id())
            ->whereIn('status', ['unpaid', 'rejected', 'overdue'])
            ->firstOrFail();

        // Solusi 1: Tolak pembayaran jika tagihan masih untuk bulan berjalan
        $now = Carbon::now();
        if ($billing->period_year === $now->year && $billing->period_month === $now->month) {
            return back()->withErrors([
                'billing_id' => 'Tagihan bulan ' . $billing->period_label . ' belum dapat dibayar. Pembayaran akan dibuka pada tanggal 1 bulan berikutnya.'
            ]);
        }

        // Hapus bukti lama jika ada
        if ($billing->payment_proof) {
            Storage::disk('public')->delete($billing->payment_proof);
        }

        // Simpan file bukti bayar
        $proofPath = $request->file('payment_proof')
            ->store('billing-proofs/' . Auth::id(), 'public');

        $billing->update([
            'payment_method' => $request->payment_method,
            'payment_proof'  => $proofPath,
            'status'         => 'waiting_verification',
        ]);

        return redirect()->route('owner.monthly-payment')
            ->with('success', 'Bukti pembayaran berhasil dikirim. Kami akan memverifikasi dalam 1×24 jam kerja.');
    }
}
