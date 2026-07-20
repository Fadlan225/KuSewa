<?php

namespace App\Http\Controllers;
use App\Models\asset_category;
use App\Models\asset;

use Illuminate\Http\Request;

class AssetController extends Controller
{
    /**
     * Display a listing of the resource.
     */
public function index()
{
    $categories = asset_category::select(['id', 'name', 'icon'])
        ->with([
            'assets' => function ($query) {
                $query->where('status', 'active')
                    ->select([
                        'id',
                        'asset_category_id',
                        'owner_profile_id',
                        'title',
                        'city',
                        'address',
                        'status'
                    ])
                    ->with([
                        'firstImage',
                        'primaryPricing:id,asset_id,period,price',
                        'favorites' => function ($query) {
                            $query->where('user_id', auth()->id());
                        }
                    ])
                    ->withAvg('reviews as reviews_avg_rating', 'rating');
            }
        ])
        ->whereHas('assets', fn($q) => $q->where('status', 'active'))
        ->get();


    $categories->each(function ($category) {

        $category->assets->each(function ($asset) {

            $favorite = $asset->favorites->first();

            $asset->isFavorite = (bool) $favorite;
            $asset->favorite_id = $favorite?->id;

            unset($asset->favorites);

        });

    });


    return inertia('Home/index', [
        'categories' => $categories
    ]);
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
            'category',
            'images',
            'pricings',
            'ownerProfile.user',
            'reviews.user',
        ])
        ->loadAvg('reviews', 'rating')
        ->loadCount('reviews');


        return inertia('Home/Assets/Show', [
            'asset' => $asset
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
