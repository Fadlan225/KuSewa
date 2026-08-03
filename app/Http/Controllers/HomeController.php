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
    private function getLocationSuggestions(?string $query = null): array
    {
        $q = asset::where('status', 'active')
            ->whereNotNull('city')
            ->where('city', '!=', '');

        if ($query) {
            $q->where('city', 'like', "%{$query}%");
        }

        return $q->select('city')
            ->distinct()
            ->orderBy('city')
            ->limit(10)
            ->pluck('city')
            ->map(fn($city) => [
                'id'        => $city,
                'title'     => $city,
                'desc'      => "Aset di {$city}",
                'icon'      => 'fa-solid fa-location-dot',
                'iconColor' => 'text-[#FFC000]',
                'bg'        => 'bg-[#FFC000]/10',
            ])
            ->toArray();
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
            $query->whereIn('asset_id', asset::where('status', 'active')->pluck('id'));
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
            return $asset;
        };

        // 2. Populer Minggu Ini
        $popularAssets = asset::where('status', 'active')
            ->select(['id', 'slug', 'asset_type_id', 'owner_profile_id', 'title', 'city', 'subdistrict', 'address', 'status', 'detail'])
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
                    ->where('status', 'active')
                    ->select(['id', 'slug', 'asset_type_id', 'owner_profile_id', 'title', 'city', 'subdistrict', 'address', 'status', 'detail'])
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
            $favTypes = favorite::where('favorites.user_id', auth()->id())
                ->join('assets', 'assets.id', '=', 'favorites.asset_id')
                ->join('asset_types', 'asset_types.id', '=', 'assets.asset_type_id')
                ->select('asset_types.id', 'asset_types.name', \DB::raw('count(*) as count'))
                ->groupBy('asset_types.id', 'asset_types.name')
                ->orderByDesc('count')
                ->limit(3)
                ->get();

            foreach ($favTypes as $favType) {
                $recommendedAssets = asset::where('asset_type_id', $favType->id)
                    ->where('status', 'active')
                    ->select(['id', 'slug', 'asset_type_id', 'owner_profile_id', 'title', 'city', 'subdistrict', 'address', 'status', 'detail'])
                    ->orderByDesc('id')
                    ->withCommonRelations()
                    ->withCount('reviews')
            ->withAvg('reviews as reviews_avg_rating', 'rating')
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
        $topRatedAssets = asset::where('status', 'active')
            ->select(['id', 'slug', 'asset_type_id', 'owner_profile_id', 'title', 'city', 'subdistrict', 'address', 'status', 'detail'])
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
            ->whereHas('types.assets', fn($q) => $q->where('status', 'active'))
            ->get();

        $categories->each(function ($category) use (&$sections, $mapAsset) {
            $typeIds = $category->types->pluck('id');

            $categoryAssets = asset::whereIn('asset_type_id', $typeIds)
                ->where('status', 'active')
                ->select(['id', 'slug', 'asset_type_id', 'owner_profile_id', 'title', 'city', 'subdistrict', 'address', 'status', 'detail'])
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

        $query = asset::where('status', 'active')
            ->select([
                'id', 'asset_type_id', 'owner_profile_id',
                'title', 'slug', 'city', 'subdistrict', 'address', 'status', 'detail'
            ])
            ->with([
                'thumbnailImages' => fn($q) => $q->select(['id', 'asset_id', 'image'])->orderBy('id')->limit(3),
                'defaultPricing:id,asset_id,price',
                'type:id,name,allow_units,rental_unit,category_id',
                'type.category:id,name,icon',
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
                  ->orWhere('city', 'like', "%{$keyword}%")
                  ->orWhere('address', 'like', "%{$keyword}%");
            });
        }

        // Filter tipe aset
        if (!empty($types)) {
            $query->whereHas('type', fn($q) => $q->whereIn('name', $types));
        }

        // Filter lokasi (city atau address)
        if ($location) {
            $query->where(function ($q) use ($location) {
                $q->where('city', 'like', "%{$location}%")
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
            return $asset;
        });

        $meta = $this->getSearchMeta();
        $locationQuery = null; // Always fetch default suggestions for the dropdown
        $locationSuggestions = $this->getLocationSuggestions($locationQuery);

        $facilitiesByType = $this->getFacilitiesByType();

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
        asset::where('status', 'active')
            ->where('title', 'like', "%{$q}%")
            ->select('title')
            ->distinct()
            ->limit(5)
            ->pluck('title')
            ->each(fn($title) => $addSuggestion($title, 'asset', 'fa-solid fa-building'));

        // 3. Dari kota aset aktif
        asset::where('status', 'active')
            ->where('city', 'like', "%{$q}%")
            ->whereNotNull('city')
            ->select('city')
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


