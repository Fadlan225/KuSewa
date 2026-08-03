<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\payment;
use App\Models\bank_account;
use Inertia\Inertia;

class PaymentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store payment confirmation (upload proof, set date).
     */
    public function store(Request $request)
    {
        $request->validate([
            'payment_id'       => 'required|exists:payments,id',
            'proof_of_payment' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        $payment = payment::with('booking')->findOrFail($request->payment_id);

        // Pastikan milik user yang login
        if ($payment->booking->user_id !== auth()->id()) {
            abort(403);
        }

        // Simpan file bukti
        $path = $request->file('proof_of_payment')->store('proofs', 'public');

        // Simpan nama bank sebagai payment_method (bukan ID)
        $bankName = null;
        if ($payment->payment_method) {
            $bank = bank_account::find($payment->payment_method);
            $bankName = $bank ? $bank->bank_name : $payment->payment_method;
        }

        $payment->update([
            'proof_of_payment' => $path,
            'payment_date'     => now()->toDateString(),
            'payment_status'   => 'verifying',  // Owner perlu konfirmasi dulu
            'payment_method'   => $bankName ?? $payment->payment_method,
        ]);

        // Booking status tetap 'pending' sampai owner konfirmasi

        return redirect()->route('booking.show', $payment->booking_id)
            ->with('success', 'Bukti pembayaran berhasil dikirim! Kami akan memverifikasi segera.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $payment = payment::with([
            'booking.asset',
            'booking.assetUnit',
            'booking.user'
        ])->findOrFail($id);

        // Cari bank berdasarkan payment_method (yang mana ID dari bank_account)
        $selectedBank = bank_account::find($payment->payment_method);

        return Inertia::render('Home/Payment', [
            'payment' => $payment,
            'selectedBank' => $selectedBank
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}

