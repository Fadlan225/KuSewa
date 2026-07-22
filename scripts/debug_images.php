<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Debug: cek apakah thumbnailImages bekerja
$asset = App\Models\asset::where('status', 'active')
    ->with(['thumbnailImages' => function($q) {
        $q->select(['id', 'asset_id', 'image'])->orderBy('id');
    }])
    ->first();

echo "Asset ID: " . $asset->id . "\n";
// Cek key nama yang dipakai Eloquent (camelCase vs snake_case)
$relations = $asset->getRelations();
echo "Loaded relations: " . implode(', ', array_keys($relations)) . "\n";

if (isset($relations['thumbnailImages'])) {
    echo "thumbnailImages count: " . count($relations['thumbnailImages']) . "\n";
    if ($relations['thumbnailImages']->first()) {
        echo "First image: " . $relations['thumbnailImages']->first()->image . "\n";
    }
} else {
    echo "thumbnailImages NOT found in relations!\n";
}

// Cek via direct query
$imgs = \Illuminate\Support\Facades\DB::table('asset_images')
    ->where('asset_id', $asset->id)
    ->count();
echo "Total images in DB for this asset: " . $imgs . "\n";
