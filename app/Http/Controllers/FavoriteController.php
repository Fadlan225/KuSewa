<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Favorite;
use Inertia\Inertia;

class FavoriteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $favorites = auth()->user()->favorites()
            ->with(['asset' => function ($query) {
                $query->select([
                    'id', 'asset_type_id', 'owner_profile_id',
                    'title', 'city', 'address', 'status', 'detail'
                ])->with([
                    'thumbnailImages' => fn($q) => $q->select(['id', 'asset_id', 'image'])->orderBy('id')->limit(3),
                    'defaultPricing:id,asset_id,price',
                    'type:id,name,allow_units,rental_unit,category_id',
                    'type.category:id,name,icon',
                ])
                ->withAvg('reviews as reviews_avg_rating', 'rating')
                ->withCount('reviews');
            }])
            ->latest()
            ->get();

        $transformed = $favorites->map(function ($fav) {
            $asset = $fav->asset;
            if (!$asset) return null;
            
            $asset->isFavorite = true;
            $asset->favorite_id = $fav->id;
            
            return $asset;
        })->filter()->values();

        $categoriesList = collect(['Semua'])->merge(
            \App\Models\asset_category::pluck('name')
        )->values();

        return Inertia::render('Home/Favorite', [
            'initialFavorites' => $transformed,
            'categoriesList' => $categoriesList
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
        $request->validate([
            'asset_id' => [
                'required',
                'exists:assets,id'
            ]
        ]);

        // Cegah duplikat jika request datang lebih dari sekali
        $favorite = Favorite::firstOrCreate([
            'user_id' => auth()->id(),
            'asset_id' => $request->asset_id
        ]);

        return response()->json([
            'success' => true,
            'favorite_id' => $favorite->id
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
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
    public function destroy(Favorite $favorite)
    {
        abort_if(
            $favorite->user_id !== auth()->id(),
            403
        );

        $favorite->delete();

        return response()->json(['success' => true]);
    }
}
