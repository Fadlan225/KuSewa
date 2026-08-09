<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\Owner\StoreAssetRequest;
use App\Models\asset;
use App\Models\asset_category;
use App\Models\asset_type;
use App\Models\asset_units;
use App\Models\asset_pricing;
use App\Models\asset_image;
use App\Models\asset_facility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

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

        $kategoriPropertiGroups = asset_category::with('types')->get()->map(function($category) {
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
     * Pass all required data from DB to frontend.
     */
    public function create()
    {
        $ownerProfile = auth()->user()->ownerProfile;

        if (!$ownerProfile) {
            return redirect()->route('Home')->with('error', 'Silakan lengkapi profil owner Anda terlebih dahulu.');
        }

        // Kategori aset beserta jenis-jenis di dalamnya
        $categories = asset_category::with(['types:id,category_id,name,rental_unit,allow_units'])
            ->get(['id', 'name']);

        return inertia('owner/Asset/Create/Index', [
            'categories' => $categories,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     * Menggunakan DB Transaction — jika satu bagian gagal, semua di-rollback.
     */
    public function store(StoreAssetRequest $request)
    {
        $ownerProfile = auth()->user()->ownerProfile;

        $assetType = asset_type::findOrFail($request->asset_type_id);
        $allowUnits = (bool) $assetType->allow_units;

        DB::beginTransaction();

        try {
            // --- 1. Buat Aset ---
            $slug = Str::slug($request->title) . '-' . Str::random(6);
            // Pastikan slug unik
            while (asset::where('slug', $slug)->exists()) {
                $slug = Str::slug($request->title) . '-' . Str::random(6);
            }

            $assetRecord = asset::create([
                'owner_profile_id' => $ownerProfile->id,
                'asset_type_id'    => $request->asset_type_id,
                'title'            => $request->title,
                'slug'             => $slug,
                'description'      => $request->description,
                'detail'           => $request->detail ?? [],
                'province_code'    => $request->province_code,
                'city_code'        => $request->city_code,
                'district_code'    => $request->district_code,
                'village_code'     => $request->village_code,
                'postal_code'      => $request->postal_code,
                'address'          => $request->address,
                'latitude'         => $request->latitude,
                'longitude'        => $request->longitude,
                'status'           => 'pending',
            ]);

            // --- 2. Fasilitas Aset (hanya jika tidak menggunakan unit) ---
            if (!$allowUnits && !empty($request->facility_ids)) {
                $assetRecord->facilities()->sync($request->facility_ids);
            }

            // --- 3. Harga Aset (jika tanpa unit) ---
            if (!$allowUnits) {
                asset_pricing::create([
                    'asset_id'      => $assetRecord->id,
                    'asset_unit_id' => null,
                    'price'         => $request->price,
                ]);
            }

            // --- 4. Unit Aset (jika allow_units = true) ---
            if ($allowUnits && $request->units) {
                foreach ($request->units as $unitData) {
                    $unit = asset_units::create([
                        'asset_id' => $assetRecord->id,
                        'name'     => $unitData['name'],
                        'detail'   => $unitData['detail'] ?? [],
                        'quantity' => $unitData['quantity'],
                        'status'   => 'active',
                    ]);

                    // Harga per unit
                    asset_pricing::create([
                        'asset_id'      => null,
                        'asset_unit_id' => $unit->id,
                        'price'         => $unitData['price'],
                    ]);

                    // Fasilitas unit
                    if (!empty($unitData['facility_ids'])) {
                        $unit->facilities()->sync($unitData['facility_ids']);
                    }
                }
            }

            // --- 5. Upload Foto ---
            $photosInput = $request->input('photos', []);
            $photosFiles = $request->file('photos') ?? [];

            foreach ($photosInput as $index => $photoGroup) {
                $galleryCategoryId = $photoGroup['gallery_category_id'] ?? null;
                $files = $photosFiles[$index]['files'] ?? [];

                foreach ($files as $file) {
                    if ($file instanceof \Illuminate\Http\UploadedFile) {
                        $path = $file->store('assets/images', 'public');
                        asset_image::create([
                            'asset_id'           => $assetRecord->id,
                            'asset_unit_id'      => null,
                            'gallery_category_id' => $galleryCategoryId,
                            'image'              => $path,
                        ]);
                    }
                }
            }

            DB::commit();

            return redirect()->route('owner.asset.index')
                ->with('success', 'Aset berhasil ditambahkan dan sedang menunggu verifikasi admin.');

        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->withErrors([
                'general' => 'Terjadi kesalahan saat menyimpan aset: ' . $e->getMessage(),
            ])->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the existing resource.
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
