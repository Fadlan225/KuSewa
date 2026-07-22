<?php
// Khusus test payload size homepage setelah Fix #2 (limit 12 per kategori)
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== TEST PAYLOAD SIZE (SEBELUM vs SESUDAH) ===\n\n";

// SEBELUM: tanpa limit (semua aset aktif)
$start = microtime(true);
$allAssets = App\Models\asset::where('status', 'active')
    ->select(['id', 'asset_category_id', 'owner_profile_id', 'title', 'city', 'address', 'status'])
    ->with([
        'thumbnailImages' => function($q) { $q->select(['id', 'asset_id', 'image'])->orderBy('id')->limit(3); },
        'defaultPricing:id,asset_id,price',
        'category:id,name,icon',
    ])
    ->get();
$timeBefore = round((microtime(true) - $start) * 1000, 2);
$jsonBefore = json_encode($allAssets->toArray());
$sizeBefore = strlen($jsonBefore);

echo "[SEBELUM Fix #2] Tanpa limit:\n";
echo "  Total aset      : " . $allAssets->count() . "\n";
echo "  Query time      : {$timeBefore}ms\n";
echo "  JSON payload    : " . round($sizeBefore / 1024, 1) . " KB\n\n";

// SESUDAH: dengan limit 12 per kategori (Fix #2)
$start = microtime(true);
$categories = App\Models\asset_category::select(['id', 'name', 'icon'])
    ->with(['assets' => function ($q) {
        $q->where('status', 'active')
          ->select(['id', 'asset_category_id', 'owner_profile_id', 'title', 'city', 'address', 'status'])
          ->with([
              'thumbnailImages' => fn($q) => $q->select(['id', 'asset_id', 'image'])->orderBy('id')->limit(3),
              'defaultPricing:id,asset_id,price',
          ])
          ->withAvg('reviews as reviews_avg_rating', 'rating')
          ->latest('id')
          ->limit(12); // FIX #2
    }])
    ->whereHas('assets', fn($q) => $q->where('status', 'active'))
    ->get();
$timeAfter = round((microtime(true) - $start) * 1000, 2);
$jsonAfter  = json_encode($categories->toArray());
$sizeAfter  = strlen($jsonAfter);

$totalAssetsLoaded = $categories->sum(fn($c) => $c->assets->count());

echo "[SESUDAH Fix #2] Limit 12/kategori:\n";
echo "  Total aset dimuat : {$totalAssetsLoaded} (dari 714 aktif)\n";
echo "  Query time        : {$timeAfter}ms\n";
echo "  JSON payload      : " . round($sizeAfter / 1024, 1) . " KB\n\n";

$reduction = round((1 - $sizeAfter / $sizeBefore) * 100, 1);
$speedup   = round($sizeBefore / $sizeAfter, 1);

echo "=== PERBANDINGAN ===\n";
echo "  Payload sebelum : " . round($sizeBefore / 1024, 1) . " KB\n";
echo "  Payload sesudah : " . round($sizeAfter / 1024, 1) . " KB\n";
echo "  Pengurangan     : {$reduction}%\n";
echo "  Speedup faktor  : {$speedup}x lebih kecil\n";
echo "  Time sebelum    : {$timeBefore}ms\n";
echo "  Time sesudah    : {$timeAfter}ms\n";
