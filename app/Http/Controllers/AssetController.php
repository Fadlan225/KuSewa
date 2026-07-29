<?php

namespace App\Http\Controllers;
use App\Models\asset;
use App\Models\AssetView;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
            },
            'units.pricings',
            'units.images.gallery_category'
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

        // ==========================================
        // Tracking view — hanya untuk user login
        // ==========================================
        $assetView = null;
        if (auth()->check()) {
            $assetView = AssetView::where('user_id', auth()->id())
                ->where('asset_id', $asset->id)
                ->first();

            if ($assetView) {
                // Sudah pernah lihat — increment view_count
                $assetView->increment('view_count');
                $assetView->update(['last_viewed' => now()]);
            } else {
                // Pertama kali lihat — buat record baru
                $assetView = AssetView::create([
                    'user_id'     => auth()->id(),
                    'asset_id'    => $asset->id,
                    'view_count'  => 1,
                    'last_viewed' => now(),
                ]);
            }
        }

        $serviceFeeRecord = DB::table('service_fees')->first();
        $serviceFee = $serviceFeeRecord ? [
            'type'  => $serviceFeeRecord->fee_type,
            'value' => (float) $serviceFeeRecord->fee_value
        ] : [
            'type'  => 'percentage',
            'value' => 5
        ];

        return inertia('Home/Assets/Show', [
            'asset'      => $asset,
            'serviceFee' => $serviceFee,
            'assetView'  => $assetView,
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
