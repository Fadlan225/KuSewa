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

        // Tentukan unit_id dari pricing agar bookedDates spesifik per unit
        $selectedPricing = null;
        $unitId = null;
        if ($pricingId) {
            $selectedPricing = \App\Models\asset_pricing::find($pricingId);
            $unitId = $selectedPricing?->asset_unit_id;
        }

        $serviceFee = \Illuminate\Support\Facades\DB::table('service_fees')->where('fee_type', 'percentage')->value('fee_value') ?? 5;

        // Fetch bank accounts for the asset owner
        $bankAccounts = \App\Models\bank_account::where('owner_profile_id', $asset->owner_profile_id)->get();
        if ($bankAccounts->isEmpty()) {
            $bankAccounts = \App\Models\bank_account::all();
        }

        // Fetch booked dates — scoped ke unit jika ada, atau asset-level jika tidak ada unit
        $bookedDates = collect();
        if ($asset->units->isEmpty()) {
            $bookedDates = \App\Models\booking::where('asset_id', $assetId)
                ->when(
                    $unitId !== null,
                    fn ($q) => $q->where('asset_unit_id', $unitId),  // Unit-level: only this unit
                    fn ($q) => $q->whereNull('asset_unit_id')          // Asset-level: no unit bookings
                )
                ->where('end_date', '>=', now()->format('Y-m-d'))
                ->whereNotIn('booking_status', ['cancelled', 'rejected'])
                ->where(function ($q) {
                    $q->where('booking_status', '!=', 'pending')
                      ->orWhere(function ($q2) {
                          $q2->where('booking_status', 'pending')
                             ->whereHas('payment', function ($q3) {
                                 $q3->where('payment_status', 'pending')
                                    ->where('expires_at', '>', now());
                             });
                      });
                })
                ->select('start_date', 'end_date')
                ->get()
                ->map(function ($booking) {
                    return [
                        'from' => \Carbon\Carbon::parse($booking->start_date)->format('Y-m-d'),
                        'to' => \Carbon\Carbon::parse($booking->end_date)->format('Y-m-d'),
                    ];
                });
        }

        return Inertia::render('Home/Booking', [
            'asset' => $asset,
            'selectedPricing' => $selectedPricing,
            'serviceFee' => $serviceFee,
            'requestParams' => $request->all(),
            'bankAccounts' => $bankAccounts,
            'bookedDates' => $bookedDates
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
            'payment_method' => 'required',
            'booker_name' => 'required|string|max:255',
            'booker_phone' => 'required|string|max:20',
            'booker_email' => 'required|email|max:255',
            'guest_name' => 'required|string|max:255',
        ]);

        try {
            $payment = \Illuminate\Support\Facades\DB::transaction(function () use ($validated) {

                $asset = \App\Models\asset::with('type')->findOrFail($validated['asset_id']);
                $pricing = \App\Models\asset_pricing::findOrFail($validated['pricing_id']);

                // Tentukan scope overlap: per unit (Studio/Hotel) atau per asset (Villa/Rumah)
                $unitId = $pricing->asset_unit_id;
                
                $parsedStartDate = \Carbon\Carbon::parse($validated['startDate']);
                $parsedEndDate = \Carbon\Carbon::parse($validated['endDate']);
                
                // Jika mode 'day' atau 'month', booking mencakup keseluruhan hari (hingga 23:59:59)
                // Ini penting agar overlap terdeteksi saat orang lain memesan di hari terakhir (checkout day).
                // Untuk 'night' tidak perlu karena check-in bisa dilakukan di hari checkout orang lain.
                // Untuk 'hour' waktu spesifik sudah ada.
                if (in_array($validated['rental_mode'] ?? 'day', ['day', 'month'])) {
                    $parsedEndDate->endOfDay();
                }

                $overlappingBookingsCount = \App\Models\booking::where('asset_id', $validated['asset_id'])
                    ->when(
                        $unitId !== null,
                        fn ($q) => $q->where('asset_unit_id', $unitId),
                        fn ($q) => $q->whereNull('asset_unit_id')
                    )
                    ->whereNotIn('booking_status', ['cancelled', 'rejected'])
                    ->where(function ($q) {
                        $q->where('booking_status', '!=', 'pending')
                          ->orWhere(function ($q2) {
                              $q2->where('booking_status', 'pending')
                                 ->whereHas('payment', function ($q3) {
                                     $q3->where('payment_status', 'pending')
                                        ->where('expires_at', '>', now());
                                 });
                          });
                    })
                    // Overlap: start baru < end lama DAN end baru > start lama
                    ->where('start_date', '<', $parsedEndDate)
                    ->where('end_date', '>', $parsedStartDate)
                    ->lockForUpdate()
                    ->count();

                $maxQuantity = 1;
                if ($unitId) {
                    $unit = \App\Models\asset_units::find($unitId);
                    if ($unit) {
                        $maxQuantity = $unit->quantity;
                    }
                }

                if ($overlappingBookingsCount >= $maxQuantity) {
                    throw new \Exception('OVERLAP');
                }

                $priceMultiplier = ($asset->type->rental_unit === 'night' && ($validated['rental_mode'] ?? '') === 'month') ? 30 : 1;
                $subtotal = ($pricing->price * $priceMultiplier) * $validated['duration'];

                $serviceFeePercent = \Illuminate\Support\Facades\DB::table('service_fees')->where('fee_type', 'percentage')->value('fee_value') ?? 5;
                $serviceFee = $subtotal * ($serviceFeePercent / 100);
                $total = $subtotal + $serviceFee;

                $unitName = null;
                if ($pricing->asset_unit_id) {
                    $unit = \App\Models\asset_units::find($pricing->asset_unit_id);
                    if ($unit) $unitName = $unit->name;
                }

                $booking = \App\Models\booking::create([
                    'asset_id' => $validated['asset_id'],
                    'asset_unit_id' => $pricing->asset_unit_id,
                    'asset_name' => $asset->title,
                    'asset_unit_name' => $unitName,
                    'booking_code' => 'BK-' . strtoupper(uniqid()),
                    'booker_name' => $validated['booker_name'],
                    'booker_phone' => $validated['booker_phone'],
                    'booker_email' => $validated['booker_email'],
                    'guest_name' => $validated['guest_name'],
                    'user_id' => auth()->id(),
                    'start_date' => $parsedStartDate,
                    'end_date' => $parsedEndDate,
                    'subtotal' => $subtotal,
                    'service_fee' => $serviceFee,
                    'total' => $total,
                    'booking_status' => 'pending'
                ]);

                $payment = \App\Models\payment::create([
                    'booking_id' => $booking->id,
                    'payment_method' => $validated['payment_method'],
                    'payment_status' => 'pending',
                    'expires_at' => now()->addHours(24),
                ]);

                return $payment;
            });

            // Redirect ke halaman pembayaran setelah transaksi berhasil
            return redirect()->route('payment.show', $payment->id)
                ->with('success', 'Booking berhasil dibuat! Selesaikan pembayaran Anda.');

        } catch (\Exception $e) {
            $message = $e->getMessage() === 'OVERLAP'
                ? 'Maaf, aset sudah dipesan oleh pengguna lain pada tanggal tersebut. Silakan pilih tanggal lain.'
                : 'Terjadi kesalahan saat memproses pemesanan. Silakan coba lagi.';

            \Log::warning('Booking store error: ' . $e->getMessage(), [
                'asset_id' => $validated['asset_id'] ?? null,
                'user_id' => auth()->id(),
                'startDate' => $validated['startDate'] ?? null,
                'endDate' => $validated['endDate'] ?? null,
            ]);

            return back()->withErrors(['startDate' => $message])->withInput();
        }
    }


    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $booking = \App\Models\booking::with([
            'asset.firstImage',
            'asset.type.category',
            'asset.ownerProfile.user',
            'assetUnit',
            'payment',
            'user'
        ])->findOrFail($id);

        if ($booking->user_id !== auth()->id()) {
            abort(403, 'Unauthorized action.');
        }

        return Inertia::render('Home/BookingPass', [
            'booking' => $booking
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
