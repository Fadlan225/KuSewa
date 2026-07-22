<?php

namespace App\Http\Controllers;
use App\Models\asset;

use Illuminate\Http\Request;

class AssetController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {

    }
    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(asset $asset)
    {
        $asset->load([
            'type:id,name,allow_units,rental_unit,category_id',
            'type.category:id,name,icon',
            'images',
            'thumbnailImages',
            'pricings',
            'ownerProfile.user',
            'reviews.user',
            'favorites' => function ($query) {
                $query->where('user_id', auth()->id());
            }
        ])
        ->loadAvg('reviews', 'rating')
        ->loadCount([
            'reviews',
            'favorites'
        ]);

        $favorite = $asset->favorites->first();
        $asset->isFavorite = (bool) $favorite;
        $asset->favorite_id = $favorite?->id;
        unset($asset->favorites);

        $serviceFee = \Illuminate\Support\Facades\DB::table('service_fees')->where('fee_type', 'percentage')->value('fee_value') ?? 5;

        return inertia('Home/Assets/Show', [
            'asset' => $asset,
            'serviceFee' => $serviceFee
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
