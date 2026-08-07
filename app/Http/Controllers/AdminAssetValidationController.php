<?php

namespace App\Http\Controllers;

use App\Models\asset;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AdminAssetValidationController extends Controller
{
    public function index(Request $request)
    {
        $assets = asset::with(['ownerProfile.user', 'type.category', 'images', 'pricings'])
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search')->toString();
                $query->where(function ($query) use ($search) {
                    $query->where('title', 'like', "%{$search}%")
                        ->orWhere('city', 'like', "%{$search}%")
                        ->orWhereHas('ownerProfile.user', fn ($query) => $query->where('name', 'like', "%{$search}%"));
                });
            })
            ->latest()
            ->get()
            ->map(fn (asset $item) => [
                'id' => $item->id,
                'title' => $item->title,
                'owner' => $item->ownerProfile?->user?->name ?? 'Owner tidak ditemukan',
                'location' => $item->city,
                'category' => $item->type?->category?->name ?? $item->type?->name ?? '-',
                'type' => $item->type?->name ?? '-',
                'description' => $item->description,
                'address' => $item->address,
                'province' => $item->province,
                'detail' => $item->detail,
                'images' => $item->images->map(fn ($image) => $image->image_url)->filter()->values(),
                'pricing' => $item->pricings->map(fn ($pricing) => [
                    'price' => $pricing->price ?? $pricing->amount ?? null,
                    'unit' => $pricing->rental_unit ?? $pricing->unit ?? null,
                ])->values(),
                'status' => match ($item->status) {
                    'active' => 'Disetujui',
                    'inactive' => 'Ditolak',
                    default => 'Pending',
                },
                'submitted' => optional($item->created_at)->format('d M Y'),
            ]);

        return Inertia::render('admin/ValidasiAsetPengajuan', [
            'assets' => $assets,
            'filters' => ['search' => $request->string('search')->toString()],
        ]);
    }

    public function approve(asset $asset)
    {
        $asset->update(['status' => 'active']);

        return back()->with('success', 'Aset berhasil disetujui.');
    }

    public function reject(asset $asset)
    {
        $asset->update(['status' => 'inactive']);

        return back()->with('success', 'Aset berhasil ditolak.');
    }
}
