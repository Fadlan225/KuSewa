<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$kos = \App\Models\asset_type::with('allowedFacilities.category')->where('name', 'Kos')->first();
$cats = collect($kos->allowedFacilities)->map(function($f) { return $f->category->name ?? 'none'; })->unique()->values();
echo "Categories in allowedFacilities: " . json_encode($cats) . "\n";
