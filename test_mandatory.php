<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$assetType = \App\Models\asset_type::where('name', 'Kos')->first();
$facilityCategories = \App\Models\facility_category::orderBy('name')->select('id', 'name')->get();
$mandatoryFacilityNames = $assetType->getMandatoryFacilityCategories();
$mandatoryFacilityCategories = collect($mandatoryFacilityNames)->map(function ($name) use ($facilityCategories) {
    return $facilityCategories->firstWhere('name', $name);
})->filter()->values();

echo "Mandatory Facility Categories: " . json_encode($mandatoryFacilityCategories) . "\n";
