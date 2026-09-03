<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\asset;
use App\Models\asset_type;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AssetManagementController extends Controller
{
    public function index(Request $request)
    {
        $query = asset::with([
            'ownerProfile.user:id,name,email',
            'type:id,name',
            'city:code,name',
            'thumbnailImages',
        ])
        ->when($request->search, fn($q) => $q->where(function ($q) use ($request) {
            $q->where('title', 'like', "%{$request->search}%")
              ->orWhereHas('ownerProfile.user', fn($q2) => $q2->where('name', 'like', "%{$request->search}%"));
        }))
        ->when($request->status && $request->status !== 'Semua', fn($q) => $q->where('status', strtolower($request->status)))
        ->when($request->type && $request->type !== 'Semua', fn($q) => $q->whereHas('type', fn($q2) => $q2->where('name', $request->type)))
        ->orderBy('created_at', 'desc')
        ->paginate(15)
        ->withQueryString();

        $assetTypes = asset_type::orderBy('name')->pluck('name')->toArray();

        $stats = [
            'total'    => asset::count(),
            'approved' => asset::where('status', 'approved')->count(),
            'pending'  => asset::where('status', 'pending')->count(),
            'rejected' => asset::where('status', 'rejected')->count(),
            'draft'    => asset::where('status', 'draft')->count(),
        ];

        return Inertia::render('admin/AsetProperti', [
            'assets'     => $query,
            'assetTypes' => $assetTypes,
            'stats'      => $stats,
            'filters'    => $request->only(['search', 'status', 'type']),
        ]);
    }
}
