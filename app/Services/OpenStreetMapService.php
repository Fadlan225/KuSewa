<?php

namespace App\Services;

use App\Models\NearbyPlace;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class OpenStreetMapService
{
    /**
     * Map OSM tags to KuSewa categories
     */
    protected static $categoryMapping = [
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

    /**
     * Define the priority of categories to ensure consistent ordering in output.
     */
    protected static $categoryPriority = [
        'health' => 1,
        'public_transport' => 2,
        'shopping' => 3,
        'recreation' => 4,
        'food' => 5,
        'religious' => 6,
        'education' => 7,
    ];

    /**
     * Get nearby places for an asset
     * 
     * @param float $lat
     * @param float $lon
     * @param int $assetId
     * @param int $radius in meters
     * @return array
     */
    public static function getNearbyPlaces($lat, $lon, $assetId, $radius = 3000)
    {
        if (!$lat || !$lon) {
            return [];
        }

        $cacheKey = "osm_sync_asset_{$assetId}";

        // Check if we need to sync data from OSM
        if (!Cache::has($cacheKey)) {
            self::syncFromOsm($lat, $lon, $radius);
            // Cache the sync flag for 7 days
            Cache::put($cacheKey, true, now()->addDays(7));
        }

        $maxDistanceKm = $radius / 1000;
        $driver = \Illuminate\Support\Facades\DB::connection()->getDriverName();

        if ($driver === 'sqlite') {
            // Fallback for SQLite testing which lacks trig functions
            $allPlaces = NearbyPlace::all();
            $places = $allPlaces->filter(function($place) use ($lat, $lon, $maxDistanceKm) {
                $earthRadius = 6371;
                $latDelta = deg2rad($place->latitude - $lat);
                $lonDelta = deg2rad($place->longitude - $lon);
                $a = sin($latDelta / 2) * sin($latDelta / 2) +
                     cos(deg2rad($lat)) * cos(deg2rad($place->latitude)) *
                     sin($lonDelta / 2) * sin($lonDelta / 2);
                $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
                $distance = $earthRadius * $c;
                
                $place->setAttribute('distance', $distance);
                return $distance <= $maxDistanceKm;
            })->sortBy('distance')->values();
        } else {
            $haversine = "(
                6371 * acos(
                    cos(radians(?))
                    * cos(radians(latitude))
                    * cos(radians(longitude) - radians(?))
                    + sin(radians(?)) * sin(radians(latitude))
                )
            )";

            $places = NearbyPlace::select('*')
                ->selectRaw("{$haversine} AS distance", [$lat, $lon, $lat])
                ->having('distance', '<=', $maxDistanceKm)
                ->orderBy('distance', 'asc')
                ->get();
        }

        // Group by category, limit 3 per category, sort by priority
        $grouped = [];
        foreach (self::$categoryPriority as $cat => $priority) {
            $catPlaces = $places->where('category', $cat)->take(3)->values();
            if ($catPlaces->isNotEmpty()) {
                $grouped[$cat] = $catPlaces->map(function ($place) {
                    return [
                        'name' => $place->name,
                        'distance' => round($place->distance, 2),
                        'address' => $place->address,
                    ];
                });
            }
        }

        return $grouped;
    }

    /**
     * Fetch from Overpass API and sync to local DB
     */
    protected static function syncFromOsm($lat, $lon, $radius)
    {
        // Build Overpass QL query
        $overpassUrl = config('services.overpass.url', 'https://overpass-api.de/api/interpreter');

        // We fetch nodes, ways, and relations that match our criteria
        $queryLines = [];
        foreach (self::$categoryMapping as $category => $tags) {
            foreach ($tags as $key => $values) {
                if ($values === '*') {
                    $queryLines[] = "nwr[\"{$key}\"](around:{$radius}, {$lat}, {$lon});";
                } else {
                    foreach ($values as $val) {
                        $queryLines[] = "nwr[\"{$key}\"=\"{$val}\"](around:{$radius}, {$lat}, {$lon});";
                    }
                }
            }
        }

        $queryBody = implode("\n  ", $queryLines);
        $overpassQuery = "[out:json][timeout:25];\n(\n  {$queryBody}\n);\nout center;";

        try {
            $response = Http::timeout(30)
                ->withHeaders(['User-Agent' => 'KuSewa/1.0'])
                ->get($overpassUrl, [
                    'data' => $overpassQuery
                ]);

            if ($response->successful()) {
                $data = $response->json();
                if (isset($data['elements'])) {
                    self::processOsmElements($data['elements']);
                }
            } else {
                Log::error("Overpass API error", ['status' => $response->status(), 'body' => $response->body()]);
            }
        } catch (\Exception $e) {
            Log::error("Overpass API connection error", ['message' => $e->getMessage()]);
        }
    }

    /**
     * Process elements from OSM and save to DB
     */
    protected static function processOsmElements($elements)
    {
        $now = now();
        
        foreach ($elements as $el) {
            if (!isset($el['tags']['name'])) {
                continue; // Skip POIs without names
            }

            $tags = $el['tags'];
            $category = self::determineCategory($tags);

            if (!$category) {
                continue; // Cannot map to our categories
            }

            // For nodes, lat/lon is direct. For ways/relations, we use 'center'
            $latitude = $el['lat'] ?? $el['center']['lat'] ?? null;
            $longitude = $el['lon'] ?? $el['center']['lon'] ?? null;

            if (!$latitude || !$longitude) {
                continue;
            }

            // Extract address info if available
            $addressParts = [];
            if (isset($tags['addr:street'])) $addressParts[] = $tags['addr:street'];
            if (isset($tags['addr:housenumber'])) $addressParts[] = $tags['addr:housenumber'];
            if (isset($tags['addr:city'])) $addressParts[] = $tags['addr:city'];
            $address = !empty($addressParts) ? implode(', ', $addressParts) : null;

            NearbyPlace::updateOrCreate(
                ['osm_id' => $el['id']],
                [
                    'name' => $tags['name'],
                    'category' => $category,
                    'latitude' => $latitude,
                    'longitude' => $longitude,
                    'address' => $address,
                    'metadata' => $tags,
                    'last_synced_at' => $now,
                ]
            );
        }
    }

    /**
     * Determine KuSewa category based on OSM tags
     */
    protected static function determineCategory($tags)
    {
        foreach (self::$categoryMapping as $category => $mapTags) {
            foreach ($mapTags as $key => $values) {
                if (isset($tags[$key])) {
                    if ($values === '*') {
                        return $category;
                    }
                    if (in_array($tags[$key], $values)) {
                        return $category;
                    }
                }
            }
        }

        return null;
    }
}
