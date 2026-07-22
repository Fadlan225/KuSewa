<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== TEST PERFORMA FINAL DENGAN 1000 DATA ===\n\n";

// Test 1: Search page (24 per page)
$start = microtime(true);
$assets = App\Models\asset::where('status', 'active')
    ->select(['id', 'asset_category_id', 'owner_profile_id', 'title', 'city', 'address', 'status'])
    ->with([
        'thumbnailImages' => function($q) { $q->select(['id', 'asset_id', 'image'])->orderBy('id')->limit(3); },
        'defaultPricing:id,asset_id,price',
        'category:id,name,icon',
    ])
    ->withAvg('reviews as reviews_avg_rating', 'rating')
    ->paginate(24);
$time1 = round((microtime(true) - $start) * 1000, 2);
$firstAsset = $assets->items()[0];
$imgCount = count($firstAsset->getRelation('thumbnailImages'));

echo "[1] SEARCH PAGE (paginate 24)\n";
echo "    Total aset aktif  : " . $assets->total() . " dari 1000\n";
echo "    Gambar per aset   : {$imgCount} (max 3)\n";
echo "    Waktu query       : {$time1}ms\n\n";

// Test 2: Home page categories
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
          ->limit(20);
    }])
    ->whereHas('assets', fn($q) => $q->where('status', 'active'))
    ->get();
$time2 = round((microtime(true) - $start) * 1000, 2);
$totalLoaded = $categories->sum(fn($c) => $c->assets->count());

echo "[2] HOME PAGE (kategori + aset)\n";
echo "    Total kategori    : " . $categories->count() . "\n";
echo "    Total aset dimuat : {$totalLoaded} (20 per kategori)\n";
echo "    Waktu query       : {$time2}ms\n\n";

// Test 3: Keyword search + filter kota
$start = microtime(true);
$result = App\Models\asset::where('status', 'active')
    ->where(function ($q) {
        $q->where('title', 'like', '%Samarinda%')
          ->orWhere('city', 'like', '%Samarinda%');
    })
    ->with([
        'thumbnailImages' => fn($q) => $q->select(['id', 'asset_id', 'image'])->orderBy('id')->limit(3),
        'defaultPricing:id,asset_id,price',
        'category:id,name,icon',
    ])
    ->withAvg('reviews as reviews_avg_rating', 'rating')
    ->paginate(24);
$time3 = round((microtime(true) - $start) * 1000, 2);

echo "[3] KEYWORD SEARCH 'Samarinda'\n";
echo "    Hasil ditemukan   : " . $result->total() . "\n";
echo "    Waktu query       : {$time3}ms\n\n";

// Test 4: Sort by price asc
$start = microtime(true);
$sortResult = App\Models\asset::where('status', 'active')
    ->select(['id', 'asset_category_id', 'owner_profile_id', 'title', 'city', 'address', 'status'])
    ->with([
        'thumbnailImages' => fn($q) => $q->select(['id', 'asset_id', 'image'])->orderBy('id')->limit(3),
        'defaultPricing:id,asset_id,price',
        'category:id,name,icon',
    ])
    ->withAvg('reviews as reviews_avg_rating', 'rating')
    ->orderBy(
        App\Models\asset_pricing::select('price')
            ->whereColumn('asset_id', 'assets.id')
            ->orderBy('price')
            ->limit(1),
        'asc'
    )
    ->paginate(24);
$time4 = round((microtime(true) - $start) * 1000, 2);

echo "[4] SORT HARGA TERENDAH\n";
echo "    Total hasil       : " . $sortResult->total() . "\n";
echo "    Harga pertama     : Rp " . number_format($sortResult->items()[0]->default_pricing?->price ?? 0, 0, ',', '.') . "\n";
echo "    Waktu query       : {$time4}ms\n\n";

$avg = round(($time1 + $time2 + $time3 + $time4) / 4, 2);
echo "==========================================\n";
echo "SUMMARY\n";
echo "  Search page   : {$time1}ms\n";
echo "  Home page     : {$time2}ms\n";
echo "  Keyword search: {$time3}ms\n";
echo "  Sort by price : {$time4}ms\n";
echo "  Average       : {$avg}ms\n";
echo "==========================================\n";
