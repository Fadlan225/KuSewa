<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

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
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $payment = \App\Models\payment::with([
            'booking.asset',
            'booking.assetUnit',
            'booking.user'
        ])->findOrFail($id);

        // Ambil rekening bank milik owner aset tersebut
        $bankAccounts = \App\Models\bank_account::where('owner_profile_id', $payment->booking->asset->owner_profile_id)->get();

        // Jika owner belum memasukkan bank, tampilkan semua (opsional / fallback)
        if ($bankAccounts->isEmpty()) {
            $bankAccounts = \App\Models\bank_account::all();
        }

        return \Inertia\Inertia::render('Home/Payment', [
            'payment' => $payment,
            'bankAccounts' => $bankAccounts
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
