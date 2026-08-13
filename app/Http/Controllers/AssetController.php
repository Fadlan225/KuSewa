<?php

namespace App\Http\Controllers;
use App\Models\asset;
use App\Models\AssetView;

use Illuminate\Http\Request;
use App\Models\booking;

use Illuminate\Support\Facades\DB;

class AssetController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {

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
    public function show(asset $asset)
    {
        $asset->load([
            'type:id,name,allow_units,rental_unit,category_id',
            'type.category:id,name,icon',
            'images',
            'thumbnailImages',
            'pricings',
            'city:code,name',
            'province:code,name',
            'district:code,name',
            'village:code,name',
            'ownerProfile.user',
            'reviews.user',
            'reviews.reviewTagItems.reviewTag',
            'favorites' => function ($query) {
                $query->where('user_id', auth()->id());
            },
            // Fasilitas sistem baru (via pivot asset_facilities)
            'facilities:id,name,facility_category_id',
            'facilities.category:id,name,icon',
            'units.pricings',
            'units.images.gallery_category',
            // Fasilitas tambahan per unit
            'units.facilities:id,name,facility_category_id',
            'units.facilities.category:id,name,icon',
            // FAQ & Kebijakan
            'faqs',
            'policies',
            // Bookings aktif per unit untuk cek sisa ketersediaan
            'units.bookings' => function ($query) {
                $query->where('end_date', '>=', now()->format('Y-m-d'))
                      ->whereNotIn('booking_status', ['cancelled', 'rejected'])
                      ->where(function ($q) {
                          $q->where('booking_status', '!=', 'pending')
                            ->orWhere(function ($q2) {
                                $q2->where('booking_status', 'pending')
                                   ->whereHas('payment', function ($q3) {
                                       $q3->whereNotIn('payment_status', ['expired', 'failed', 'cancelled'])
                                          ->where(function ($q4) {
                                              $q4->where('payment_status', '!=', 'pending')
                                                 ->orWhere('expires_at', '>', now());
                                          });
                                   });
                            });
                      })
                      ->select('id', 'asset_unit_id', 'start_date', 'end_date', 'booking_status');
            },
        ])
        ->loadAvg('reviews', 'rating')
        ->loadCount([
            'reviews',
            'favorites'
        ]);

        $favorite = $asset->favorites->first();
        $asset->isFavorite = (bool) $favorite;
        $asset->favorite_id = $favorite?->id;
        unset($asset->favorites);

        // ==========================================
        // Tracking view — hanya untuk user login
        // ==========================================
        $assetView = null;
        if (auth()->check()) {
            $assetView = AssetView::where('user_id', auth()->id())
                ->where('asset_id', $asset->id)
                ->first();

            if ($assetView) {
                // Sudah pernah lihat — increment view_count
                $assetView->increment('view_count');
                $assetView->update(['last_viewed' => now()]);
            } else {
                // Pertama kali lihat — buat record baru
                $assetView = AssetView::create([
                    'user_id'     => auth()->id(),
                    'asset_id'    => $asset->id,
                    'view_count'  => 1,
                    'last_viewed' => now(),
                ]);
            }

            // Batasi jumlah riwayat dilihat maksimum 100 aset per user
            $viewCount = AssetView::where('user_id', auth()->id())->count();
            if ($viewCount > 100) {
                $oldestViews = AssetView::where('user_id', auth()->id())
                    ->orderBy('last_viewed', 'asc')
                    ->limit($viewCount - 100)
                    ->get();
                foreach ($oldestViews as $oldView) {
                    $oldView->delete();
                }
            }
        }

        $serviceFeeRecord = DB::table('service_fees')->first();
        $serviceFee = $serviceFeeRecord ? [
            'type'  => $serviceFeeRecord->fee_type,
            'value' => (float) $serviceFeeRecord->fee_value
        ] : [
            'type'  => 'percentage',
            'value' => 5
        ];

        // Fetch booked dates for the calendar on Detail page.
        // - Asset TANPA unit (Villa, Rumah): tampilkan tanggal yang sudah diblokir di level asset.
        // - Asset DENGAN unit (Hotel, Studio): kembalikan kosong, karena user belum pilih unit.
        //   Pengecekan aktual dilakukan di backend saat booking.store.
        $bookedDates = collect();
        if ($asset->units->isEmpty()) {
            $bookedDates = booking::where('asset_id', $asset->id)
                ->whereNull('asset_unit_id')
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

        // Fetch nearby places
        $nearbyPlaces = \App\Services\OpenStreetMapService::getNearbyPlaces($asset->latitude, $asset->longitude, $asset->id);

        return inertia('Home/Assets/Show', [
            'asset'       => $asset,
            'serviceFee'  => $serviceFee,
            'bookedDates' => $bookedDates,
            'assetView'   => $assetView,
            'nearbyPlaces'=> $nearbyPlaces,
            'allCategories' => \App\Models\asset_category::select(['id', 'name', 'icon'])
                                ->with(['types:id,category_id,name,allow_units,rental_unit'])
                                ->get(),
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

