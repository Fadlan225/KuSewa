<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;
use App\Models\asset;
use App\Models\booking;
use App\Models\asset_units;
use App\Models\AssetView;

class DashboardController extends Controller
{
    /**
     * Tampilkan halaman utama Dashboard Owner.
     */
    public function index(Request $request)
    {
        Carbon::setLocale('id');
        $user = $request->user();

        // Pastikan user memiliki owner profile dan rekening bank
        $ownerProfileId = $user->ownerProfile ? $user->ownerProfile->id : null;
        $hasBankAccount = $user->ownerProfile ? $user->ownerProfile->bankAccounts()->exists() : false;
        $isProfileComplete = $ownerProfileId && $hasBankAccount;

        // Jika belum ada profile, return 0 untuk semuanya
        if (!$ownerProfileId) {
            return Inertia::render('owner/index', [
                'stats' => $this->getEmptyStats(),
                'isProfileComplete' => $isProfileComplete
            ]);
        }

        // Ambil ID semua aset milik owner ini (atau aset tertentu jika active_asset_slug ada)
        $activeAssetSlug = $request->session()->get('active_asset_slug');
        $assetQuery = asset::where('owner_profile_id', $ownerProfileId);
        
        if ($activeAssetSlug) {
            $assetQuery->where(function($q) use ($activeAssetSlug) {
                $q->where('slug', $activeAssetSlug)->orWhere('id', $activeAssetSlug);
            });
        }
        
        $assetIds = $assetQuery->pluck('id');


        // Total Unit
        // Jika aset tidak memiliki unit (kuantitas = 0), maka aset tersebut dihitung sebagai 1 unit.
        $totalUnit = 0;
        $totalInactive = 0;
        $totalPending = 0;
        $totalRejected = 0;

        $assetsWithUnits = asset::whereIn('id', $assetIds)->get();
        foreach ($assetsWithUnits as $asset) {
            $unitQuantity = asset_units::where('asset_id', $asset->id)->sum('quantity');
            $inactiveUnits = asset_units::where('asset_id', $asset->id)->where('status', 'inactive')->sum('quantity');

            $effectiveQuantity = $unitQuantity > 0 ? $unitQuantity : 1;
            $totalUnit += $effectiveQuantity;

            if ($asset->status === 'pending') {
                $totalPending += $effectiveQuantity;
            } elseif ($asset->status === 'rejected') {
                $totalRejected += $effectiveQuantity;
            } elseif ($asset->status === 'inactive') {
                $totalInactive += $effectiveQuantity;
            } else { // approved
                if ($unitQuantity > 0) {
                    $totalInactive += $inactiveUnits;
                }
            }
        }

        // Total Unit Tersewa (asumsi: booking tidak dibatalkan/ditolak, dan masa sewa meliputi hari ini)
        $today = Carbon::today();
        $totalTersewa = booking::whereIn('asset_id', $assetIds)
            ->whereNotIn('booking_status', ['cancelled', 'rejected'])
            ->whereDate('start_date', '<=', $today)
            ->whereDate('end_date', '>=', $today)
            ->count();

        $tingkatKeterisian = $totalUnit > 0 ? round(($totalTersewa / $totalUnit) * 100) : 0;

        // Waktu Bulan Ini
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();

        // Booking Bulan Ini (semua status)
        $bookingBulanIni = booking::whereIn('asset_id', $assetIds)
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->count();

        // Booking Baru Hari Ini
        $bookingBaruHariIni = booking::whereIn('asset_id', $assetIds)
            ->whereDate('created_at', $today)
            ->count();

        // 1. Pesanan Baru (Perlu Konfirmasi)
        $pesananBaru = booking::whereIn('asset_id', $assetIds)
            ->where('booking_status', 'pending')
            ->count();

        // 2. Penyewa Aktif (Sama dengan booking berjalan saat ini)
        $penyewaAktif = $totalTersewa;

        // 3. Aset Tayang (Live)
        $asetTayang = $assetsWithUnits->where('status', 'approved')->count();

        // 4. Total Kunjungan (Menghitung dari tabel asset_views)
        $totalKunjungan = AssetView::whereIn('asset_id', $assetIds)->sum('view_count');

        // Pendapatan Bulan Ini
        $pendapatanBulanIni = booking::whereIn('asset_id', $assetIds)
            ->where('booking_status', 'completed')
            ->whereBetween('updated_at', [$startOfMonth, $endOfMonth])
            ->sum('total');

        // Pendapatan Bulan Lalu
        $startOfLastMonth = Carbon::now()->subMonth()->startOfMonth();
        $endOfLastMonth = Carbon::now()->subMonth()->endOfMonth();

        $pendapatanBulanLalu = booking::whereIn('asset_id', $assetIds)
            ->where('booking_status', 'completed')
            ->whereBetween('updated_at', [$startOfLastMonth, $endOfLastMonth])
            ->sum('total');

        // Optimasi: Ambil semua data transaksi 1 tahun terakhir dalam 1 query!
        $oneYearAgo = Carbon::now()->subMonths(11)->startOfMonth();
        $allBookings = booking::whereIn('asset_id', $assetIds)
            ->where('booking_status', 'completed')
            ->where('updated_at', '>=', $oneYearAgo)
            ->get();

        // Helper function untuk agregasi data di memori
        $aggregateData = function ($count, $format, $isMonthly = false) use ($allBookings) {
            $data = [];
            for ($i = $count - 1; $i >= 0; $i--) {
                if ($isMonthly) {
                    $date = Carbon::now()->subMonths($i);
                    $start = $date->copy()->startOfMonth();
                    $end = $date->copy()->endOfMonth();
                } else {
                    $date = Carbon::now()->subDays($i);
                    $start = $date->copy()->startOfDay();
                    $end = $date->copy()->endOfDay();
                }

                $filtered = $allBookings->filter(function ($booking) use ($start, $end) {
                    return $booking->updated_at >= $start && $booking->updated_at <= $end;
                });

                $income = $filtered->sum('total');
                $booking_count = $filtered->count();

                $data[] = [
                    'label' => $date->translatedFormat($format),
                    'date' => $start->format('Y-m-d'),
                    'income' => (int) $income,
                    'booking_count' => $booking_count,
                    'average_booking' => $booking_count > 0 ? (int) round($income / $booking_count) : 0
                ];
            }
            return $data;
        };

        $chartData = [
            '7days' => $aggregateData(7, 'd M'),
            '30days' => $aggregateData(30, 'd M'),
            '90days' => $aggregateData(90, 'd M'),
            '1year' => $aggregateData(12, 'M Y', true)
        ];

        // Persebaran Aset per Kota
        $kotaData = asset::where('owner_profile_id', $ownerProfileId)
            ->join('cities', 'assets.city_code', '=', 'cities.code')
            ->selectRaw('cities.name as name, count(*) as count')
            ->groupBy('cities.name')
            ->get()
            ->keyBy('name')
            ->map(function ($item) {
                return $item->count;
            })
            ->toArray();

        $statusAssetData = [
            ['name' => 'Aktif', 'value' => $assetsWithUnits->where('status', 'approved')->count()],
            ['name' => 'Menunggu Validasi', 'value' => $assetsWithUnits->where('status', 'pending')->count()],
            ['name' => 'Draf', 'value' => $assetsWithUnits->where('status', 'draft')->count()],
            ['name' => 'Nonaktif', 'value' => $assetsWithUnits->whereIn('status', ['inactive', 'rejected'])->count()],
        ];

        $stats = [
            'totalUnit' => $totalUnit,
            'totalTersewa' => $totalTersewa,
            'totalInactive' => $totalInactive,
            'tingkatKeterisian' => $tingkatKeterisian,
            'bookingBulanIni' => $bookingBulanIni,
            'bookingBaruHariIni' => $bookingBaruHariIni,
            'pendapatanBulanIni' => $pendapatanBulanIni,
            'pendapatanBulanLalu' => $pendapatanBulanLalu,
            'pesananBaru' => $pesananBaru,
            'penyewaAktif' => $penyewaAktif,
            'asetTayang' => $asetTayang,
            'totalKunjungan' => $totalKunjungan,
            'chartData' => $chartData,
            'kotaData' => $kotaData,
            'statusAssetData' => $statusAssetData,
        ];

        // Jika mode single asset, ambil data spesifik aset (seperti di AssetController@show)
        $singleAssetData = null;
        if ($activeAssetSlug) {
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
                    $q->where('start_date', '<=', now()->format('Y-m-d'))
                      ->where('end_date', '>=', now()->format('Y-m-d'))
                      ->whereNotIn('booking_status', ['cancelled', 'rejected']);
                },
                'bookings' => function($q) {
                    $q->where('start_date', '<=', now()->format('Y-m-d'))
                      ->where('end_date', '>=', now()->format('Y-m-d'))
                      ->whereNotIn('booking_status', ['cancelled', 'rejected'])
                      ->whereNull('asset_unit_id');
                }
            ])
            ->withAvg('reviews', 'rating')
            ->withCount([
                'reviews',
                'favorites'
            ])
            ->withSum('views', 'view_count')
            ->where('owner_profile_id', $ownerProfileId)
            ->where(function($query) use ($activeAssetSlug) {
                $query->where('id', $activeAssetSlug)->orWhere('slug', $activeAssetSlug);
            })
            ->first();

            if ($asset) {
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
                $asset->total_views = $asset->views_sum_view_count ?? 0;
                $asset->owner_occupancy = $hasUnits ? "{$occupiedUnits}/{$totalUnits} Unit" : ($status === 'Tersewa' ? '1/1 Unit' : '0/1 Unit');

                // Data collection for the last 24 months (to calculate percentage for 1 year)
                $twoYearsAgo = Carbon::now()->subMonths(23)->startOfMonth();

                $viewsData = \App\Models\AssetView::where('asset_id', $asset->id)
                    ->where('updated_at', '>=', $twoYearsAgo)
                    ->get();

                $favoritesData = \App\Models\favorite::where('asset_id', $asset->id)
                    ->where('created_at', '>=', $twoYearsAgo)
                    ->get();

                $chatsData = \App\Models\room_chat::where('asset_id', $asset->id)
                    ->where('created_at', '>=', $twoYearsAgo)
                    ->get();

                $buildChartSeries = function($dataCollection, $period, $groupType, $dateField = 'created_at', $valueField = null) {
                    $series = [];
                    $currentTotal = 0;
                    
                    if ($groupType === 'month') {
                        $startOfCurrent = Carbon::now()->subMonths($period - 1)->startOfMonth();
                        $startOfPrevious = Carbon::now()->subMonths(($period * 2) - 1)->startOfMonth();
                    } elseif ($groupType === 'week') {
                        $startOfCurrent = Carbon::now()->subDays(($period * 7) - 1)->startOfDay();
                        $startOfPrevious = Carbon::now()->subDays(($period * 2 * 7) - 1)->startOfDay();
                    } else { // day
                        $startOfCurrent = Carbon::now()->subDays($period - 1)->startOfDay();
                        $startOfPrevious = Carbon::now()->subDays(($period * 2) - 1)->startOfDay();
                    }
                    $endOfPrevious = $startOfCurrent->copy()->subSecond();

                    $previousTotal = $dataCollection->filter(function($item) use ($startOfPrevious, $endOfPrevious, $dateField) {
                        return $item->$dateField >= $startOfPrevious && $item->$dateField <= $endOfPrevious;
                    })->sum(function($item) use ($valueField) {
                        return $valueField ? $item->$valueField : 1;
                    });

                    for ($i = $period - 1; $i >= 0; $i--) {
                        if ($groupType === 'month') {
                            $date = Carbon::now()->subMonths($i);
                            $start = $date->copy()->startOfMonth();
                            $end = $date->copy()->endOfMonth();
                            $label = $date->translatedFormat('M Y');
                            $fullName = $date->translatedFormat('F Y');
                        } elseif ($groupType === 'week') {
                            $endDayOffset = $i * 7; 
                            $startDayOffset = ($i * 7) + 6;
                            
                            $start = Carbon::now()->subDays($startDayOffset)->startOfDay();
                            $end = Carbon::now()->subDays($endDayOffset)->endOfDay();
                            
                            $label = $start->translatedFormat('d M');
                            $fullName = $start->translatedFormat('d M') . ' - ' . $end->translatedFormat('d M');
                        } else { // day
                            $date = Carbon::now()->subDays($i);
                            $start = $date->copy()->startOfDay();
                            $end = $date->copy()->endOfDay();
                            $label = $date->translatedFormat('d M');
                            $fullName = $date->translatedFormat('l, d M Y');
                        }

                        $filtered = $dataCollection->filter(function ($item) use ($start, $end, $dateField) {
                            return $item->$dateField >= $start && $item->$dateField <= $end;
                        });

                        $val = $valueField ? $filtered->sum($valueField) : $filtered->count();
                        $currentTotal += $val;
                        
                        $series[] = [
                            'name' => $label,
                            'full_name' => $fullName,
                            'value' => (int) $val
                        ];
                    }
                    
                    $percentage = 0;
                    if ($previousTotal == 0 && $currentTotal > 0) {
                        $percentage = 100;
                    } elseif ($previousTotal > 0) {
                        $percentage = round((($currentTotal - $previousTotal) / $previousTotal) * 100);
                    }
                    
                    return [
                        'series' => $series,
                        'current_total' => $currentTotal,
                        'percentage' => $percentage
                    ];
                };

                $chartDataSingle = [
                    'views' => [
                        '7days' => $buildChartSeries($viewsData, 7, 'day', 'updated_at', 'view_count'),
                        '30days' => $buildChartSeries($viewsData, 4, 'week', 'updated_at', 'view_count'),
                        '90days' => $buildChartSeries($viewsData, 12, 'week', 'updated_at', 'view_count'),
                        '1year' => $buildChartSeries($viewsData, 12, 'month', 'updated_at', 'view_count')
                    ],
                    'favorites' => [
                        '7days' => $buildChartSeries($favoritesData, 7, 'day', 'created_at', null),
                        '30days' => $buildChartSeries($favoritesData, 4, 'week', 'created_at', null),
                        '90days' => $buildChartSeries($favoritesData, 12, 'week', 'created_at', null),
                        '1year' => $buildChartSeries($favoritesData, 12, 'month', 'created_at', null)
                    ],
                    'chats' => [
                        '7days' => $buildChartSeries($chatsData, 7, 'day', 'created_at', null),
                        '30days' => $buildChartSeries($chatsData, 4, 'week', 'created_at', null),
                        '90days' => $buildChartSeries($chatsData, 12, 'week', 'created_at', null),
                        '1year' => $buildChartSeries($chatsData, 12, 'month', 'created_at', null)
                    ]
                ];

                // Generate Rating Distribution
                $ratingDistribution = \App\Models\Review::whereHas('booking', function ($q) use ($asset) {
                    $q->where('asset_id', $asset->id);
                })
                ->selectRaw('rating, COUNT(*) as count')
                ->groupBy('rating')
                ->pluck('count', 'rating')
                ->toArray();

                $formattedRatingDistribution = [];
                $totalReviews = array_sum($ratingDistribution);
                for ($star = 5; $star >= 1; $star--) {
                    $count = $ratingDistribution[$star] ?? 0;
                    $formattedRatingDistribution[] = [
                        'star' => $star,
                        'count' => $count,
                        'percentage' => $totalReviews > 0 ? round(($count / $totalReviews) * 100) : 0
                    ];
                }

                // Generate OS & Browser Distribution
                $osDistribution = \App\Models\AssetViewLog::where('asset_id', $asset->id)
                    ->selectRaw('os as name, COUNT(*) as count')
                    ->groupBy('os')
                    ->orderByDesc('count')
                    ->get();

                $browserDistribution = \App\Models\AssetViewLog::where('asset_id', $asset->id)
                    ->selectRaw('browser as name, COUNT(*) as count')
                    ->groupBy('browser')
                    ->orderByDesc('count')
                    ->get();

                $singleAssetData = [
                    'asset' => $asset,
                    'chartData' => $chartDataSingle,
                    'ratingDistribution' => $formattedRatingDistribution,
                    'totalUnitsCount' => $totalUnits,
                    'occupiedUnitsCount' => $occupiedUnits,
                    'osDistribution' => $osDistribution,
                    'browserDistribution' => $browserDistribution,
                ];
            }
        }

        return Inertia::render('owner/index', [
            'stats' => $stats,
            'isProfileComplete' => $isProfileComplete,
            'isGlobal' => empty($activeAssetSlug),
            'singleAssetData' => $singleAssetData
        ]);
    }

    private function getEmptyStats()
    {
        return [
            'totalUnit' => 0,
            'totalTersewa' => 0,
            'totalInactive' => 0,
            'tingkatKeterisian' => 0,
            'bookingBulanIni' => 0,
            'bookingBaruHariIni' => 0,
            'pendapatanBulanIni' => 0,
            'pendapatanBulanLalu' => 0,
            'pesananBaru' => 0,
            'penyewaAktif' => 0,
            'asetTayang' => 0,
            'totalKunjungan' => 0,
            'chartData' => [
                '7days' => [],
                '30days' => [],
                '90days' => [],
                '1year' => [],
            ],
            'kotaData' => [],
            'statusAssetData' => [
                ['name' => 'Aktif', 'value' => 0],
                ['name' => 'Menunggu Validasi', 'value' => 0],
                ['name' => 'Draf', 'value' => 0],
                ['name' => 'Nonaktif', 'value' => 0],
            ],
        ];
    }
}
