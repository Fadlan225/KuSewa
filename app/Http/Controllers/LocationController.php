<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class LocationController extends Controller
{
    /**
     * Get list of cities for dropdowns
     *
     * @return JsonResponse
     */
    public function getCities(): JsonResponse
    {
        // Cache the result forever (or a very long time) since city data rarely changes
        // This avoids querying the database on every request
        $cities = Cache::rememberForever('locations.cities', function () {
            return DB::table('cities')
                ->select('code', 'name')
                ->orderBy('name', 'asc')
                ->get()
                ->map(fn($item) => (array) $item)
                ->toArray();
        });

        return response()->json([
            'data' => $cities
        ]);
    }
}
