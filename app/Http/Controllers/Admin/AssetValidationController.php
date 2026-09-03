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
            'ownerProfile.user:id,name,email',
            'type:id,name',
            'city:code,name',
            'images',
            'pricings:id,asset_id,price,rental_unit',
        ])
        ->whereIn('status', ['pending', 'approved', 'rejected'])
        ->when($request->status && $request->status !== 'Semua', fn($q) => $q->where('status', strtolower($request->status)))
        ->when($request->search, fn($q) => $q->where(function ($q) use ($request) {
            $q->where('title', 'like', "%{$request->search}%")
              ->orWhereHas('ownerProfile.user', fn($q2) => $q2->where('name', 'like', "%{$request->search}%"))
              ->orWhereHas('city', fn($q2) => $q2->where('name', 'like', "%{$request->search}%"));
        }))
        ->orderByRaw("FIELD(status, 'pending', 'approved', 'rejected')")
        ->orderBy('created_at', 'desc')
        ->paginate(15)
        ->withQueryString();

        $stats = [
            'pending'  => asset::where('status', 'pending')->count(),
            'approved' => asset::where('status', 'approved')->count(),
            'rejected' => asset::where('status', 'rejected')->count(),
            'total'    => asset::whereIn('status', ['pending', 'approved', 'rejected'])->count(),
        ];

        return Inertia::render('admin/ValidasiAsetPengajuan', [
            'assets'  => $query,
            'stats'   => $stats,
            'filters' => $request->only(['search', 'status']),
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
