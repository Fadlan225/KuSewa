<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class MonthlyPaymentController extends Controller
{
    /**
     * Menampilkan Halaman Tagihan & Pembayaran Biaya Bulanan Owner
     */
    public function index()
    {
        $user = Auth::user();

        // [Dummy/Real Data]: Ambil tagihan aktif bulan ini untuk owner
        // Bisa disesuaikan dengan Query Database Anda nantinya (misal: MonthlyBill::where('user_id', $user->id)...)
        $billInfo = [
            'invoiceId' => 'INV/' . date('Ym') . '/KSW/' . str_pad($user->id ?? 1, 4, '0', STR_PAD_LEFT),
            'period' => 'Agustus ' . date('Y'),
            'dueDate' => '10 Agustus ' . date('Y'),
            'amount' => 250000,
            'serviceFee' => 5000,
            'status' => 'Belum Dibayar', // Pilihan status: 'Belum Dibayar', 'Menunggu Verifikasi', 'Lunas'
        ];

        return Inertia::render('Owner/MonthlyPayment', [
            'billInfo' => $billInfo
        ]);
    }

    /**
     * Memproses Bukti Pembayaran yang Diunggah oleh Owner
     */
    public function store(Request $request)
    {
        // 1. Validasi Input
        $request->validate([
            'payment_method' => 'required|string|in:qris,bca,mandiri,manual',
            'payment_proof' => 'required|image|mimes:jpeg,png,jpg,pdf|max:2048', // Maksimal 2MB
        ], [
            'payment_method.required' => 'Pilih metode pembayaran terlebih dahulu.',
            'payment_proof.required' => 'Harap unggah bukti transfer/pembayaran Anda.',
            'payment_proof.image' => 'File harus berupa gambar atau PDF.',
            'payment_proof.max' => 'Ukuran file maksimal adalah 2MB.',
        ]);

        // 2. Simpan File Bukti Pembayaran ke Storage
        $filePath = null;
        if ($request->hasFile('payment_proof')) {
            $filePath = $request->file('payment_proof')->store('payment-proofs', 'public');
        }

        // 3. Simpan Transaksi ke Database (Contoh Implementasi Logika DB)
        /*
        MonthlyPayment::create([
            'user_id'        => Auth::id(),
            'invoice_id'     => $request->invoice_id,
            'payment_method' => $request->payment_method,
            'proof_file'     => $filePath,
            'amount'         => 255000,
            'status'         => 'pending', // Menunggu verifikasi admin
        ]);
        */

        // 4. Redirect Kembali dengan Notifikasi Sukses
        return redirect()->back()->with('success', 'Bukti pembayaran berhasil diunggah! Tim kusewa.id akan memverifikasi transaksi Anda max 1x24 jam.');
    }
}