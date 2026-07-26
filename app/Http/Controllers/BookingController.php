<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class BookingController extends Controller
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
    public function create(Request $request)
    {
        $assetId = $request->query('asset');
        $pricingId = $request->query('pricing_id');
        
        $asset = \App\Models\asset::with([
            'firstImage',
            'type.category',
            'defaultPricing'
        ])->findOrFail($assetId);

        $selectedPricing = null;
        if ($pricingId) {
            $selectedPricing = \App\Models\asset_pricing::find($pricingId);
        }

        $serviceFee = \Illuminate\Support\Facades\DB::table('service_fees')->where('fee_type', 'percentage')->value('fee_value') ?? 5;

        return Inertia::render('Home/Booking', [
            'asset' => $asset,
            'selectedPricing' => $selectedPricing,
            'serviceFee' => $serviceFee,
            'requestParams' => $request->all()
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'asset_id' => 'required|exists:assets,id',
            'pricing_id' => 'required|exists:asset_pricings,id',
            'startDate' => 'required|date',
            'endDate' => 'required|date|after_or_equal:startDate',
            'duration' => 'required|integer|min:1',
            'rental_mode' => 'nullable|string',
        ]);

        $asset = \App\Models\asset::with('type')->findOrFail($validated['asset_id']);
        $pricing = \App\Models\asset_pricing::findOrFail($validated['pricing_id']);

        $priceMultiplier = ($asset->type->rental_unit === 'night' && ($validated['rental_mode'] ?? '') === 'month') ? 30 : 1;
        $subtotal = ($pricing->price * $priceMultiplier) * $validated['duration'];
        
        $serviceFeePercent = \Illuminate\Support\Facades\DB::table('service_fees')->where('fee_type', 'percentage')->value('fee_value') ?? 5;
        $serviceFee = $subtotal * ($serviceFeePercent / 100);
        $total = $subtotal + $serviceFee;

        $booking = \App\Models\booking::create([
            'asset_id' => $validated['asset_id'],
            'asset_unit_id' => $pricing->asset_unit_id,
            'booking_code' => 'BK-' . strtoupper(uniqid()),
            'user_id' => auth()->id(),
            'start_date' => $validated['startDate'],
            'end_date' => $validated['endDate'],
            'subtotal' => $subtotal,
            'service_fee' => $serviceFee,
            'total' => $total,
            'booking_status' => 'pending'
        ]);

        // Karena belum ada halaman payment, sementara redirect ke beranda dengan pesan sukses
        return redirect()->route('Home')->with('success', 'Booking berhasil dibuat! Menunggu pembayaran.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
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
