<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;
use App\Models\asset;
use App\Models\booking;
use App\Models\asset_units;

class DashboardController extends Controller
{
    /**
     * Tampilkan halaman utama Dashboard Owner.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        // Pastikan user memiliki owner profile
        $ownerProfileId = $user->ownerProfile ? $user->ownerProfile->id : null;

        // Jika belum ada profile, return 0 untuk semuanya
        if (!$ownerProfileId) {
            return Inertia::render('owner/index', [
                'stats' => $this->getEmptyStats()
            ]);
        }

        // Ambil ID semua aset milik owner ini
        $assetIds = asset::where('owner_profile_id', $ownerProfileId)->pluck('id');


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

        // Total Unit Tersewa (asumsi: booking aktif hari ini)
        $today = Carbon::today();
        $totalTersewa = booking::whereIn('asset_id', $assetIds)
            ->where('booking_status', 'active') // Sesuaikan dengan status booking berjalan
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

        $statusUnitData = [
            ['name' => 'Siap Disewakan', 'value' => max(0, $totalUnit - $totalTersewa - $totalInactive - $totalPending - $totalRejected), 'fill' => '#10B981'], // Emerald
            ['name' => 'Disewa', 'value' => $totalTersewa, 'fill' => '#F59E0B'], // Amber
            ['name' => 'Menunggu Verifikasi', 'value' => $totalPending, 'fill' => '#3B82F6'], // Blue
            ['name' => 'Ditolak', 'value' => $totalRejected, 'fill' => '#EF4444'], // Red
            ['name' => 'Tidak Aktif', 'value' => $totalInactive, 'fill' => '#94A3B8'], // Slate/Gray
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
            'chartData' => $chartData,
            'kotaData' => $kotaData,
            'statusUnitData' => $statusUnitData,
        ];

        return Inertia::render('owner/index', [
            'stats' => $stats,
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
            'chartData' => [
                '7days' => [],
                '30days' => [],
                '90days' => [],
                '1year' => [],
            ],
            'kotaData' => [],
            'statusUnitData' => [
                ['name' => 'Siap Disewakan', 'value' => 0, 'fill' => 'var(--color-available)'],
                ['name' => 'Disewa', 'value' => 0, 'fill' => 'var(--color-booked)'],
                ['name' => 'Menunggu Verifikasi', 'value' => 0, 'fill' => '#F59E0B'],
                ['name' => 'Ditolak', 'value' => 0, 'fill' => '#EF4444'],
                ['name' => 'Tidak Aktif', 'value' => 0, 'fill' => 'var(--color-inactive)'],
            ],
        ];
    }
}
