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
use App\Models\asset_faq;
use App\Models\asset_policy;
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
            ->withSum('units as total_units_quantity', 'quantity')
            ->withCount([
                'bookings as active_bookings_count' => function ($q) {
                    $q->where('end_date', '>=', now()->format('Y-m-d'))
                      ->whereNotIn('booking_status', ['cancelled', 'rejected']);
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

            $tUnits = $assetStat->total_units_quantity > 0 ? $assetStat->total_units_quantity : 1;
            $oUnits = $assetStat->active_bookings_count;

            if ($oUnits > $tUnits) $oUnits = $tUnits;

            $totalAsset += $tUnits;
            $totalOccupied += $oUnits;
            $totalAvailable += ($tUnits - $oUnits);
        }

        $stats = [
            'totalAssetCount' => $statsQuery->count(),
            'totalAsset' => $totalAsset,
            'totalAvailable' => $totalAvailable,
            'totalOccupied' => $totalOccupied,
            'totalPendingVerification' => $totalPendingVerification,
        ];

        // Query Utama untuk Tabel
        $query = asset::with([
                'type:id,name,category_id,allow_units',
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
                $totalUnits = $asset->units->sum('quantity') ?: 1;
                // H2 Fix: count occupied via units.bookings (asset_unit_id != null, already filtered in eager load)
                $occupiedUnits = $asset->units->sum(fn($unit) => $unit->bookings->count());
            } else {
                if ($asset->bookings->isNotEmpty()) {
                    $occupiedUnits = 1;
                }
            }

            if ($occupiedUnits > $totalUnits) $occupiedUnits = $totalUnits;

            $availableUnits = $totalUnits - $occupiedUnits;
            $status = $availableUnits > 0 ? 'Tersedia' : 'Tersewa';

            $vStatus = $asset->status;

            // H4 Fix: Ambil harga termurah dari semua pricings (bukan baris pertama)
            $price = 0;
            if ($asset->pricings->isNotEmpty()) {
                $price = $asset->pricings->min('price');
            }

            $thumbnail = null;
            if ($asset->thumbnailImages->isNotEmpty()) {
                $thumbnail = $asset->thumbnailImages->first()->image ?? $asset->thumbnailImages->first()->path ?? $asset->thumbnailImages->first()->url ?? null;
            } elseif ($vStatus === 'draft' && !empty($asset->draft_payload['thumbnail'])) {
                $thumbnail = $asset->draft_payload['thumbnail'];
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
                'slug' => $asset->slug,
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
                'rent_period' => $asset->pricings->first()->rental_unit ?? 'month',
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
        $categories = asset_category::with(['types:id,category_id,name,allow_units'])
            ->get(['id', 'name']);

        return inertia('owner/Asset/Create/Index', [
            'categories' => $categories,
        ]);
    }

    /**
     * Tampilkan halaman Edit Draft
     */
    public function editDraft($id)
    {
        $ownerProfile = auth()->user()->ownerProfile;
        if (!$ownerProfile) {
            return redirect()->route('Home')->with('error', 'Silakan lengkapi profil owner Anda terlebih dahulu.');
        }

        $asset = asset::where('id', $id)
            ->where('owner_profile_id', $ownerProfile->id)
            ->where('status', 'draft')
            ->firstOrFail();

        $categories = asset_category::with(['types:id,category_id,name,allow_units'])
            ->get(['id', 'name']);

        return inertia('owner/Asset/Create/Index', [
            'categories' => $categories,
            'draftData' => $asset->draft_payload,
            'draftId' => $asset->id,
        ]);
    }

    /**
     * Simpan gambar sementara untuk mode Draft
     */
    public function uploadTemp(Request $request)
    {
        $request->validate([
            'file' => 'required|image|mimes:jpeg,png,jpg,webp|max:5120',
        ]);

        $path = $request->file('file')->store('uploads/temp', 'public');

        return response()->json([
            'path' => $path,
            'url' => asset('storage/' . $path)
        ]);
    }

    /**
     * Simpan otomatis progress form sebagai draft (Auto-save)
     */
    public function autoSaveDraft(Request $request)
    {
        $ownerProfile = auth()->user()->ownerProfile;
        if (!$ownerProfile) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $payload = $request->all();
        $assetId = $request->input('draft_id');
        $title = $request->input('title') ?: 'Draft Aset';

        if ($assetId) {
            $asset = asset::where('id', $assetId)
                ->where('owner_profile_id', $ownerProfile->id)
                ->first();

            if ($asset) {
                $oldLat = $asset->draft_payload['latitude'] ?? null;
                $oldLon = $asset->draft_payload['longitude'] ?? null;

                $asset->update([
                    'title' => $title,
                    'draft_payload' => $payload,
                ]);

                if (!empty($payload['latitude']) && !empty($payload['longitude']) && $payload['latitude'] != 0) {
                    if ($oldLat != $payload['latitude'] || $oldLon != $payload['longitude']) {
                        \Illuminate\Support\Facades\Cache::forget("osm_sync_asset_{$asset->id}");
                        \App\Jobs\FetchNearbyPlacesJob::dispatch(
                            (float) $payload['latitude'],
                            (float) $payload['longitude'],
                            $asset->id
                        );
                    }
                }

                return response()->json(['message' => 'Draft updated', 'draft_id' => $asset->id]);
            }
        }

        $slug = Str::slug($title) . '-' . Str::random(6);
        while (asset::where('slug', $slug)->exists()) {
            $slug = Str::slug($title) . '-' . Str::random(6);
        }

        $asset = asset::create([
            
            'owner_profile_id' => $ownerProfile->id,
            'asset_type_id'    => $request->input('asset_type_id') ?: \App\Models\asset_type::first()->id ?? 1,
            'title'            => $title,
            'slug'             => $slug,
            'status'           => 'draft',
            'draft_payload'    => $payload,
            'description'      => '-',
            'detail'           => [],
            'province_code'    => '-',
            'city_code'        => '-',
            'district_code'    => '-',
            'village_code'     => '-',
            'address'          => '-',
            'latitude'         => '0',
            'longitude'        => '0',
        ]);

        if (!empty($payload['latitude']) && !empty($payload['longitude']) && $payload['latitude'] != 0) {
            \App\Jobs\FetchNearbyPlacesJob::dispatch(
                (float) $payload['latitude'],
                (float) $payload['longitude'],
                $asset->id
            );
        }

        return response()->json(['message' => 'Draft created', 'draft_id' => $asset->id]);
    }

    /**
     * Preview nearby places for map pin
     */
    public function previewNearby(Request $request)
    {
        $lat = $request->query('lat');
        $lon = $request->query('lon');
        
        if (!$lat || !$lon || $lat == 0 || $lon == 0) {
            return response()->json([]);
        }

        $nearbyPlaces = \App\Services\OpenStreetMapService::getNearbyPlaces((float) $lat, (float) $lon, null, 3000, true);
        
        return response()->json($nearbyPlaces);
    }

    /**
     * Store a newly created resource in storage.
     * Menggunakan DB Transaction - jika satu bagian gagal, semua di-rollback.
     */
    public function store(StoreAssetRequest $request)
    {
        $ownerProfile = auth()->user()->ownerProfile;

        $assetType = asset_type::findOrFail($request->asset_type_id);
        $allowUnits = (bool) $assetType->allow_units;

        DB::beginTransaction();

        try {
            // --- 1. Buat Aset ---
            $draftId = $request->input('draft_id');
            if ($draftId) {
                $assetRecord = asset::where('id', $draftId)
                    ->where('owner_profile_id', $ownerProfile->id)
                    ->firstOrFail();

                $assetRecord->update([
                    'asset_type_id'    => $request->asset_type_id,
                    'title'            => $request->title,
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
                    'draft_payload'    => null, // Hapus draft
                ]);
            } else {
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
            }

            // --- 2. Fasilitas Aset ---
            // Simpan fasilitas aset tanpa mempedulikan allowUnits, karena hotel pun punya fasilitas gedung (parkir, resepsionis)
            if (!empty($request->facility_ids)) {
                $assetRecord->facilities()->sync($request->facility_ids);
            }

            // --- 3. Harga Aset (jika tanpa unit) ---
            if (!$allowUnits && $request->pricings) {
                // H5: Cek duplikat paket harga sebelum insert
                $seen = [];
                foreach ($request->pricings as $pricing) {
                    $key = $pricing['duration'] . '-' . $pricing['rental_unit'];
                    if (in_array($key, $seen)) {
                        throw new \Exception('Terdapat paket harga duplikat: ' . $pricing['duration'] . ' ' . $pricing['rental_unit']);
                    }
                    $seen[] = $key;
                }

                foreach ($request->pricings as $pricing) {
                    asset_pricing::create([
                        'asset_id'      => $assetRecord->id,
                        'asset_unit_id' => null,
                        'duration'      => $pricing['duration'],
                        'rental_unit'   => $pricing['rental_unit'],
                        'price'         => $pricing['price'],
                    ]);
                }
            }

            // --- 4. Unit Aset (jika allow_units = true) ---
            if ($allowUnits && $request->units) {
                foreach ($request->units as $index => $unitData) {
                    $unit = asset_units::create([
                        'asset_id' => $assetRecord->id,
                        'name'        => $unitData['name'],
                        'description' => $unitData['description'] ?? '',
                        'detail'      => $unitData['detail'] ?? [],
                        'quantity'    => $unitData['quantity'],
                        'status'      => 'active',
                    ]);

                    // Harga per unit — simpan asset_id juga agar query per asset masih bisa menemukan pricing ini
                    if (isset($unitData['pricings'])) {
                        // H5: Cek duplikat paket harga unit sebelum insert
                        $seenUnit = [];
                        foreach ($unitData['pricings'] as $pricing) {
                            $key = $pricing['duration'] . '-' . $pricing['rental_unit'];
                            if (in_array($key, $seenUnit)) {
                                throw new \Exception('Terdapat paket harga duplikat pada unit ' . $unitData['name'] . ': ' . $pricing['duration'] . ' ' . $pricing['rental_unit']);
                            }
                            $seenUnit[] = $key;
                        }

                        foreach ($unitData['pricings'] as $pricing) {
                            asset_pricing::create([
                                'asset_id'      => $assetRecord->id,
                                'asset_unit_id' => $unit->id,
                                'duration'      => $pricing['duration'],
                                'rental_unit'   => $pricing['rental_unit'],
                                'price'         => $pricing['price'],
                            ]);
                        }
                    }

                    // Fasilitas unit
                    $facilityIds = $unitData['facility_ids'] ?? [];
                    if (!empty($facilityIds)) {
                        $unit->facilities()->sync($facilityIds);
                    } else {
                        $unit->facilities()->sync([]);
                    }

                    // Foto unit
                    $unitPhotosInput = $unitData['photos'] ?? [];
                    // Jika dari FormData, file mungkin ada di request->file. Jika JSON, file berupa string di $unitPhotosInput
                    $unitPhotosFiles = $request->file("units.{$index}.photos") ?? [];

                    foreach ($unitPhotosInput as $photoIdx => $photoGroup) {
                        $galleryCategoryId = $photoGroup['gallery_category_id'] ?? null;

                        // Coba ambil dari FormData dulu
                        $files = $unitPhotosFiles[$photoIdx]['files'] ?? [];

                        // Jika tidak ada di FormData, coba ambil dari input biasa (kasus URL string dari Temp Upload)
                        if (empty($files) && isset($photoGroup['files'])) {
                            $files = $photoGroup['files'];
                        }

                        foreach ($files as $file) {
                            $path = null;
                            if ($file instanceof \Illuminate\Http\UploadedFile) {
                                $path = $file->store('uploads/assets', 'public');
                            } elseif (is_string($file) && str_starts_with($file, 'uploads/temp/')) {
                                $newPath = str_replace('uploads/temp/', 'uploads/assets/', $file);
                                if (Storage::disk('public')->exists($file)) {
                                    Storage::disk('public')->move($file, $newPath);
                                    $path = $newPath;
                                }
                            }

                            if ($path) {
                                asset_image::create([
                                    'asset_id'           => $assetRecord->id,
                                    'asset_unit_id'      => $unit->id,
                                    'gallery_category_id' => $galleryCategoryId,
                                    'image'              => $path,
                                ]);
                            }
                        }
                    }

                    // Thumbnail unit
                    $thumbnailPath = null;
                    if ($request->hasFile("units.{$index}.thumbnail")) {
                        $thumbnailPath = $request->file("units.{$index}.thumbnail")->store('uploads/assets/thumbnails', 'public');
                    } elseif (isset($unitData['thumbnail']) && is_string($unitData['thumbnail']) && str_starts_with($unitData['thumbnail'], 'uploads/temp/')) {
                        $newPath = str_replace('uploads/temp/', 'uploads/assets/thumbnails/', $unitData['thumbnail']);
                        if (Storage::disk('public')->exists($unitData['thumbnail'])) {
                            Storage::disk('public')->move($unitData['thumbnail'], $newPath);
                            $thumbnailPath = $newPath;
                        }
                    }

                    if ($thumbnailPath) {
                        asset_image::create([
                            'asset_id'           => $assetRecord->id,
                            'asset_unit_id'      => $unit->id,
                            'gallery_category_id' => null, // Thumbnail tidak wajib ada kategori
                            'image'              => $thumbnailPath,
                            'is_thumbnail'       => true,
                        ]);
                    }
                }
            }

            // --- 5. Upload Foto ---
            $photosInput = $request->input('photos', []);
            $photosFiles = $request->file('photos') ?? [];

            foreach ($photosInput as $index => $photoGroup) {
                $galleryCategoryId = $photoGroup['gallery_category_id'] ?? null;
                $files = $photosFiles[$index]['files'] ?? [];

                if (empty($files) && isset($photoGroup['files'])) {
                    $files = $photoGroup['files'];
                }

                foreach ($files as $file) {
                    $path = null;
                    if ($file instanceof \Illuminate\Http\UploadedFile) {
                        $path = $file->store('uploads/assets', 'public');
                    } elseif (is_string($file) && str_starts_with($file, 'uploads/temp/')) {
                        $newPath = str_replace('uploads/temp/', 'uploads/assets/', $file);
                        if (Storage::disk('public')->exists($file)) {
                            Storage::disk('public')->move($file, $newPath);
                            $path = $newPath;
                        }
                    }

                    if ($path) {
                        asset_image::create([
                            'asset_id'           => $assetRecord->id,
                            'asset_unit_id'      => null,
                            'gallery_category_id' => $galleryCategoryId,
                            'image'              => $path,
                        ]);
                    }
                }
            }

            // --- 6. Thumbnail Aset ---
            $mainThumbnailPath = null;
            if ($request->hasFile('thumbnail')) {
                $mainThumbnailPath = $request->file('thumbnail')->store('uploads/assets/thumbnails', 'public');
            } elseif (is_string($request->input('thumbnail')) && str_starts_with($request->input('thumbnail'), 'uploads/temp/')) {
                $thumbnailString = $request->input('thumbnail');
                $newPath = str_replace('uploads/temp/', 'uploads/assets/thumbnails/', $thumbnailString);
                if (Storage::disk('public')->exists($thumbnailString)) {
                    Storage::disk('public')->move($thumbnailString, $newPath);
                    $mainThumbnailPath = $newPath;
                }
            }

            if ($mainThumbnailPath) {
                asset_image::create([
                    'asset_id'           => $assetRecord->id,
                    'asset_unit_id'      => null,
                    'gallery_category_id' => null,
                    'image'              => $mainThumbnailPath,
                    'is_thumbnail'       => true,
                ]);
            }

            // --- 7. FAQ ---
            if ($request->has('faqs')) {
                foreach ($request->faqs as $index => $faqData) {
                    if (!empty($faqData['question']) && !empty($faqData['answer'])) {
                        asset_faq::create([
                            'asset_id'   => $assetRecord->id,
                            'question'   => $faqData['question'],
                            'answer'     => $faqData['answer'],
                            'sort_order' => $index + 1,
                        ]);
                    }
                }
            }

            // --- 8. Kebijakan ---
            if ($request->has('policies')) {
                foreach ($request->policies as $index => $policyData) {
                    if (!empty($policyData['title'])) {
                        asset_policy::create([
                            'asset_id'    => $assetRecord->id,
                            'title'       => $policyData['title'],
                            'description' => $policyData['description'] ?? null,
                            'sort_order'  => $index + 1,
                        ]);
                    }
                }
            }

            DB::commit();

            // Dispatch job to fetch nearby places in the background
            \App\Jobs\FetchNearbyPlacesJob::dispatch(
                (float) $assetRecord->latitude,
                (float) $assetRecord->longitude,
                $assetRecord->id
            );

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
    public function show(Request $request, string $id)
    {
        $asset = asset::with([
            'type:id,name,allow_units,category_id',
            'type.category:id,name,icon',
            'images.gallery_category',
            'thumbnailImages',
            'faqs',
            'policies',
            'pricings',
            'city',
            'province',
            'district',
            'village',
            'ownerProfile.user',
            'reviews.user',
            'reviews.reviewTagItems.reviewTag',
            'facilities:id,name,facility_category_id',
            'facilities.category:id,name,icon',
            'units.pricings',
            'units.images.gallery_category',
            'units.thumbnailImage',
            'units.facilities:id,name,facility_category_id',
            'units.facilities.category:id,name,icon',
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
        ->withAvg('reviews', 'rating')
        ->withCount([
            'reviews',
            'favorites'
        ])
        ->where(function($query) use ($id) {
            $query->where('id', $id)->orWhere('slug', $id);
        })
        ->firstOrFail();

        $ownerProfile = $request->user()->ownerProfile;
        if (!$ownerProfile || $asset->owner_profile_id !== $ownerProfile->id) {
            abort(403, 'Anda tidak berhak mengakses aset ini.');
        }

        // For owner page, we might want to calculate the summary
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

        $asset->owner_status = $status;
        $asset->owner_occupancy = $hasUnits ? "{$occupiedUnits}/{$totalUnits} Unit" : ($status === 'Tersewa' ? '1/1 Unit' : '0/1 Unit');

        // Kategori galeri bersifat global — satu set kategori untuk semua foto (asset & unit)
        $galleryCategories = \App\Models\galery_category::orderBy('name')->get();

        $masterFacilityCategories = \App\Models\facility_category::with(['facilities' => function($q) {
            $q->where('is_active', true)->orderBy('sort_order');
        }])->orderBy('sort_order')->get();

        return inertia('owner/Asset/show', [
            'asset'                    => $asset,
            'galleryCategories'        => $galleryCategories,
            'masterFacilityCategories' => $masterFacilityCategories,
        ]);
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
        $asset = asset::where('slug', $id)->orWhere('id', $id)->firstOrFail();

        $ownerProfile = $request->user()->ownerProfile;
        if (!$ownerProfile || $asset->owner_profile_id !== $ownerProfile->id) {
            abort(403, 'Anda tidak berhak mengubah aset ini.');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'address' => 'required|string|max:500',
        ]);

        $asset->update($validated);

        return redirect()->back()->with('success', 'Detail aset berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id)
    {
        $asset = asset::where('slug', $id)->orWhere('id', $id)->firstOrFail();

        $ownerProfile = $request->user()->ownerProfile;
        if (!$ownerProfile || $asset->owner_profile_id !== $ownerProfile->id) {
            abort(403, 'Anda tidak berhak menghapus aset ini.');
        }

        if (!in_array($asset->status, ['draft', 'pending', 'rejected'])) {
            return redirect()->back()->with('error', 'Aset yang sudah disetujui atau nonaktif tidak dapat dihapus.');
        }

        // Check for active bookings
        $hasActiveBookings = \App\Models\booking::where('asset_id', $asset->id)
            ->whereIn('booking_status', ['pending', 'confirmed', 'active'])
            ->exists();

        if ($hasActiveBookings) {
            return redirect()->back()->with('error', 'Tidak dapat menghapus aset karena terdapat penyewaan yang sedang aktif atau menunggu konfirmasi.');
        }

        $asset->delete();

        return redirect()->route('owner.asset.index')->with('success', 'Aset berhasil dihapus permanen.');
    }

    /**
     * Mengubah status aset (Aktif/Nonaktif)
     */
    public function toggleStatus(Request $request, string $id)
    {
        $asset = asset::where('slug', $id)->orWhere('id', $id)->firstOrFail();

        $ownerProfile = $request->user()->ownerProfile;
        if (!$ownerProfile || $asset->owner_profile_id !== $ownerProfile->id) {
            abort(403, 'Anda tidak berhak mengubah status aset ini.');
        }

        if ($asset->status === 'inactive') {
            $asset->update(['status' => 'approved']);
            return redirect()->back()->with('success', 'Aset berhasil diaktifkan.');
        } else {
            $hasActiveBookings = \App\Models\booking::where('asset_id', $asset->id)
                ->whereIn('booking_status', ['pending', 'confirmed', 'active'])
                ->exists();

            if ($hasActiveBookings) {
                return redirect()->back()->with('error', 'Tidak dapat menonaktifkan aset karena terdapat penyewaan yang sedang aktif atau menunggu konfirmasi.');
            }

            $asset->update(['status' => 'inactive']);
            return redirect()->back()->with('success', 'Aset berhasil dinonaktifkan.');
        }
    }

    /**
     * Menambahkan fasilitas ke aset
     */
    public function storeFacility(Request $request, string $id)
    {
        $asset = asset::where('slug', $id)->orWhere('id', $id)->firstOrFail();

        $ownerProfile = $request->user()->ownerProfile;
        if (!$ownerProfile || $asset->owner_profile_id !== $ownerProfile->id) {
            abort(403, 'Anda tidak berhak mengubah aset ini.');
        }

        $validated = $request->validate([
            'facility_id' => 'required|exists:facilities,id',
        ]);

        $asset->facilities()->syncWithoutDetaching([$validated['facility_id']]);

        return redirect()->back()->with('success', 'Fasilitas berhasil ditambahkan.');
    }

    /**
     * Menghapus fasilitas dari aset
     */
    public function destroyFacility(Request $request, string $id, string $facility_id)
    {
        $asset = asset::where('slug', $id)->orWhere('id', $id)->firstOrFail();

        $ownerProfile = $request->user()->ownerProfile;
        if (!$ownerProfile || $asset->owner_profile_id !== $ownerProfile->id) {
            abort(403, 'Anda tidak berhak mengubah aset ini.');
        }

        $asset->facilities()->detach($facility_id);

        return redirect()->back()->with('success', 'Fasilitas berhasil dihapus.');
    }

    /**
     * Menambahkan unit baru ke aset
     */
    public function storeUnit(Request $request, string $id)
    {
        $asset = asset::where('slug', $id)->orWhere('id', $id)->firstOrFail();

        $ownerProfile = $request->user()->ownerProfile;
        if (!$ownerProfile || $asset->owner_profile_id !== $ownerProfile->id) {
            abort(403, 'Anda tidak berhak mengubah aset ini.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'quantity' => 'required|integer|min:1',
            'pricings' => 'required|array|min:1|max:1',
            'pricings.*.duration' => 'required|integer|min:1',
            'pricings.*.rental_unit' => 'required|string|in:hour,day,night,week,month',
            'pricings.*.price' => 'required|numeric|min:0',
            'detail' => 'nullable|array',
            'facilities' => 'nullable|array',
            'facilities.*' => 'exists:facilities,id',
            'new_images' => 'nullable|array',
            'new_images.*.file' => 'required|image|mimes:jpeg,png,jpg,webp|max:2048',
            'new_images.*.category_id' => 'required|exists:galery_categories,id',
            'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
        ]);

        $unit = $asset->units()->create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? '',
            'quantity' => $validated['quantity'],
            'detail' => $validated['detail'] ?? [],
            'status' => 'active',
        ]);

        // H5: Cek duplikat paket harga unit sebelum insert
        $seenPricings = [];
        foreach ($validated['pricings'] as $pricing) {
            $key = $pricing['duration'] . '-' . $pricing['rental_unit'];
            if (in_array($key, $seenPricings)) {
                return redirect()->back()->withErrors([
                    'pricings' => 'Terdapat paket harga duplikat: ' . $pricing['duration'] . ' ' . $pricing['rental_unit'] . '. Setiap kombinasi durasi dan satuan waktu harus unik.',
                ]);
            }
            $seenPricings[] = $key;
        }

        foreach ($validated['pricings'] as $pricing) {
            $unit->pricings()->create([
                'asset_id' => $asset->id,
                'duration' => $pricing['duration'],
                'rental_unit' => $pricing['rental_unit'],
                'price' => $pricing['price'],
            ]);
        }

        if (!empty($validated['facilities'])) {
            $unit->facilities()->sync($validated['facilities']);
        }

        if (!empty($validated['new_images'])) {
            foreach ($validated['new_images'] as $imgData) {
                $path = $imgData['file']->store('uploads/assets', 'public');
                $asset->images()->create([
                    'asset_unit_id' => $unit->id,
                    'gallery_category_id' => $imgData['category_id'],
                    'image' => $path,
                ]);
            }
        }

        if ($request->hasFile('thumbnail')) {
            $path = $request->file('thumbnail')->store('uploads/assets/thumbnails', 'public');
            $asset->images()->create([
                'asset_unit_id' => $unit->id,
                'image' => $path,
                'is_thumbnail' => true,
            ]);
        }

        return redirect()->back()->with('success', 'Unit berhasil ditambahkan.');
    }

    /**
     * Memperbarui data unit
     */
    /**
     * Memperbarui daftar harga untuk aset tunggal (tanpa unit)
     */
    public function updatePricings(Request $request, string $id)
    {
        $asset = asset::where('slug', $id)->orWhere('id', $id)->firstOrFail();

        $ownerProfile = $request->user()->ownerProfile;
        if (!$ownerProfile || $asset->owner_profile_id !== $ownerProfile->id) {
            abort(403, 'Anda tidak berhak mengubah aset ini.');
        }

        if ($asset->type->allow_units) {
            abort(400, 'Aset ini menggunakan unit, silakan atur harga per unit.');
        }

        $validated = $request->validate([
            'pricings' => 'required|array|min:1',
            'pricings.*.duration' => 'required|integer|min:1',
            'pricings.*.rental_unit' => 'required|string|in:hour,day,night,week,month',
            'pricings.*.price' => 'required|numeric|min:0',
        ]);

        // Cek duplikat
        $seen = [];
        foreach ($validated['pricings'] as $pricing) {
            $key = $pricing['duration'] . '-' . $pricing['rental_unit'];
            if (in_array($key, $seen)) {
                return redirect()->back()->withErrors(['pricings' => 'Terdapat paket harga dengan durasi dan satuan waktu yang sama.']);
            }
            $seen[] = $key;
        }

        // Hapus semua harga lama (yang tidak terkait ke unit)
        $asset->pricings()->whereNull('asset_unit_id')->delete();

        // Simpan harga baru
        foreach ($validated['pricings'] as $pricing) {
            $asset->pricings()->create([
                'asset_unit_id' => null,
                'duration' => $pricing['duration'],
                'rental_unit' => $pricing['rental_unit'],
                'price' => $pricing['price'],
            ]);
        }

        return redirect()->back()->with('success', 'Paket harga berhasil diperbarui.');
    }

    public function updateUnit(Request $request, string $id, string $unit_id)
    {
        $asset = asset::where('slug', $id)->orWhere('id', $id)->firstOrFail();

        $ownerProfile = $request->user()->ownerProfile;
        if (!$ownerProfile || $asset->owner_profile_id !== $ownerProfile->id) {
            abort(403, 'Anda tidak berhak mengubah aset ini.');
        }

        $unit = $asset->units()->findOrFail($unit_id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'quantity' => 'required|integer|min:1',
            'pricings' => 'required|array|min:1|max:1',
            'pricings.*.duration' => 'required|integer|min:1',
            'pricings.*.rental_unit' => 'required|string|in:hour,day,night,week,month',
            'pricings.*.price' => 'required|numeric|min:0',
            'detail' => 'nullable|array',
            'facilities' => 'nullable|array',
            'facilities.*' => 'exists:facilities,id',
            'new_images' => 'nullable|array',
            'new_images.*.file' => 'required|image|mimes:jpeg,png,jpg,webp|max:2048',
            'new_images.*.category_id' => 'required|exists:galery_categories,id',
            'deleted_images' => 'nullable|array',
            'deleted_images.*' => 'exists:asset_images,id',
            'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
        ]);

        $unit->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? '',
            'quantity' => $validated['quantity'],
            'detail' => $validated['detail'] ?? [],
        ]);

        // Ganti semua pricing dengan yang baru (hapus lalu buat lagi lebih mudah untuk handle unique)
        $unit->pricings()->delete();
        foreach ($validated['pricings'] as $pricing) {
            $unit->pricings()->create([
                'asset_id' => $asset->id,
                'duration' => $pricing['duration'],
                'rental_unit' => $pricing['rental_unit'],
                'price' => $pricing['price'],
            ]);
        }

        if (isset($validated['facilities'])) {
            $unit->facilities()->sync($validated['facilities']);
        } else {
            $unit->facilities()->detach();
        }

        // Handle deleted images
        if (!empty($validated['deleted_images'])) {
            foreach ($validated['deleted_images'] as $imageId) {
                $img = $asset->images()->where('asset_unit_id', $unit->id)->find($imageId);
                if ($img) {
                    if ($img->image && !str_starts_with($img->image, 'http') && !str_starts_with($img->image, 'assets/')) {
                        Storage::disk('public')->delete($img->image);
                    }
                    $img->delete();
                }
            }
        }

        // Handle new images
        if (!empty($validated['new_images'])) {
            foreach ($validated['new_images'] as $imgData) {
                $path = $imgData['file']->store('uploads/assets', 'public');
                $asset->images()->create([
                    'asset_unit_id' => $unit->id,
                    'gallery_category_id' => $imgData['category_id'],
                    'image' => $path,
                ]);
            }
        }

        // Handle Thumbnail Update
        if ($request->hasFile('thumbnail')) {
            // Hapus thumbnail lama
            $oldThumbnail = $asset->images()->where('asset_unit_id', $unit->id)->where('is_thumbnail', true)->first();
            if ($oldThumbnail) {
                if ($oldThumbnail->image && !str_starts_with($oldThumbnail->image, 'http') && !str_starts_with($oldThumbnail->image, 'assets/')) {
                    Storage::disk('public')->delete($oldThumbnail->image);
                }
                $oldThumbnail->delete();
            }

            $path = $request->file('thumbnail')->store('uploads/assets/thumbnails', 'public');
            $asset->images()->create([
                'asset_unit_id' => $unit->id,
                'image' => $path,
                'is_thumbnail' => true,
            ]);
        }

        return redirect()->back()->with('success', 'Unit berhasil diperbarui.');
    }

    /**
     * Menambahkan foto ke aset
     */
    public function storeImage(Request $request, string $id)
    {
        $asset = asset::where('slug', $id)->orWhere('id', $id)->firstOrFail();

        $ownerProfile = $request->user()->ownerProfile;
        if (!$ownerProfile || $asset->owner_profile_id !== $ownerProfile->id) {
            abort(403, 'Anda tidak berhak mengubah aset ini.');
        }

        // Jika request memiliki file 'thumbnail' (Untuk Aset Utama)
        if ($request->hasFile('thumbnail')) {
            $request->validate([
                'thumbnail' => 'image|mimes:jpeg,png,jpg,webp|max:5120',
            ]);

            // Hapus thumbnail lama jika ada
            $oldThumbnail = $asset->images()->where('is_thumbnail', true)->whereNull('asset_unit_id')->first();
            if ($oldThumbnail) {
                if ($oldThumbnail->image && !str_starts_with($oldThumbnail->image, 'http') && !str_starts_with($oldThumbnail->image, 'assets/')) {
                    Storage::disk('public')->delete($oldThumbnail->image);
                }
                $oldThumbnail->delete();
            }

            $path = $request->file('thumbnail')->store('uploads/assets/thumbnails', 'public');
            $asset->images()->create([
                'image' => $path,
                'is_thumbnail' => true,
            ]);
            return redirect()->back()->with('success', 'Thumbnail berhasil diunggah.');
        }

        $request->validate([
            'images' => 'required|array',
            'images.*' => 'image|mimes:jpeg,png,jpg,webp|max:5120',
            'gallery_category_id' => 'nullable|exists:galery_categories,id',
        ]);

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                $path = $file->store('uploads/assets', 'public');

                $asset->images()->create([
                    'gallery_category_id' => $request->gallery_category_id,
                    'image' => $path,
                ]);
            }
        }

        return redirect()->back()->with('success', 'Foto berhasil diunggah.');
    }

    /**
     * Menghapus foto dari aset
     */
    public function destroyImage(Request $request, string $id, string $image_id)
    {
        $asset = asset::where('slug', $id)->orWhere('id', $id)->firstOrFail();

        $ownerProfile = $request->user()->ownerProfile;
        if (!$ownerProfile || $asset->owner_profile_id !== $ownerProfile->id) {
            abort(403, 'Anda tidak berhak mengubah aset ini.');
        }

        $image = $asset->images()->findOrFail($image_id);

        // Hapus file fisik dari storage jika ada dan bukan dari seeder assets/
        if ($image->image && !str_starts_with($image->image, 'http') && !str_starts_with($image->image, 'assets/')) {
            Storage::disk('public')->delete($image->image);
        }

        $image->delete();

        return redirect()->back()->with('success', 'Foto berhasil dihapus.');
    }

    // =================== FAQ ===================

    public function storeFaq(Request $request, string $id)
    {
        $asset = asset::where('slug', $id)->orWhere('id', $id)->firstOrFail();
        $ownerProfile = $request->user()->ownerProfile;
        if (!$ownerProfile || $asset->owner_profile_id !== $ownerProfile->id) {
            abort(403);
        }
        $request->validate([
            'question'   => 'required|string|max:300',
            'answer'     => 'required|string|max:2000',
            'sort_order' => 'nullable|integer',
        ]);
        $sortOrder = $request->sort_order ?? ($asset->faqs()->max('sort_order') + 1);
        asset_faq::create([
            'asset_id'   => $asset->id,
            'question'   => $request->question,
            'answer'     => $request->answer,
            'sort_order' => $sortOrder,
        ]);
        return redirect()->back()->with('success', 'FAQ berhasil ditambahkan.');
    }

    public function updateFaq(Request $request, string $id, int $faqId)
    {
        $asset = asset::where('slug', $id)->orWhere('id', $id)->firstOrFail();
        $ownerProfile = $request->user()->ownerProfile;
        if (!$ownerProfile || $asset->owner_profile_id !== $ownerProfile->id) {
            abort(403);
        }
        $request->validate([
            'question'   => 'required|string|max:300',
            'answer'     => 'required|string|max:2000',
        ]);
        $faq = asset_faq::where('asset_id', $asset->id)->findOrFail($faqId);
        $faq->update([
            'question' => $request->question,
            'answer'   => $request->answer,
        ]);
        return redirect()->back()->with('success', 'FAQ berhasil diperbarui.');
    }

    public function destroyFaq(Request $request, string $id, int $faqId)
    {
        $asset = asset::where('slug', $id)->orWhere('id', $id)->firstOrFail();
        $ownerProfile = $request->user()->ownerProfile;
        if (!$ownerProfile || $asset->owner_profile_id !== $ownerProfile->id) {
            abort(403);
        }
        asset_faq::where('asset_id', $asset->id)->findOrFail($faqId)->delete();
        return redirect()->back()->with('success', 'FAQ berhasil dihapus.');
    }

    // =================== KEBIJAKAN ===================

    public function storePolicy(Request $request, string $id)
    {
        $asset = asset::where('slug', $id)->orWhere('id', $id)->firstOrFail();
        $ownerProfile = $request->user()->ownerProfile;
        if (!$ownerProfile || $asset->owner_profile_id !== $ownerProfile->id) {
            abort(403);
        }
        $request->validate([
            'title'       => 'required|string|max:200',
            'description' => 'nullable|string|max:2000',
        ]);
        $sortOrder = $asset->policies()->max('sort_order') + 1;
        asset_policy::create([
            'asset_id'    => $asset->id,
            'title'       => $request->title,
            'description' => $request->description,
            'sort_order'  => $sortOrder,
        ]);
        return redirect()->back()->with('success', 'Kebijakan berhasil ditambahkan.');
    }

    public function updatePolicy(Request $request, string $id, int $policyId)
    {
        $asset = asset::where('slug', $id)->orWhere('id', $id)->firstOrFail();
        $ownerProfile = $request->user()->ownerProfile;
        if (!$ownerProfile || $asset->owner_profile_id !== $ownerProfile->id) {
            abort(403);
        }
        $request->validate([
            'title'       => 'required|string|max:200',
            'description' => 'nullable|string|max:2000',
        ]);
        $policy = asset_policy::where('asset_id', $asset->id)->findOrFail($policyId);
        $policy->update([
            'title'       => $request->title,
            'description' => $request->description,
        ]);
        return redirect()->back()->with('success', 'Kebijakan berhasil diperbarui.');
    }

    public function destroyPolicy(Request $request, string $id, int $policyId)
    {
        $asset = asset::where('slug', $id)->orWhere('id', $id)->firstOrFail();
        $ownerProfile = $request->user()->ownerProfile;
        if (!$ownerProfile || $asset->owner_profile_id !== $ownerProfile->id) {
            abort(403);
        }
        asset_policy::where('asset_id', $asset->id)->findOrFail($policyId)->delete();
        return redirect()->back()->with('success', 'Kebijakan berhasil dihapus.');
    }
}
