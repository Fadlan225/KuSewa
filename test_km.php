<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$assetType = \App\Models\asset_type::with('allowedFacilities.category')->where('name', 'Kos')->first();
$km = collect($assetType->allowedFacilities)->filter(function($f) {
    return ($f->category->name ?? '') === 'Kamar Mandi';
})->values();

echo "Kamar Mandi Facilities: " . json_encode($km) . "\n";
