<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\asset;

class HomeAssetController extends Controller
{
    public function nearby(Request $request)
    {
        $lat = $request->input('lat');
        $lng = $request->input('lng');

        if (!$lat || !$lng) {
            return response()->json([]);
        }

        // Radius filter dalam KM (misal 50 KM) untuk Bounding Box
        $radius = 50;
        $latDiff = $radius / 111.045;
        $lngDiff = $radius / (111.045 * cos(deg2rad($lat)));

        $haversine = "(6371 * acos(cos(radians(?))
                        * cos(radians(latitude))
                        * cos(radians(longitude) - radians(?))
                        + sin(radians(?))
                        * sin(radians(latitude))))";

        $assets = asset::select('assets.*')
            ->selectRaw("{$haversine} AS distance", [$lat, $lng, $lat])
            ->whereBetween('latitude', [$lat - $latDiff, $lat + $latDiff])
            ->whereBetween('longitude', [$lng - $lngDiff, $lng + $lngDiff])
            ->where('status', 'approved')
            ->having('distance', '<=', $radius)
            ->orderBy('distance')
            ->limit(10)
            ->with([
                'thumbnailImages' => fn($q) => $q->select(['id', 'asset_id', 'image'])->orderBy('id')->limit(3),
                'defaultPricing:id,asset_id,price',
                'type:id,name,allow_units,rental_unit,category_id',
                'city:code,name',
                'district:code,name',
                'province:code,name',
                'favorites' => function ($q) {
                    if (auth()->check()) {
                        $q->select(['id', 'user_id', 'asset_id'])->where('user_id', auth()->id());
                    } else {
                        $q->whereRaw('1=0');
                    }
                }
            ])
            ->withCount('reviews')
            ->withAvg('reviews as reviews_avg_rating', 'rating')
            ->get()
            ->map(function ($asset) {
                $favorite = $asset->favorites->first();
                $asset->isFavorite = (bool) $favorite;
                $asset->favorite_id = $favorite?->id;
                unset($asset->favorites);
                return $asset;
            });

        return response()->json($assets);
    }
}
