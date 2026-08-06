<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class LocationController extends Controller
{
    /**
     * Get list of provinces
     */
    public function getProvinces(): JsonResponse
    {
        $provinces = Cache::rememberForever('locations.provinces', function () {
            return DB::table('provinces')
                ->select('code', 'name')
                ->orderBy('name', 'asc')
                ->get()
                ->map(fn($item) => (array) $item)
                ->toArray();
        });

        return response()->json([
            'data' => $provinces
        ]);
    }

    /**
     * Get list of cities for dropdowns
     * Filter by province_code if provided
     */
    public function getCities(Request $request): JsonResponse
    {
        $provinceCode = $request->query('province_code');
        
        $cacheKey = $provinceCode ? "locations.cities.{$provinceCode}" : 'locations.cities.all';

        $cities = Cache::rememberForever($cacheKey, function () use ($provinceCode) {
            $query = DB::table('cities')
                ->select('code', 'name', 'province_code')
                ->orderBy('name', 'asc');

            if ($provinceCode) {
                $query->where('province_code', $provinceCode);
            }

            return $query->get()
                ->map(fn($item) => (array) $item)
                ->toArray();
        });

        return response()->json([
            'data' => $cities
        ]);
    }

    /**
     * Get list of districts
     * Filter by city_code if provided
     */
    public function getDistricts(Request $request): JsonResponse
    {
        $cityCode = $request->query('city_code');
        
        $cacheKey = $cityCode ? "locations.districts.{$cityCode}" : 'locations.districts.all';

        $districts = Cache::rememberForever($cacheKey, function () use ($cityCode) {
            $query = DB::table('districts')
                ->select('code', 'name', 'city_code')
                ->orderBy('name', 'asc');

            if ($cityCode) {
                $query->where('city_code', $cityCode);
            }

            return $query->get()
                ->map(fn($item) => (array) $item)
                ->toArray();
        });

        return response()->json([
            'data' => $districts
        ]);
    }

    /**
     * Get list of villages
     * Filter by district_code if provided
     */
    public function getVillages(Request $request): JsonResponse
    {
        $districtCode = $request->query('district_code');
        
        $cacheKey = $districtCode ? "locations.villages.{$districtCode}" : 'locations.villages.all';

        $villages = Cache::rememberForever($cacheKey, function () use ($districtCode) {
            $query = DB::table('villages')
                ->select('code', 'name', 'district_code')
                ->orderBy('name', 'asc');

            if ($districtCode) {
                $query->where('district_code', $districtCode);
            }

            return $query->get()
                ->map(fn($item) => (array) $item)
                ->toArray();
        });

        return response()->json([
            'data' => $villages
        ]);
    }
}
