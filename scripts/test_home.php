<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Test HomeController index via DB query langsung
$categories = \App\Models\asset_category::select(['id', 'name', 'icon'])
    ->with(['types:id,category_id,name,allow_units'])
    ->whereHas('types.assets', fn($q) => $q->where('status', 'active'))
    ->get();

$categories->each(function ($category) {
    $typeIds = $category->types->pluck('id');
    $category->assets = \App\Models\asset::whereIn('asset_type_id', $typeIds)
        ->where('status', 'active')
        ->with(['thumbnailImages' => fn($q) => $q->select(['id', 'asset_id', 'image'])->orderBy('id')->limit(3), 'type:id,name,allow_units,category_id'])
        ->limit(12)
        ->get();
});
$categories = $categories->filter(fn($c) => $c->assets->isNotEmpty())->values();

echo "=== TEST HOMEPAGE RESPONSE ===\n\n";
echo "Categories: " . $categories->count() . "\n\n";

foreach ($categories as $cat) {
    $assetCount = $cat->assets->count();
    echo "  [{$cat->name}] → {$assetCount} aset\n";
    if ($assetCount > 0) {
        $first = $cat->assets->first();
        $img = $first->thumbnailImages->first();
        echo "    Sample: '{$first->title}'\n";
        echo "    Type: " . ($first->type?->name ?? 'NULL') . "\n";
        echo "    Images: " . $first->thumbnailImages->count() . "\n";
        echo "    image_url[0]: " . ($img?->image_url ?? 'NULL') . "\n";
    }
}

echo "\n=== RESPONSE OK ===\n";

foreach ($categories as $cat) {
    $assetCount = count($cat->assets);
    echo "  [{$cat->name}] → {$assetCount} aset\n";
    if ($assetCount > 0) {
        $first = $cat->assets[0];
        echo "    Sample: '{$first->title}'\n";
        echo "    Type: " . ($first->type->name ?? 'NULL') . "\n";
        echo "    Images: " . count($first->thumbnail_images ?? []) . "\n";
        echo "    image_url[0]: " . ($first->thumbnail_images[0]->image_url ?? 'NULL') . "\n";
    }
}

echo "\n=== RESPONSE OK ===\n";
