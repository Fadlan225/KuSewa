<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\booking;
use App\Models\asset;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class IncomeController extends Controller
{
    public function index(Request $request)
    {
        $ownerProfileId = auth()->user()->ownerProfile->id;
        $period = $request->query('period', 'bulan_ini');

        // Define date range based on selected period
        $now = Carbon::now();
        $startDate = $now->copy();
        $endDate = $now->copy();
        $prevStartDate = $now->copy();
        $prevEndDate = $now->copy();

        switch ($period) {
            case 'hari_ini':
                $startDate->startOfDay();
                $endDate->endOfDay();
                $prevStartDate->subDay()->startOfDay();
                $prevEndDate->subDay()->endOfDay();
                $groupBy = 'hour';
                break;
            case '7_hari':
                $startDate->subDays(6)->startOfDay();
                $endDate->endOfDay();
                $prevStartDate->subDays(13)->startOfDay();
                $prevEndDate->subDays(7)->endOfDay();
                $groupBy = 'day';
                break;
            case 'bulan_ini':
                $startDate->startOfMonth();
                $endDate->endOfMonth();
                $prevStartDate->subMonth()->startOfMonth();
                $prevEndDate->subMonth()->endOfMonth();
                $groupBy = 'day';
                break;
            case 'bulan_lalu':
                $startDate->subMonth()->startOfMonth();
                $endDate->subMonth()->endOfMonth();
                $prevStartDate->subMonths(2)->startOfMonth();
                $prevEndDate->subMonths(2)->endOfMonth();
                $groupBy = 'day';
                break;
            case '3_bulan':
                $startDate->subMonths(2)->startOfMonth();
                $endDate->endOfMonth();
                $prevStartDate->subMonths(5)->startOfMonth();
                $prevEndDate->subMonths(3)->endOfMonth();
                $groupBy = 'month';
                break;
            case 'tahun_ini':
                $startDate->startOfYear();
                $endDate->endOfYear();
                $prevStartDate->subYear()->startOfYear();
                $prevEndDate->subYear()->endOfYear();
                $groupBy = 'month';
                break;
            default:
                $startDate->startOfMonth();
                $endDate->endOfMonth();
                $prevStartDate->subMonth()->startOfMonth();
                $prevEndDate->subMonth()->endOfMonth();
                $groupBy = 'day';
                break;
        }

        $validStatuses = ['confirmed', 'active', 'completed'];

        // Base query for current period
        $currentBookings = booking::whereHas('asset', function($q) use ($ownerProfileId) {
                $q->where('owner_profile_id', $ownerProfileId);
            })
            ->whereIn('booking_status', $validStatuses)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();

        // Base query for previous period (for growth comparison)
        $previousBookings = booking::whereHas('asset', function($q) use ($ownerProfileId) {
                $q->where('owner_profile_id', $ownerProfileId);
            })
            ->whereIn('booking_status', $validStatuses)
            ->whereBetween('created_at', [$prevStartDate, $prevEndDate])
            ->get();

        $currentIncome = $currentBookings->sum('subtotal');
        $previousIncome = $previousBookings->sum('subtotal');
        
        $currentTrxCount = $currentBookings->count();
        $previousTrxCount = $previousBookings->count();

        $incomeGrowth = $previousIncome > 0 ? (($currentIncome - $previousIncome) / $previousIncome) * 100 : ($currentIncome > 0 ? 100 : 0);
        $trxGrowth = $currentTrxCount - $previousTrxCount;

        // Best asset calculation
        $assetIncomes = $currentBookings->groupBy('asset_id')->map(function ($bookings) {
            return [
                'name' => $bookings->first()->asset_name,
                'income' => $bookings->sum('subtotal')
            ];
        })->sortByDesc('income');

        $bestAsset = $assetIncomes->first();
        $bestAssetPercent = $currentIncome > 0 && $bestAsset ? ($bestAsset['income'] / $currentIncome) * 100 : 0;

        $avgTrx = $currentTrxCount > 0 ? $currentIncome / $currentTrxCount : 0;

        $summaryData = [
            'totalPendapatan' => $currentIncome,
            'pendapatanGrowth' => round($incomeGrowth, 1),
            'totalTransaksi' => $currentTrxCount,
            'transaksiGrowth' => $trxGrowth,
            'asetTerbaik' => $bestAsset ? $bestAsset['name'] : '-',
            'asetTerbaikIncome' => $bestAsset ? $bestAsset['income'] : 0,
            'asetTerbaikPercent' => round($bestAssetPercent, 1),
            'avgTransaksi' => round($avgTrx)
        ];

        // Income Trend Data
        $trendData = [];
        if ($groupBy === 'hour') {
            for ($i = 0; $i < 24; $i++) {
                $label = str_pad($i, 2, '0', STR_PAD_LEFT) . ':00';
                $trendData[$label] = 0;
            }
            foreach ($currentBookings as $b) {
                $label = $b->created_at->format('H:00');
                if (isset($trendData[$label])) $trendData[$label] += $b->subtotal;
            }
        } elseif ($groupBy === 'day') {
            $periodDays = $startDate->diffInDays($endDate);
            for ($i = 0; $i <= $periodDays; $i++) {
                $date = $startDate->copy()->addDays($i);
                $label = $date->format('d M');
                $trendData[$label] = 0;
            }
            foreach ($currentBookings as $b) {
                $label = $b->created_at->format('d M');
                if (isset($trendData[$label])) $trendData[$label] += $b->subtotal;
            }
        } elseif ($groupBy === 'month') {
            $periodMonths = $startDate->diffInMonths($endDate);
            for ($i = 0; $i <= $periodMonths; $i++) {
                $date = $startDate->copy()->addMonths($i);
                $label = $date->format('M Y');
                $trendData[$label] = 0;
            }
            foreach ($currentBookings as $b) {
                $label = $b->created_at->format('M Y');
                if (isset($trendData[$label])) $trendData[$label] += $b->subtotal;
            }
        }

        $incomeTrendData = collect($trendData)->map(function ($income, $label) {
            return ['label' => $label, 'income' => $income];
        })->values()->toArray();

        // Asset Donut Chart Data
        $colors = ['#FFC000', '#0A2540', '#10b981', '#3b82f6', '#8b5cf6', '#f43f5e', '#ec4899', '#f97316'];
        $assetIncomeData = $assetIncomes->values()->map(function ($item, $index) use ($currentIncome, $colors) {
            return [
                'name' => $item['name'],
                'income' => $item['income'],
                'percent' => $currentIncome > 0 ? round(($item['income'] / $currentIncome) * 100, 1) : 0,
                'color' => $colors[$index % count($colors)]
            ];
        })->toArray();

        // Unit Breakdown
        $unitBreakdowns = [];
        $bookingsByAsset = $currentBookings->groupBy('asset_name');
        foreach ($bookingsByAsset as $assetName => $bookings) {
            $totalAssetIncome = $bookings->sum('subtotal');
            $units = $bookings->groupBy(function($b) { return $b->asset_unit_name ?: 'Semua Unit'; });
            $unitData = [];
            foreach ($units as $unitName => $uBookings) {
                $unitIncome = $uBookings->sum('subtotal');
                $unitData[] = [
                    'name' => $unitName,
                    'income' => $unitIncome,
                    'percent' => $totalAssetIncome > 0 ? round(($unitIncome / $totalAssetIncome) * 100, 1) : 0
                ];
            }
            usort($unitData, fn($a, $b) => $b['income'] <=> $a['income']);
            $unitBreakdowns[$assetName] = $unitData;
        }

        // Recent Transactions
        // Also fetch pending, cancelled, etc for the recent transactions view, regardless of date, but order by created_at.
        $recentTransactionsQuery = booking::whereHas('asset', function($q) use ($ownerProfileId) {
                $q->where('owner_profile_id', $ownerProfileId);
            })
            ->with(['asset:id,title', 'assetUnit:id,name'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
            
        $recentTransactions = $recentTransactionsQuery->map(function ($b) {
            $statusMap = [
                'pending' => 'Menunggu Pembayaran',
                'confirmed' => 'Dibayar',
                'active' => 'Aktif',
                'completed' => 'Selesai',
                'cancelled' => 'Dibatalkan'
            ];
            $startDate = Carbon::parse($b->start_date);
            $endDate = Carbon::parse($b->end_date);
            
            if ($startDate->isSameDay($endDate)) {
                $dateStr = $startDate->format('d M Y');
            } elseif ($startDate->format('M Y') === $endDate->format('M Y')) {
                $dateStr = $startDate->format('d') . '–' . $endDate->format('d M Y');
            } else {
                $dateStr = $startDate->format('d M Y') . ' – ' . $endDate->format('d M Y');
            }
            
            return [
                'id' => $b->booking_code ?? 'KS-'.$b->id,
                'asset' => $b->asset_name ?? ($b->asset->title ?? '-'),
                'unit' => $b->asset_unit_name ?? ($b->assetUnit->name ?? '-'),
                'date' => $dateStr,
                'total' => $b->subtotal,
                'status' => $statusMap[$b->booking_status] ?? ucfirst($b->booking_status)
            ];
        });

        return Inertia::render('owner/Income', [
            'initialPeriod' => $period,
            'summaryData' => $summaryData,
            'incomeTrendData' => $incomeTrendData,
            'assetIncomeData' => $assetIncomeData,
            'unitBreakdowns' => (object) $unitBreakdowns,
            'recentTransactions' => $recentTransactions
        ]);
    }
}
