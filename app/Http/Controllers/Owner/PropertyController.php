<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Property;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;

class PropertyController extends Controller
{
    /**
     * Menampilkan daftar seluruh properti/aset milik owner
     */
    public function index(): Response
    {
        $properties = Property::where('user_id', Auth::id())
            ->latest()
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'title' => $item->title,
                    'category' => $item->category,
                    'type' => $item->type ?? '-',
                    'price' => (float) $item->price,
                    'rent_period' => $item->rent_period,
                    'city' => $item->city,
                    'address' => $item->address,
                    'status' => $item->status,
                    'tenant' => $item->tenant,
                    'image' => $item->image ? Storage::url($item->image) : 'https://images.unsplash.com/photo-1522708323590-d24dbb6b0267?auto=format&fit=crop&w=500&q=80',
                    'occupancy' => $item->occupancy ?? ($item->status === 'Tersewa' ? 'Tersewa' : '0/1 Unit'),
                ];
            });

        return Inertia::render('owner/property/index', [
            'properties' => $properties,
        ]);
    }

    /**
     * Menampilkan form UI tambah properti baru
     */
    public function create(): Response
    {
        return Inertia::render('owner/property/create');
    }

    /**
     * Simulation Route untuk pengajuan form (Hanya Frontend Mockup)
     * Catatan: Jika nanti ingin simpan ke DB, alur validasi & simpan tinggal di-uncomment.
     */
    public function store(Request $request): RedirectResponse
    {
        /*
        // Opsi simpan asli ke database nantinya:
        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'category'    => 'required|string',
            'type'        => 'nullable|string',
            'price'       => 'required|numeric|min:0',
            'rent_period' => 'required|string',
            'city'        => 'required|string',
            'address'     => 'required|string',
        ]);

        Property::create([
            'user_id'     => Auth::id(),
            'title'       => $validated['title'],
            'category'    => $validated['category'],
            'type'        => $validated['type'] ?? null,
            'price'       => $validated['price'],
            'rent_period' => $validated['rent_period'],
            'city'        => $validated['city'],
            'address'     => $validated['address'],
            'status'      => 'Tersedia',
        ]);
        */

        // ✅ Perbaikan typo: 'owner.dasboard' -> 'owner.dashboard'
        return redirect()->route('owner.dashboard')
            ->with('success', 'MOCKUP UI: Pengajuan properti berhasil disimulasikan!');
    }

    /**
     * Menampilkan form edit properti
     */
    public function edit(Property $property): Response
    {
        if ($property->user_id !== Auth::id()) {
            abort(403, 'Akses tidak diizinkan.');
        }

        return Inertia::render('owner/property/edit', [
            'property' => $property,
        ]);
    }

    /**
     * Memperbarui data properti di database
     */
    public function update(Request $request, Property $property): RedirectResponse
    {
        if ($property->user_id !== Auth::id()) {
            abort(403, 'Akses tidak diizinkan.');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'category' => 'required|string',
            'type' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'rent_period' => 'required|string',
            'city' => 'required|string',
            'address' => 'required|string',
            'status' => 'required|in:Tersedia,Tersewa,Maintenance',
        ]);

        $property->update($validated);

        return redirect()->route('owner.property.index')
            ->with('success', 'Data properti berhasil diperbarui!');
    }

    /**
     * Menghapus properti dari database
     */
    public function destroy(Property $property): RedirectResponse
    {
        if ($property->user_id !== Auth::id()) {
            abort(403, 'Akses tidak diizinkan.');
        }

        if ($property->image) {
            Storage::disk('public')->delete($property->image);
        }

        $property->delete();

        return redirect()->route('owner.property.index')
            ->with('success', 'Properti berhasil dihapus!');
    }
}