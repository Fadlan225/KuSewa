<?php
$file = 'app/Http/Controllers/Owner/IncomeController.php';
$content = file_get_contents($file);

// Add AssetView or asset_units if not already imported
if (strpos($content, 'use App\Models\asset_units;') === false) {
    $content = str_replace("use App\Models\asset;", "use App\Models\asset;\nuse App\Models\asset_units;", $content);
}

// 1. Get active_asset_slug and determine isGlobal
$contextLogic = <<<'PHP'
        $activeAssetSlug = $request->session()->get('active_asset_slug');
        $isGlobal = empty($activeAssetSlug) || $activeAssetSlug === 'global';

        $assetQuery = asset::where('owner_profile_id', $ownerProfileId);
        if (!$isGlobal) {
            $assetQuery->where(function($q) use ($activeAssetSlug) {
                $q->where('slug', $activeAssetSlug)->orWhere('id', $activeAssetSlug);
            });
        }
        $assetIds = $assetQuery->pluck('id');
        
        $hasUnits = false;
        if (!$isGlobal && $assetIds->isNotEmpty()) {
            $hasUnits = asset_units::where('asset_id', $assetIds->first())->sum('quantity') > 0;
        }
PHP;

$content = str_replace(
    "\$period = \$request->query('period', 'bulan_ini');",
    "\$period = \$request->query('period', 'bulan_ini');\n\n" . $contextLogic,
    $content
);

// 2. Base query filtering by assetIds instead of just owner profile
$content = preg_replace(
    "/\\\$currentBookings = booking::whereHas\('asset', function\(\\\$q\) use \(\\\$ownerProfileId\) \{\s*\\\$q->where\('owner_profile_id', \\\$ownerProfileId\);\s*\}\)/",
    "\$currentBookings = booking::whereIn('asset_id', \$assetIds)",
    $content
);
$content = preg_replace(
    "/\\\$previousBookings = booking::whereHas\('asset', function\(\\\$q\) use \(\\\$ownerProfileId\) \{\s*\\\$q->where\('owner_profile_id', \\\$ownerProfileId\);\s*\}\)/",
    "\$previousBookings = booking::whereIn('asset_id', \$assetIds)",
    $content
);

// 3. Best asset calculation logic adjustment
$bestAssetReplacement = <<<'PHP'
        if ($isGlobal) {
            $incomesByGrouping = $currentBookings->groupBy('asset_id')->map(function ($bookings) {
                return [
                    'name' => $bookings->first()->asset_name ?? ($bookings->first()->asset->title ?? '-'),
                    'income' => $bookings->sum('subtotal')
                ];
            })->sortByDesc('income');
        } else {
            $incomesByGrouping = $currentBookings->groupBy('asset_unit_id')->map(function ($bookings) {
                return [
                    'name' => $bookings->first()->asset_unit_name ?? ($bookings->first()->assetUnit->name ?? 'Semua Unit'),
                    'income' => $bookings->sum('subtotal')
                ];
            })->sortByDesc('income');
        }

        $bestAsset = $incomesByGrouping->first();
        $bestAssetPercent = $currentIncome > 0 && $bestAsset ? ($bestAsset['income'] / $currentIncome) * 100 : 0;
PHP;

$content = preg_replace(
    "/\/\/ Best asset calculation[\s\S]*?\\\$bestAssetPercent = \\\$currentIncome > 0 && \\\$bestAsset \? \(\\\$bestAsset\['income'\] \/ \\\$currentIncome\) \* 100 : 0;/",
    $bestAssetReplacement,
    $content
);

// 4. Asset Donut Chart Data adjustment
$donutReplacement = <<<'PHP'
        // Asset Donut Chart Data
        $colors = ['#FFC000', '#0A2540', '#10b981', '#3b82f6', '#8b5cf6', '#f43f5e', '#ec4899', '#f97316'];
        
        $assetIncomeData = [];
        if ($isGlobal || $hasUnits) {
            $assetIncomeData = $incomesByGrouping->values()->map(function ($item, $index) use ($currentIncome, $colors) {
                return [
                    'name' => $item['name'],
                    'income' => $item['income'],
                    'percent' => $currentIncome > 0 ? round(($item['income'] / $currentIncome) * 100, 1) : 0,
                    'color' => $colors[$index % count($colors)]
                ];
            })->toArray();
        }
PHP;

$content = preg_replace(
    "/\/\/ Asset Donut Chart Data[\s\S]*?\}\)->toArray\(\);/",
    $donutReplacement,
    $content
);

// 5. Recent Transactions filtering
$recentTrxReplacement = <<<'PHP'
        $recentTransactionsQuery = booking::whereIn('asset_id', $assetIds)
            ->with(['asset:id,title', 'assetUnit:id,name'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
PHP;

$content = preg_replace(
    "/\$recentTransactionsQuery = booking::whereHas\('asset', function\(\\\$q\) use \(\\\$ownerProfileId\) \{\s*\\\$q->where\('owner_profile_id', \\\$ownerProfileId\);\s*\}\)\s*->with\(\['asset:id,title', 'assetUnit:id,name'\]\)\s*->orderBy\('created_at', 'desc'\)\s*->limit\(10\)\s*->get\(\);/",
    $recentTrxReplacement,
    $content
);

// 6. Return Inertia response
$inertiaReplacement = <<<'PHP'
        return Inertia::render('owner/Income', [
            'initialPeriod' => $period,
            'summaryData' => $summaryData,
            'incomeTrendData' => $incomeTrendData,
            'assetIncomeData' => $assetIncomeData,
            'unitBreakdowns' => (object) $unitBreakdowns,
            'recentTransactions' => $recentTransactions,
            'isGlobal' => $isGlobal,
            'hasUnits' => $hasUnits
        ]);
PHP;

$content = preg_replace(
    "/return Inertia::render\('owner\/Income', \[\s*'initialPeriod' => \\\$period,\s*'summaryData' => \\\$summaryData,\s*'incomeTrendData' => \\\$incomeTrendData,\s*'assetIncomeData' => \\\$assetIncomeData,\s*'unitBreakdowns' => \(object\) \\\$unitBreakdowns,\s*'recentTransactions' => \\\$recentTransactions\s*\]\);/",
    $inertiaReplacement,
    $content
);

file_put_contents($file, $content);
echo "Successfully refactored IncomeController.php\n";
