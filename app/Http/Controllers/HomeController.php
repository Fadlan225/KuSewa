<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\Models\asset_category;
use App\Models\asset_type;
use App\Models\asset;
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
     * Halaman beranda utama.
     * Arsitektur baru: Category → Types → Assets.
     * Homepage menampilkan per CATEGORY (Hunian, Komersial, Lahan, Event, Media Iklan)
     * masing-masing berisi max 12 aset terbaru dari semua types di bawahnya.
     */
    public function index()
    {
        // Ambil semua kategori beserta type_ids di bawahnya
        $categories = asset_category::select(['id', 'name', 'icon'])
            ->with(['types:id,category_id,name,allow_units,rental_unit'])
            ->whereHas('types.assets', fn($q) => $q->where('status', 'active'))
            ->get();

        // Untuk setiap kategori, load aset dari semua typenya (max 12)
        $categories->each(function ($category) {
            $typeIds = $category->types->pluck('id');

            $category->assets = asset::whereIn('asset_type_id', $typeIds)
                ->where('status', 'active')
                ->select(['id', 'asset_type_id', 'owner_profile_id', 'title', 'city', 'address', 'status'])
                ->with([
                    'thumbnailImages' => fn($q) => $q->select(['id', 'asset_id', 'image'])->orderBy('id')->limit(3),
                    'defaultPricing:id,asset_id,price',
                    'type:id,name,allow_units,rental_unit,category_id',
                    'favorites' => function ($q) {
                        $q->select(['id', 'user_id', 'asset_id'])
                          ->where('user_id', auth()->id());
                    }
                ])
                ->withAvg('reviews as reviews_avg_rating', 'rating')
                ->latest('id')
                ->limit(12)
                ->get()
                ->each(function ($asset) {
                    $favorite = $asset->favorites->first();
                    $asset->isFavorite  = (bool) $favorite;
                    $asset->favorite_id = $favorite?->id;
                    unset($asset->favorites);
                });
        })
        ->filter(fn($cat) => $cat->assets->isNotEmpty())
        ->values();

        $meta = $this->getSearchMeta();
        $locationSuggestions = $this->getLocationSuggestions();

        return inertia('Home/index', [
            'categories'          => $categories,
            'searchHistory'       => $meta['searchHistory'],
            'trending'            => $meta['trending'],
            'locationSuggestions' => $locationSuggestions,
        ]);
    }

    /**
     * Halaman hasil pencarian / filter.
     */
    public function search(Request $request)
    {
        $keyword    = $request->input('q', '');
        $categories = $request->input('category', []);
        $location   = $request->input('location', '');
        $minPrice   = $request->input('min_price', 0);
        $maxPrice   = $request->input('max_price', 10000000);
        $dateStart  = $request->input('date_start', '');
        $dateEnd    = $request->input('date_end', '');
        $sort       = $request->input('sort', 'popular'); // popular, price_asc, price_desc

        $query = asset::where('status', 'active')
            ->select([
                'id', 'asset_type_id', 'owner_profile_id',
                'title', 'city', 'address', 'status'
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
            ->withAvg('reviews as reviews_avg_rating', 'rating');

        // Filter keyword
        if ($keyword) {
            $query->where(function ($q) use ($keyword) {
                $q->where('title', 'like', "%{$keyword}%")
                  ->orWhere('city', 'like', "%{$keyword}%")
                  ->orWhere('address', 'like', "%{$keyword}%");
            });
        }

        // Filter kategori (by category name, melalui type → category)
        if (!empty($categories)) {
            $query->whereHas('type.category', fn($q) => $q->whereIn('name', $categories));
        }

        // Filter lokasi (city atau address)
        if ($location) {
            $query->where(function ($q) use ($location) {
                $q->where('city', 'like', "%{$location}%")
                  ->orWhere('address', 'like', "%{$location}%");
            });
        }

        // Filter harga
        if ($minPrice > 0 || $maxPrice < 10000000) {
            $query->whereHas('defaultPricing', fn($q) => $q->whereBetween('price', [$minPrice, $maxPrice]));
        }

        // Filter fasilitas
        $facilities = request('facilities', []);
        if (!empty($facilities) && is_array($facilities)) {
            $query->where(function ($q) use ($facilities) {
                foreach ($facilities as $facility) {
                    $q->whereJsonContains('detail->facility', $facility);
                }
            });
        }

        // Filter jadwal — exclude aset yang sudah dipesan (pending/accepted) pada rentang tsb
        if ($dateStart && $dateEnd) {
            $query->whereDoesntHave('bookings', function ($q) use ($dateStart, $dateEnd) {
                $q->whereIn('booking_status', ['pending', 'accepted'])
                  ->where('start_date', '<=', $dateEnd)
                  ->where('end_date', '>=', $dateStart);
            });
        } elseif ($dateStart) {
            // Hanya tanggal mulai dipilih — cek overlap di hari itu
            $query->whereDoesntHave('bookings', function ($q) use ($dateStart) {
                $q->whereIn('booking_status', ['pending', 'accepted'])
                  ->where('start_date', '<=', $dateStart)
                  ->where('end_date', '>=', $dateStart);
            });
        }

        // Sorting
        if ($sort === 'price_asc') {
            $query->orderBy(
                \App\Models\asset_pricing::select('price')
                    ->whereColumn('asset_id', 'assets.id')
                    ->limit(1),
                'asc'
            );
        } elseif ($sort === 'price_desc') {
            $query->orderBy(
                \App\Models\asset_pricing::select('price')
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
        $locationQuery = $location ?: null;
        $locationSuggestions = $this->getLocationSuggestions($locationQuery);

        // Ambil semua fasilitas unik - di-cache 60 menit agar tidak query ulang tiap request
        // Menggunakan JSON_EACH di SQLite / JSON_TABLE di MySQL untuk ekstraksi di DB level
        $allFacilities = Cache::remember('asset_facilities', 3600, function () {
            return asset::where('status', 'active')
                ->whereNotNull('detail')
                ->pluck('detail')
                ->flatMap(fn($d) => $d['facility'] ?? [])
                ->unique()
                ->filter()
                ->values()
                ->toArray();
        });

        return inertia('Home/Assets/Index', [
            'assets'              => $assets,
            'filters'             => [
                'q'          => $keyword,
                'category'   => $categories,
                'location'   => $location,
                'min_price'  => (int) $minPrice,
                'max_price'  => (int) $maxPrice,
                'date_start' => $dateStart,
                'date_end'   => $dateEnd,
                'facilities' => request('facilities', []),
                'sort'       => $sort,
            ],
            'categories'          => asset_category::select(['id', 'name', 'icon'])->get(),
            'allTypes'            => asset_type::select(['id', 'category_id', 'name', 'allow_units'])->get(),
            'facilities'          => $allFacilities,
            'searchHistory'       => $meta['searchHistory'],
            'trending'            => $meta['trending'],
            'locationSuggestions' => $locationSuggestions,
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
}
