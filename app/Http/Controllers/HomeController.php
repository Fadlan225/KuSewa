<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AssetView;
use App\Models\favorite;

use Illuminate\Support\Facades\Cache;
use App\Models\asset_category;
use App\Models\asset_type;
use App\Models\asset;
use App\Models\asset_pricing;
use App\Models\search_log;
use App\Models\booking;

class HomeController extends Controller
{
    /**
     * Helper: ambil kota-kota unik dari DB untuk filter lokasi.
     */
    private function getLocationSuggestions(): array
    {
        $suggestions = [];

        // 1. Kota (Cities)
        $cities = \App\Models\asset::where('status', 'approved')->whereNotNull('city_code')
            ->join('cities', 'assets.city_code', '=', 'cities.code')
            ->select('cities.name as city_name')
            ->distinct()
            ->orderBy('cities.name')
            ->get();
        foreach ($cities as $c) {
            $suggestions[] = [
                'id'        => $c->city_name,
                'title'     => $c->city_name,
                'desc'      => "Kota",
                'icon'      => 'fa-solid fa-city',
                'iconColor' => 'text-[#FFC000]',
                'bg'        => 'bg-[#FFC000]/10',
            ];
        }

        // 2. Kecamatan (Districts)
        $districts = \App\Models\asset::where('status', 'approved')->whereNotNull('district_code')
            ->join('districts', 'assets.district_code', '=', 'districts.code')
            ->join('cities', 'districts.city_code', '=', 'cities.code')
            ->select('districts.name as district_name', 'cities.name as city_name')
            ->distinct()
            ->orderBy('districts.name')
            ->get();
        foreach ($districts as $d) {
            $suggestions[] = [
                'id'        => $d->district_name,
                'title'     => $d->district_name,
                'desc'      => "Kecamatan di {$d->city_name}",
                'icon'      => 'fa-solid fa-map-location-dot',
                'iconColor' => 'text-[#FFC000]',
                'bg'        => 'bg-[#FFC000]/10',
            ];
        }

        // 3. Kelurahan (Villages)
        $villages = \App\Models\asset::where('status', 'approved')->whereNotNull('village_code')
            ->join('villages', 'assets.village_code', '=', 'villages.code')
            ->join('districts', 'villages.district_code', '=', 'districts.code')
            ->select('villages.name as village_name', 'districts.name as district_name')
            ->distinct()
            ->orderBy('villages.name')
            ->get();
        foreach ($villages as $v) {
            $suggestions[] = [
                'id'        => $v->village_name,
                'title'     => $v->village_name,
                'desc'      => "Kelurahan/Desa di {$v->district_name}",
                'icon'      => 'fa-solid fa-location-dot',
                'iconColor' => 'text-[#FFC000]',
                'bg'        => 'bg-[#FFC000]/10',
            ];
        }

        // 4. Alamat Spesifik (Address)
        $addresses = \App\Models\asset::where('status', 'approved')->whereNotNull('address')
            ->select('address')
            ->distinct()
            ->orderBy('address')
            ->get();
        foreach ($addresses as $a) {
            $suggestions[] = [
                'id'        => $a->address,
                'title'     => $a->address,
                'desc'      => "Alamat Lengkap",
                'icon'      => 'fa-solid fa-map-pin',
                'iconColor' => 'text-[#FFC000]',
                'bg'        => 'bg-[#FFC000]/10',
            ];
        }

        return $suggestions;
    }

    /**
     * Helper: bangun data riwayat & trending.
     */
    private function getSearchMeta(): array
    {
        $searchHistory = [];
        if (auth()->check()) {
            $searchHistory = auth()->user()->searchLogs()
                ->select('keyword')
                ->groupBy('keyword')
                ->orderByRaw('MAX(searched_at) DESC')
                ->limit(6)
                ->pluck('keyword')
                ->toArray();
        }

        $trending = search_log::select('keyword')
            ->where('searched_at', '>=', now()->subWeek())
            ->groupBy('keyword')
            ->orderByRaw('COUNT(*) DESC')
            ->limit(6)
            ->pluck('keyword')
            ->toArray();

        return compact('searchHistory', 'trending');
    }

    /**
     * Helper: hitung distribusi harga untuk histogram
     */
    private function getPriceDistribution($assetIds = null): array
    {
        $query = asset_pricing::orderBy('id');

        if ($assetIds !== null) {
            $query->whereIn('asset_id', $assetIds);
        } else {
            $query->whereIn('asset_id', asset::where('status', 'approved')->pluck('id'));
        }

        $prices = $query->get(['asset_id', 'price'])
            ->unique('asset_id')
            ->pluck('price');

        $histogramBuckets = 30;
        $histogramMax = 10000000;
        $bucketSize = $histogramMax / $histogramBuckets;
        $priceDistribution = array_fill(0, $histogramBuckets, 0);

        foreach ($prices as $price) {
            if ($price >= $histogramMax) {
                $priceDistribution[$histogramBuckets - 1]++;
            } else {
                $idx = floor($price / $bucketSize);
                if ($idx >= $histogramBuckets) $idx = $histogramBuckets - 1;
                $priceDistribution[$idx]++;
            }
        }

        return $priceDistribution;
    }

    /**
     * Helper: kumpulkan data untuk placeholder dinamis pencarian
     */
    private function getDynamicPlaceholders(): array
    {
        $placeholders = collect([]);

        // 1. Aset Populer
        $popular = asset::where('status', 'approved')
            ->withCount(['views', 'reviews'])
            ->orderByDesc('views_count')
            ->limit(5)
            ->pluck('title');
        $placeholders = $placeholders->merge($popular);

        if (auth()->check()) {
            $userId = auth()->id();

            // 2. Riwayat Pencarian User (Search Logs)
            $history = search_log::where('user_id', $userId)
                ->orderByDesc('searched_at')
                ->limit(3)
                ->pluck('keyword');
            $placeholders = $placeholders->merge($history);

            // 3. Aset Favorit User
            $favorites = asset::whereHas('favorites', fn($q) => $q->where('user_id', $userId))
                ->limit(3)
                ->pluck('title');
            $placeholders = $placeholders->merge($favorites);

            // 4. Aset yang di-booking User
            $bookings = asset::whereHas('bookings', fn($q) => $q->where('user_id', $userId))
                ->limit(3)
                ->pluck('title');
            $placeholders = $placeholders->merge($bookings);
            
            // 5. Aset yang di-review User
            $reviews = asset::whereHas('reviews', fn($q) => $q->where('reviews.user_id', $userId))
                ->limit(3)
                ->pluck('title');
            $placeholders = $placeholders->merge($reviews);
        }

        // Jika tidak ada data sama sekali, beri fallback
        if ($placeholders->isEmpty()) {
            return [
                "Rumah Asri Balikpapan",
                "Gudang Logistik Samarinda",
                "Apartemen Premium",
                "Ruang Kantor Strategis",
                "Lahan Kosong Siap Bangun"
            ];
        }

        // Shuffle dan kembalikan array unik
        return $placeholders->unique()->shuffle()->take(10)->values()->toArray();
    }

    /**
     * Halaman beranda utama.
     * Arsitektur baru: Category → Types → Assets.
     * Homepage menampilkan per CATEGORY (Hunian, Komersial, Lahan, Event, Media Iklan)
     * masing-masing berisi max 12 aset terbaru dari semua types di bawahnya.
     */
    public function index()
    {
        $sections = [];

        // 1. Dekat Lokasi Anda (Akan diisi dinamis via API Frontend)
        $sections[] = [
            'id'      => 'nearby',
            'title'   => 'Dekat Lokasi Anda',
            'icon'    => 'fa-solid fa-location-dot',
            'type'    => 'dynamic',
            'api_url' => route('api.home.nearby-assets'), // pastikan nama route ini ada
            'assets'  => []
        ];

        // Helper common queries & transformations
        $mapAsset = function ($asset) {
            $favorite = $asset->favorites->first();
            $asset->isFavorite = (bool) $favorite;
            $asset->favorite_id = $favorite?->id;
            unset($asset->favorites);
            
            // Map location names so cards display correctly
            $asset->city_name = $asset->city->name ?? '';
            $asset->district_name = $asset->district->name ?? '';
            $asset->province_name = $asset->province->name ?? '';
            
            return $asset;
        };

        // 2. Populer Minggu Ini
        $popularAssets = asset::where('status', 'approved')
            ->select(['id', 'slug', 'asset_type_id', 'owner_profile_id', 'title', 'city_code', 'district_code', 'address', 'status', 'detail'])
            ->withCount(['bookings', 'views', 'favorites', 'reviews'])
            ->withAvg('reviews as reviews_avg_rating', 'rating')
            ->orderByRaw('((IFNULL(bookings_count, 0) * 5) + (IFNULL(views_count, 0) * 1) + (IFNULL(favorites_count, 0) * 3) + (IFNULL(reviews_count, 0) * 2) + (IFNULL(reviews_avg_rating, 0) * 2)) DESC')
            ->limit(10)
            ->withCommonRelations()
            ->get()
            ->map($mapAsset);

        $sections[] = [
            'id'     => 'popular',
            'title'  => 'Populer Minggu Ini',
            'icon'   => 'fa-solid fa-fire',
            'type'   => 'static',
            'assets' => $popularAssets
        ];

        // 3. Terakhir Dilihat (Perlu login)
        if (auth()->check()) {
            $viewedAssetIds = AssetView::where('user_id', auth()->id())
                ->orderByDesc('last_viewed')
                ->limit(10)
                ->pluck('asset_id');

            if ($viewedAssetIds->isNotEmpty()) {
                $viewedAssets = asset::whereIn('id', $viewedAssetIds)
                    ->where('status', 'approved')
                    ->select(['id', 'slug', 'asset_type_id', 'owner_profile_id', 'title', 'city_code', 'district_code', 'address', 'status', 'detail'])
                    ->withCommonRelations()
                    ->withCount('reviews')
            ->withAvg('reviews as reviews_avg_rating', 'rating')
                    ->get();

                // Urutkan kembali sesuai dengan urutan id di array viewedAssetIds
                $sortedViewedAssets = $viewedAssets->sortBy(function($model) use ($viewedAssetIds) {
                    return array_search($model->id, $viewedAssetIds->toArray());
                })->values()->map($mapAsset);

                $sections[] = [
                    'id'     => 'recent',
                    'title'  => 'Terakhir Dilihat',
                    'icon'   => 'fa-solid fa-clock-rotate-left',
                    'type'   => 'static',
                    'assets' => $sortedViewedAssets
                ];
            }
        }

        // 4. Karena anda menyukai (Perlu login)
        if (auth()->check()) {
            $userId = auth()->id();
            
            $favTypes = favorite::where('favorites.user_id', $userId)
                ->join('assets', 'assets.id', '=', 'favorites.asset_id')
                ->join('asset_types', 'asset_types.id', '=', 'assets.asset_type_id')
                ->select('asset_types.id', 'asset_types.name', \DB::raw('count(*) as count'))
                ->groupBy('asset_types.id', 'asset_types.name')
                ->orderByDesc('count')
                ->limit(3)
                ->get();
                
            $favoritedAssetIds = favorite::where('user_id', $userId)->pluck('asset_id');

            foreach ($favTypes as $favType) {
                $recommendedAssets = asset::where('asset_type_id', $favType->id)
                    ->where('status', 'approved')
                    ->whereNotIn('id', $favoritedAssetIds)
                    ->select(['id', 'slug', 'asset_type_id', 'owner_profile_id', 'title', 'city_code', 'district_code', 'address', 'status', 'detail'])
                    ->orderByDesc('id')
                    ->withCommonRelations()
                    ->withCount('reviews')
                    ->withAvg('reviews as reviews_avg_rating', 'rating')
                    ->limit(10)
                    ->get()
                    ->map($mapAsset);

                if ($recommendedAssets->isNotEmpty()) {
                    $sections[] = [
                        'id'     => 'recommendation_' . $favType->id,
                        'title'  => 'Karena Anda Menyukai ' . $favType->name,
                        'icon'   => 'fa-solid fa-thumbs-up',
                        'type'   => 'static',
                        'assets' => $recommendedAssets
                    ];
                }
            }
        }

        // 5. Rating tertinggi
        $topRatedAssets = asset::where('status', 'approved')
            ->select(['id', 'slug', 'asset_type_id', 'owner_profile_id', 'title', 'city_code', 'district_code', 'address', 'status', 'detail'])
            ->withCount('reviews')
            ->withAvg('reviews as reviews_avg_rating', 'rating')
            ->havingRaw('reviews_avg_rating > 0')
            ->orderByDesc('reviews_avg_rating')
            ->limit(10)
            ->withCommonRelations()
            ->get()
            ->map($mapAsset);

        if ($topRatedAssets->isNotEmpty()) {
            $sections[] = [
                'id'     => 'top_rated',
                'title'  => 'Rating Tertinggi',
                'icon'   => 'fa-solid fa-star',
                'type'   => 'static',
                'assets' => $topRatedAssets
            ];
        }

        // 6. Kategori-kategori (List yang lama di bagian paling bawah)
        $categories = asset_category::select(['id', 'name', 'icon'])
            ->with(['types:id,category_id,name,allow_units,rental_unit'])
            ->whereHas('types.assets', fn($q) => $q->where('status', 'approved'))
            ->get();

        $categories->each(function ($category) use (&$sections, $mapAsset) {
            $typeIds = $category->types->pluck('id');

            $categoryAssets = asset::whereIn('asset_type_id', $typeIds)
                ->where('status', 'approved')
                ->select(['id', 'slug', 'asset_type_id', 'owner_profile_id', 'title', 'city_code', 'district_code', 'address', 'status', 'detail'])
                ->withCommonRelations()
                ->withCount('reviews')
            ->withAvg('reviews as reviews_avg_rating', 'rating')
                ->latest('id')
                ->limit(10)
                ->get()
                ->map($mapAsset);

            if ($categoryAssets->isNotEmpty()) {
                $sections[] = [
                    'id'     => 'category_' . $category->id,
                    'title'  => $category->name,
                    'icon'   => $category->icon,
                    'type'   => 'static',
                    'assets' => $categoryAssets
                ];
            }
        });


        $meta = $this->getSearchMeta();
        $locationSuggestions = $this->getLocationSuggestions();
        $facilitiesByType = $this->getFacilitiesByType();
        $priceDistribution = $this->getPriceDistribution(); // Global distribution
        $dynamicPlaceholders = $this->getDynamicPlaceholders();

        return inertia('Home/index', [
            'sections'            => $sections,
            'allCategories'       => asset_category::select(['id', 'name', 'icon'])
                                        ->with(['types:id,category_id,name,allow_units,rental_unit'])
                                        ->get(),
            'searchHistory'       => $meta['searchHistory'],
            'trending'            => $meta['trending'],
            'locationSuggestions' => $locationSuggestions,
            'facilitiesByType'    => $facilitiesByType,
            'priceDistribution'   => $priceDistribution,
            'dynamicPlaceholders' => $dynamicPlaceholders,
        ]);
    }

    /**
     * Halaman hasil pencarian / filter.
     */
    public function search(Request $request)
    {
        $keyword    = $request->input('q', '');
        $types      = $request->input('type', []);
        $location   = $request->input('location', '');
        $minPrice   = $request->input('min_price', 0);
        $maxPrice   = $request->input('max_price', 10000000);
        $dateStart  = $request->input('date_start', '');
        $dateEnd    = $request->input('date_end', '');
        $sort       = $request->input('sort', 'popular'); // popular, price_asc, price_desc

        $query = asset::where('status', 'approved')
            ->select([
                'id', 'asset_type_id', 'owner_profile_id',
                'title', 'slug', 'city_code', 'district_code', 'address', 'status', 'detail'
            ])
            ->with([
                'thumbnailImages' => fn($q) => $q->select(['id', 'asset_id', 'image'])->orderBy('id')->limit(3),
                'defaultPricing:id,asset_id,price',
                'type:id,name,allow_units,rental_unit,category_id',
                'type.category:id,name,icon',
                'city:code,name',
                'district:code,name',
                'province:code,name',
                'units' => fn($q) => $q->select(['id', 'asset_id', 'quantity', 'status'])->where('status', 'active'),
                'units.pricings' => fn($q) => $q->select(['id', 'asset_unit_id', 'price']),
                'favorites' => function ($q) {
                    $q->select(['id', 'user_id', 'asset_id'])
                        ->where('user_id', auth()->id());
                }
            ])
            ->withCount('reviews')
            ->withAvg('reviews as reviews_avg_rating', 'rating');

        // Filter keyword
        if ($keyword) {
            $query->where(function ($q) use ($keyword) {
                $q->where('title', 'like', "%{$keyword}%")
                  ->orWhereHas('city', function($q2) use ($keyword) { $q2->where('name', 'like', "%{$keyword}%"); })
                  ->orWhere('address', 'like', "%{$keyword}%");
            });
        }

        // Filter tipe aset
        if (!empty($types)) {
            $query->whereHas('type', fn($q) => $q->whereIn('name', $types));
        }

        // Filter lokasi (city, district, village, atau address)
        if ($location) {
            $query->where(function ($q) use ($location) {
                $q->whereHas('city', function($q2) use ($location) { $q2->where('name', 'like', "%{$location}%"); })
                  ->orWhereHas('district', function($q2) use ($location) { $q2->where('name', 'like', "%{$location}%"); })
                  ->orWhereHas('village', function($q2) use ($location) { $q2->where('name', 'like', "%{$location}%"); })
                  ->orWhere('address', 'like', "%{$location}%");
            });
        }

        // --- Calculate Price Distribution (before price filter) ---
        $assetIdsForHistogram = (clone $query)->pluck('assets.id');
        $priceDistribution = $this->getPriceDistribution($assetIdsForHistogram);

        // Filter harga
        if ($minPrice > 0 || $maxPrice < 10000000) {
            $query->whereHas('defaultPricing', fn($q) => $q->whereBetween('price', [$minPrice, $maxPrice]));
        }

        // Filter fasilitas (sistem baru: pivot asset_facilities)
        $facilities = request('facilities', []);
        if (!empty($facilities) && is_array($facilities)) {
            $query->whereHas('facilities', function ($q) use ($facilities) {
                $q->whereIn('facilities.id', $facilities);
            });
        }

        // Filter jadwal — exclude aset yang sudah dipesan pada rentang tsb
        if ($dateStart && $dateEnd) {
            $parsedDateStart = \Carbon\Carbon::parse($dateStart);
            $parsedDateEnd = \Carbon\Carbon::parse($dateEnd)->endOfDay();

            $query->whereDoesntHave('bookings', function ($q) use ($parsedDateStart, $parsedDateEnd) {
                $q->whereNotIn('booking_status', ['cancelled', 'rejected'])
                  ->where('start_date', '<', $parsedDateEnd)
                  ->where('end_date', '>', $parsedDateStart)
                  ->where(function ($q2) {
                      $q2->where('booking_status', '!=', 'pending')
                         ->orWhere(function ($q3) {
                             $q3->where('booking_status', 'pending')
                                ->whereHas('payment', function ($q4) {
                                    $q4->where('payment_status', 'pending')
                                       ->where('expires_at', '>', now());
                                });
                         });
                  });
            });
        } elseif ($dateStart) {
            // Hanya tanggal mulai dipilih — cek overlap di hari itu
            $parsedDateStart = \Carbon\Carbon::parse($dateStart);
            $parsedDateEnd = \Carbon\Carbon::parse($dateStart)->endOfDay();

            $query->whereDoesntHave('bookings', function ($q) use ($parsedDateStart, $parsedDateEnd) {
                $q->whereNotIn('booking_status', ['cancelled', 'rejected'])
                  ->where('start_date', '<', $parsedDateEnd)
                  ->where('end_date', '>', $parsedDateStart)
                  ->where(function ($q2) {
                      $q2->where('booking_status', '!=', 'pending')
                         ->orWhere(function ($q3) {
                             $q3->where('booking_status', 'pending')
                                ->whereHas('payment', function ($q4) {
                                    $q4->where('payment_status', 'pending')
                                       ->where('expires_at', '>', now());
                                });
                         });
                  });
            });
        }

        // Sorting
        if ($sort === 'price_asc') {
            $query->orderBy(
                asset_pricing::select('price')
                    ->whereColumn('asset_id', 'assets.id')
                    ->limit(1),
                'asc'
            );
        } elseif ($sort === 'price_desc') {
            $query->orderBy(
                asset_pricing::select('price')
                    ->whereColumn('asset_id', 'assets.id')
                    ->limit(1),
                'desc'
            );
        } else {
            // Default: popular (by rating / reviews_avg_rating)
            $query->orderByDesc('reviews_avg_rating')->orderByDesc('id');
        }

        $assets = $query->paginate(24)->withQueryString();

        $assets->getCollection()->transform(function ($asset) {
            $favorite = $asset->favorites->first();
            $asset->isFavorite = (bool) $favorite;
            $asset->favorite_id = $favorite?->id;
            unset($asset->favorites);
            
            $asset->city_name = $asset->city->name ?? '';
            $asset->district_name = $asset->district->name ?? '';
            $asset->province_name = $asset->province->name ?? '';
            
            if ($asset->type && $asset->type->allow_units && $asset->units && $asset->units->isNotEmpty()) {
                $minPrice = PHP_FLOAT_MAX;
                $cheapestUnitQty = 0;
                
                foreach($asset->units as $unit) {
                    if ($unit->pricings && $unit->pricings->isNotEmpty()) {
                        $unitPrice = $unit->pricings->min('price');
                        if ($unitPrice < $minPrice) {
                            $minPrice = $unitPrice;
                            $cheapestUnitQty = $unit->quantity;
                        }
                    }
                }
                
                if ($minPrice !== PHP_FLOAT_MAX) {
                    $asset->cheapest_unit_price = $minPrice;
                    $asset->cheapest_unit_quantity = $cheapestUnitQty;
                }
            }
            unset($asset->units);
            
            return $asset;
        });

        $meta = $this->getSearchMeta();
        $locationSuggestions = $this->getLocationSuggestions();

        $facilitiesByType = $this->getFacilitiesByType();
        $dynamicPlaceholders = $this->getDynamicPlaceholders();

        return inertia('Home/Assets/Index', [
            'assets'              => $assets,
            'filters'             => [
                'q'          => $keyword,
                'type'       => $types,
                'location'   => $location,
                'min_price'  => (int) $minPrice,
                'max_price'  => (int) $maxPrice,
                'date_start' => $dateStart,
                'date_end'   => $dateEnd,
                'facilities' => request('facilities', []),
                'sort'       => $sort,
            ],
            'categories'          => asset_category::select(['id', 'name', 'icon'])
                ->with(['types:id,category_id,name,allow_units,rental_unit'])
                ->get(),
            'allCategories'       => asset_category::select(['id', 'name', 'icon'])
                ->with(['types:id,category_id,name,allow_units,rental_unit'])
                ->get(),
            'allTypes'            => asset_type::select(['id', 'category_id', 'name', 'allow_units', 'rental_unit'])->get(),
            'facilitiesByType'    => $facilitiesByType, // fasilitas terstruktur per tipe
            'searchHistory'       => $meta['searchHistory'],
            'trending'            => $meta['trending'],
            'locationSuggestions' => $locationSuggestions,
            'priceDistribution'   => $priceDistribution,
            'dynamicPlaceholders' => $dynamicPlaceholders,
        ]);
    }

    /**
     * Simpan riwayat pencarian (auth only).
     */
    public function logSearch(Request $request)
    {
        $request->validate(['keyword' => 'required|string|max:255']);

        search_log::where('user_id', auth()->id())
            ->where('keyword', $request->keyword)
            ->delete();

        search_log::create([
            'user_id'     => auth()->id(),
            'keyword'     => $request->keyword,
            'searched_at' => now(),
        ]);

        return response()->json(['status' => 'ok']);
    }

    /**
     * Sugesti pencarian real-time.
     */
    public function suggest(Request $request)
    {
        $q = trim($request->input('q', ''));

        if (strlen($q) < 1) {
            return response()->json([]);
        }

        $suggestions = [];
        $seen = [];

        $addSuggestion = function ($text, $type, $icon) use (&$suggestions, &$seen) {
            $key = mb_strtolower(trim($text));
            if (!in_array($key, $seen) && $text) {
                $seen[] = $key;
                $suggestions[] = ['text' => $text, 'type' => $type, 'icon' => $icon];
            }
        };

        // 1. Dari nama kategori aset (database)
        asset_category::where('name', 'like', "%{$q}%")
            ->limit(3)
            ->pluck('name')
            ->each(fn($name) => $addSuggestion($name, 'category', 'fa-solid fa-layer-group'));

        // 2. Dari judul aset aktif
        asset::where('status', 'approved')
            ->where('title', 'like', "%{$q}%")
            ->select('title')
            ->distinct()
            ->limit(5)
            ->pluck('title')
            ->each(fn($title) => $addSuggestion($title, 'asset', 'fa-solid fa-building'));

        // 3. Dari kota aset aktif
        asset::where('status', 'approved')
            ->join('cities', 'assets.city_code', '=', 'cities.code')
            ->where('cities.name', 'like', "%{$q}%")
            ->whereNotNull('city_code')
            ->select('cities.name as city')
            ->distinct()
            ->limit(3)
            ->pluck('city')
            ->each(fn($city) => $addSuggestion($city, 'location', 'fa-solid fa-location-dot'));

        // 4. Riwayat user (login)
        if (auth()->check()) {
            search_log::where('user_id', auth()->id())
                ->where('keyword', 'like', "%{$q}%")
                ->select('keyword')
                ->groupBy('keyword')
                ->orderByRaw('MAX(searched_at) DESC')
                ->limit(3)
                ->pluck('keyword')
                ->each(fn($k) => $addSuggestion($k, 'history', 'fa-solid fa-clock-rotate-left'));
        }

        // 5. Keyword populer global
        search_log::where('keyword', 'like', "%{$q}%")
            ->where('searched_at', '>=', now()->subMonth())
            ->select('keyword')
            ->groupBy('keyword')
            ->orderByRaw('COUNT(*) DESC')
            ->limit(3)
            ->pluck('keyword')
            ->each(fn($k) => $addSuggestion($k, 'popular', 'fa-solid fa-fire'));

        return response()->json(array_slice($suggestions, 0, 8));
    }

    /**
     * Hapus semua riwayat pencarian user
     */
    public function clearSearchHistory()
    {
        if (auth()->check()) {
            search_log::where('user_id', auth()->id())->delete();
            return back()->with('success', 'Riwayat pencarian berhasil dihapus.');
        }
        return back();
    }

    /**
     * Hapus satu keyword dari riwayat pencarian
     */
    public function deleteSearchKeyword(Request $request)
    {
        if (auth()->check() && $request->has('keyword')) {
            search_log::where('user_id', auth()->id())
                ->where('keyword', $request->keyword)
                ->delete();
            return back()->with('success', 'Kata kunci berhasil dihapus.');
        }
        return back();
    }

    /**
     * Helper untuk mengambil data fasilitas terstruktur per tipe
     */
    private function getFacilitiesByType()
    {
        return Cache::remember('facilities_by_type_v2', 3600, function () {
            return asset_type::select(['id', 'name'])
                ->with([
                    'allowedFacilities' => fn($q) => $q
                        ->select(['facilities.id', 'facilities.name', 'facilities.facility_category_id'])
                        ->with('category:id,name')
                        ->where('asset_type_facilities.scope', 'asset')
                        ->orderBy('facilities.sort_order')
                ])
                ->whereHas('allowedFacilities')
                ->orderBy('name')
                ->get()
                ->map(fn($type) => [
                    'type_id'    => $type->id,
                    'type_name'  => $type->name,
                    'facilities' => $type->allowedFacilities->map(fn($f) => [
                        'id'            => $f->id,
                        'name'          => $f->name,
                        'category_name' => $f->category?->name,
                    ])->values()->toArray(),
                ])
                ->values()
                ->toArray();
        });
    }
}


