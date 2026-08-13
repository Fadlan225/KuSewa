<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;
use App\Models\NearbyPlace;
use App\Services\OpenStreetMapService;
use App\Models\asset;

class NearbyPlacesTest extends TestCase
{
    use RefreshDatabase;

    public function test_mapping_categories()
    {
        // Testing private method determineCategory using Reflection
        $reflection = new \ReflectionClass(OpenStreetMapService::class);
        $method = $reflection->getMethod('determineCategory');
        $method->setAccessible(true);

        $hospital = $method->invoke(null, ['amenity' => 'hospital', 'name' => 'RSUD']);
        $this->assertEquals('health', $hospital);

        $station = $method->invoke(null, ['public_transport' => 'station', 'name' => 'Stasiun']);
        $this->assertEquals('public_transport', $station);

        $mall = $method->invoke(null, ['shop' => 'mall', 'name' => 'Mall ABC']);
        $this->assertEquals('shopping', $mall);
    }

    public function test_sync_osm_creates_records()
    {
        Http::fake([
            '*overpass-api.de*' => Http::response([
                'elements' => [
                    [
                        'id' => 12345,
                        'lat' => -0.502,
                        'lon' => 117.153,
                        'tags' => [
                            'name' => 'Rumah Sakit Test',
                            'amenity' => 'hospital'
                        ]
                    ]
                ]
            ], 200)
        ]);

        // Reflection to call syncFromOsm
        $reflection = new \ReflectionClass(OpenStreetMapService::class);
        $method = $reflection->getMethod('syncFromOsm');
        $method->setAccessible(true);

        $method->invoke(null, -0.502, 117.153, 3000);

        $this->assertDatabaseHas('nearby_places', [
            'osm_id' => 12345,
            'name' => 'Rumah Sakit Test',
            'category' => 'health'
        ]);
        
        $place = NearbyPlace::where('osm_id', 12345)->first();
        $this->assertNotNull($place->metadata);
        $this->assertEquals('hospital', $place->metadata['amenity']);
    }

    public function test_distance_calculation_and_limit()
    {
        // Asset location
        $assetLat = -0.500;
        $assetLon = 117.150;

        // Place within radius (e.g. 1km away)
        NearbyPlace::create([
            'osm_id' => 1,
            'name' => 'Klinik Dekat',
            'category' => 'health',
            'latitude' => -0.508,
            'longitude' => 117.150,
            'metadata' => []
        ]);

        // Place out of radius (e.g. 10km away)
        NearbyPlace::create([
            'osm_id' => 2,
            'name' => 'Klinik Jauh',
            'category' => 'health',
            'latitude' => -0.600,
            'longitude' => 117.150,
            'metadata' => []
        ]);

        // Place 3, 4, 5 to test limit (3 per category)
        NearbyPlace::create(['osm_id' => 3, 'name' => 'Klinik A', 'category' => 'health', 'latitude' => -0.501, 'longitude' => 117.150, 'metadata' => []]);
        NearbyPlace::create(['osm_id' => 4, 'name' => 'Klinik B', 'category' => 'health', 'latitude' => -0.502, 'longitude' => 117.150, 'metadata' => []]);
        NearbyPlace::create(['osm_id' => 5, 'name' => 'Klinik C', 'category' => 'health', 'latitude' => -0.503, 'longitude' => 117.150, 'metadata' => []]);


        $results = OpenStreetMapService::getNearbyPlaces($assetLat, $assetLon, 999, 3000); // 3000m radius

        $this->assertArrayHasKey('health', $results);
        $this->assertCount(3, $results['health']); // Limited to 3
        
        // Assert Klinik Jauh is not in results
        $names = collect($results['health'])->pluck('name');
        $this->assertFalse($names->contains('Klinik Jauh'));
        
        // Assert sorting (Klinik A is closest, distance should be ascending)
        $this->assertTrue($results['health'][0]['distance'] <= $results['health'][1]['distance']);
    }
}
