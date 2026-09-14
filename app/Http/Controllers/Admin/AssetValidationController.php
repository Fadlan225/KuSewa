<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\asset;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AssetValidationController extends Controller
{
    public function index(Request $request)
    {
        $query = asset::with([
            'ownerProfile' => function ($q) {
                $q->withCount(['assets' => fn($q) => $q->where('status', 'approved')]);
            },
            'ownerProfile.user:id,name,email,phone,profile_photo,gender,created_at',
            'type:id,name',
            'city:code,name',
            'district:code,name',
            'images',
            'pricings:id,asset_id,price,rental_unit',
        ])
        ->whereIn('status', ['pending', 'approved', 'rejected'])
        ->when($request->status && $request->status !== 'Semua', fn($q) => $q->where('status', strtolower($request->status)))
        ->when($request->type_id, fn($q) => $q->where('asset_type_id', $request->type_id))
        ->when($request->search, fn($q) => $q->where(function ($q) use ($request) {
            $q->where('title', 'like', "%{$request->search}%")
              ->orWhereHas('ownerProfile.user', fn($q2) => $q2->where('name', 'like', "%{$request->search}%"))
              ->orWhereHas('city', fn($q2) => $q2->where('name', 'like', "%{$request->search}%"));
        }))
        ->orderByRaw("FIELD(status, 'pending', 'approved', 'rejected')")
        ->orderBy('created_at', 'desc')
        ->paginate(7)
        ->withQueryString();

        $stats = [
            'pending'  => asset::where('status', 'pending')->count(),
            'approved' => asset::where('status', 'approved')->count(),
            'rejected' => asset::where('status', 'rejected')->count(),
            'total'    => asset::whereIn('status', ['pending', 'approved', 'rejected'])->count(),
        ];

        $categories = \App\Models\asset_category::with(['types:id,category_id,name'])->get();

        return Inertia::render('admin/PusatManajemen/ValidasiAset/Index', [
            'assets'     => $query,
            'stats'      => $stats,
            'categories' => $categories,
            'filters'    => $request->only(['search', 'status', 'type_id']),
        ]);
    }

    /**
     * Tampilkan detail spesifik aset.
     */
    public function show($id)
    {
        $asset = asset::with([
            'type:id,name,allow_units,category_id',
            'type.category:id,name',
            'images',
            'thumbnailImages',
            'pricings',
            'city:code,name',
            'province:code,name',
            'district:code,name',
            'village:code,name',
            'ownerProfile',
            'ownerProfile.user',
            'facilities:id,name,facility_category_id',
            'facilities.category:id,name,icon',
            'units.pricings',
            'units.images.gallery_category',
            'units.facilities:id,name,facility_category_id',
            'units.facilities.category:id,name,icon',
            'policies',
            'faqs',
            'reviews.user',
        ])->findOrFail($id);

        $nearbyPlaces = [];
        if ($asset->latitude && $asset->longitude) {
            $nearbyPlaces = \App\Services\OpenStreetMapService::getNearbyPlaces(
                $asset->latitude,
                $asset->longitude,
                $asset->id,
                3000,
                false
            );
        }

        return Inertia::render('admin/PusatManajemen/ValidasiAset/Show', [
            'asset'        => $asset,
            'nearbyPlaces' => $nearbyPlaces,
        ]);
    }

    /**
     * Setujui / publikasikan aset.
     */
    public function approve($id)
    {
        $asset = asset::findOrFail($id);
        $asset->update([
            'status'           => 'approved',
            'rejection_reason' => null,
        ]);

        return back()->with('success', "Aset \"{$asset->title}\" telah disetujui dan dipublikasikan.");
    }

    /**
     * Tolak aset dengan alasan.
     */
    public function reject(Request $request, $id)
    {
        $request->validate([
            'reason' => 'required|string|max:1000',
        ]);

        $asset = asset::findOrFail($id);
        $asset->update([
            'status'           => 'rejected',
            'rejection_reason' => $request->reason,
        ]);

        return back()->with('success', "Aset \"{$asset->title}\" telah ditolak.");
    }
}
