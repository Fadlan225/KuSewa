<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\asset;
use Illuminate\Http\Request;

class AssetController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $ownerProfile = auth()->user()->ownerProfile;

        if (!$ownerProfile) {
            return redirect()->route('Home')->with('error', 'Silakan lengkapi profil owner Anda terlebih dahulu.');
        }

        // Hitung statistik (Total aset/unit, tersedia, tersewa, pending verifikasi)
        $statsQuery = asset::where('owner_profile_id', $ownerProfile->id)
            ->withCount([
                'units as total_units_count',
                'units as occupied_units_count' => function ($q) {
                    $q->whereHas('bookings', function ($q2) {
                        $q2->where('end_date', '>=', now()->format('Y-m-d'))
                           ->whereNotIn('booking_status', ['cancelled', 'rejected']);
                    });
                },
                'bookings as active_bookings_count' => function ($q) {
                    $q->where('end_date', '>=', now()->format('Y-m-d'))
                      ->whereNotIn('booking_status', ['cancelled', 'rejected'])
                      ->whereNull('asset_unit_id');
                }
            ])
            ->get();

        $totalAsset = 0;
        $totalAvailable = 0;
        $totalOccupied = 0;
        $totalPendingVerification = 0;

        foreach ($statsQuery as $assetStat) {
            if ($assetStat->status === 'inactive') {
                $totalPendingVerification++;
            }
            
            $tUnits = $assetStat->total_units_count > 0 ? $assetStat->total_units_count : 1;
            $oUnits = $assetStat->total_units_count > 0 ? $assetStat->occupied_units_count : $assetStat->active_bookings_count;
            
            $totalAsset += $tUnits;
            $totalOccupied += $oUnits;
            $totalAvailable += ($tUnits - $oUnits);
        }

        $stats = [
            'totalAsset' => $totalAsset,
            'totalAvailable' => $totalAvailable,
            'totalOccupied' => $totalOccupied,
            'totalPendingVerification' => $totalPendingVerification,
        ];

        // Query Utama untuk Tabel
        $query = asset::with([
                'type:id,name,category_id,allow_units,rental_unit',
                'type.category:id,name',
                'city:code,name',
                'images',
                'thumbnailImages',
                'pricings',
                'units.bookings' => function($q) {
                    $q->where('end_date', '>=', now()->format('Y-m-d'))
                      ->whereNotIn('booking_status', ['cancelled', 'rejected']);
                },
                'bookings' => function($q) {
                    $q->where('end_date', '>=', now()->format('Y-m-d'))
                      ->whereNotIn('booking_status', ['cancelled', 'rejected'])
                      ->whereNull('asset_unit_id');
                }
            ])
            ->where('owner_profile_id', $ownerProfile->id);

        // Filter: Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhereHas('city', function ($q2) use ($search) {
                      $q2->where('name', 'like', "%{$search}%");
                  })
                  ->orWhere('address', 'like', "%{$search}%");
            });
        }

        // Filter: Category
        if ($request->filled('category') && $request->category !== 'Semua') {
            $category = $request->category;
            $query->whereHas('type.category', function ($q) use ($category) {
                $q->where('name', $category);
            });
        }

        // Filter: Jenis Properti
        if ($request->filled('jenis') && $request->jenis !== 'Semua') {
            $jenis = $request->jenis;
            $query->whereHas('type', function ($q) use ($jenis) {
                $q->where('name', $jenis);
            });
        }

        // Filter: Status Verifikasi & Ketersediaan
        if ($request->filled('status') && $request->status !== 'Semua') {
            $status = $request->status;
            if ($status === 'pending') {
                $query->where('status', 'pending');
            } elseif ($status === 'approved') {
                $query->where('status', 'approved');
            } elseif ($status === 'rejected') {
                $query->where('status', 'rejected'); 
            } elseif ($status === 'inactive') {
                $query->where('status', 'inactive'); 
            } elseif ($status === 'Tersedia') {
                $query->where(function($q) {
                    $q->where(function($q2) {
                        $q2->whereDoesntHave('units')
                           ->whereDoesntHave('bookings', function($q3) {
                               $q3->where('end_date', '>=', now()->format('Y-m-d'))
                                  ->whereNotIn('booking_status', ['cancelled', 'rejected'])
                                  ->whereNull('asset_unit_id');
                           });
                    })->orWhere(function($q2) {
                        $q2->whereHas('units')
                           ->whereRaw('(SELECT count(*) FROM asset_units WHERE asset_units.asset_id = assets.id) > (SELECT count(*) FROM asset_units WHERE asset_units.asset_id = assets.id AND EXISTS (SELECT * FROM bookings WHERE bookings.asset_unit_id = asset_units.id AND bookings.end_date >= ? AND bookings.booking_status NOT IN (?, ?)))', [now()->format('Y-m-d'), 'cancelled', 'rejected']);
                    });
                });
            } elseif ($status === 'Tersewa') {
                $query->where(function($q) {
                    $q->where(function($q2) {
                        $q2->whereDoesntHave('units')
                           ->whereHas('bookings', function($q3) {
                               $q3->where('end_date', '>=', now()->format('Y-m-d'))
                                  ->whereNotIn('booking_status', ['cancelled', 'rejected'])
                                  ->whereNull('asset_unit_id');
                           });
                    })->orWhere(function($q2) {
                        $q2->whereHas('units')
                           ->whereRaw('(SELECT count(*) FROM asset_units WHERE asset_units.asset_id = assets.id) <= (SELECT count(*) FROM asset_units WHERE asset_units.asset_id = assets.id AND EXISTS (SELECT * FROM bookings WHERE bookings.asset_unit_id = asset_units.id AND bookings.end_date >= ? AND bookings.booking_status NOT IN (?, ?)))', [now()->format('Y-m-d'), 'cancelled', 'rejected']);
                    });
                });
            }
        }

        $paginator = $query->paginate(10)->withQueryString();

        $properties = $paginator->through(function ($asset) {
            $hasUnits = $asset->type->allow_units ? true : false;
            $totalUnits = 1;
            $occupiedUnits = 0;
            
            if ($hasUnits) {
                $totalUnits = $asset->units->count();
                foreach ($asset->units as $unit) {
                    if ($unit->bookings->isNotEmpty()) {
                        $occupiedUnits++;
                    }
                }
            } else {
                if ($asset->bookings->isNotEmpty()) {
                    $occupiedUnits = 1;
                }
            }
            
            $availableUnits = $totalUnits - $occupiedUnits;
            $status = $availableUnits > 0 ? 'Tersedia' : 'Tersewa';

            $vStatus = $asset->status;

            $price = 0;
            if ($asset->pricings->isNotEmpty()) {
                $price = $asset->pricings->first()->price;
            }

            $thumbnail = null;
            if ($asset->thumbnailImages->isNotEmpty()) {
                $thumbnail = $asset->thumbnailImages->first()->image ?? $asset->thumbnailImages->first()->path ?? $asset->thumbnailImages->first()->url ?? null;
            }

            if ($thumbnail && !str_starts_with($thumbnail, 'http')) {
                $thumbnail = asset('storage/' . $thumbnail);
            }

            $tenant = null;
            if ($hasUnits) {
                foreach ($asset->units as $unit) {
                    if ($unit->bookings->isNotEmpty()) {
                        $tenant = $unit->bookings->first()->booker_name ?? $unit->bookings->first()->guest_name;
                        break;
                    }
                }
            } else {
                if ($asset->bookings->isNotEmpty()) {
                    $tenant = $asset->bookings->first()->booker_name ?? $asset->bookings->first()->guest_name;
                }
            }

            return [
                'id' => $asset->id,
                'title' => $asset->title,
                'city' => $asset->city->name ?? '',
                'address' => $asset->address,
                'category' => $asset->type->category->name ?? 'Lainnya',
                'type' => $asset->type->name ?? 'Lainnya',
                'verification_status' => $vStatus,
                'verification_note' => $asset->verification_note ?? null,
                'thumbnail' => $thumbnail,
                'image' => $thumbnail,
                'tenant' => $tenant,
                'price' => $price,
                'rent_period' => $asset->type->rental_unit ?? 'Bulan',
                'has_units' => $hasUnits,
                'total_units' => $totalUnits,
                'available_units' => $availableUnits,
                'occupied_units' => $occupiedUnits,
                'status' => $status,
                'occupancy' => $hasUnits ? "{$occupiedUnits}/{$totalUnits} Unit" : ($status === 'Tersewa' ? '1/1 Unit' : '0/1 Unit'),
            ];
        });

        $kategoriPropertiGroups = \App\Models\asset_category::with('types')->get()->map(function($category) {
            return [
                'label' => $category->name,
                'options' => $category->types->pluck('name')->toArray()
            ];
        });

        return inertia('owner/Asset/index', [
            'properties' => $properties,
            'stats' => $stats,
            'kategoriPropertiGroups' => $kategoriPropertiGroups,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return inertia('owner/Asset/Create/Index');
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
