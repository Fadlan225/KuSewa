<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$lat = -0.502;
$lon = 117.153;
$radius = 3000;

$queryLines = [];
$categoryMapping = [
    'health' => [
        'amenity' => ['hospital', 'clinic', 'pharmacy'],
        'healthcare' => '*'
    ],
    'public_transport' => [
        'railway' => ['station', 'halt'],
        'amenity' => ['bus_station'],
        'highway' => ['bus_stop'],
        'public_transport' => ['station']
    ],
    'shopping' => [
        'shop' => ['mall', 'supermarket', 'department_store', 'convenience']
    ],
    'recreation' => [
        'leisure' => ['park', 'playground'],
        'tourism' => ['attraction', 'museum', 'zoo']
    ],
    'food' => [
        'amenity' => ['restaurant', 'cafe', 'fast_food']
    ],
    'religious' => [
        'amenity' => ['place_of_worship']
    ],
    'education' => [
        'amenity' => ['school', 'college', 'university']
    ]
];

foreach ($categoryMapping as $category => $tags) {
    foreach ($tags as $key => $values) {
        if ($values === '*') {
            $queryLines[] = "nwr[\"{$key}\"](around:{$radius}, {$lat}, {$lon});";
        } else {
            $valuesStr = implode('|', $values);
            $queryLines[] = "nwr[\"{$key}\"~\"^{$valuesStr}$\" ](around:{$radius}, {$lat}, {$lon});";
        }
    }
}

$queryBody = implode("\n  ", $queryLines);
$overpassQuery = "[out:json][timeout:25];\n(\n  {$queryBody}\n);\nout center;";

echo "Sending query...\n";
$response = Illuminate\Support\Facades\Http::timeout(30)
    ->withHeaders(['User-Agent' => 'KuSewa/1.0'])
    ->get('https://overpass-api.de/api/interpreter', [
        'data' => $overpassQuery
    ]);

if ($response->successful()) {
    $data = $response->json();
    echo "Count elements: " . count($data['elements'] ?? []) . "\n";
    file_put_contents('osm_debug.json', json_encode($data, JSON_PRETTY_PRINT));
} else {
    echo "Error: " . $response->status() . " " . $response->body() . "\n";
}
