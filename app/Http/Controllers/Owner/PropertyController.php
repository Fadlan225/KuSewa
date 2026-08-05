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
        $dbProperties = Property::where('user_id', Auth::id())
            ->latest()
            ->paginate(9)
            ->through(function ($item) {
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
                    'verification_status' => $item->verification_status,
                    'verification_note' => $item->verification_note,
                    'tenant' => $item->tenant,
                    'image' => $item->image ? Storage::url($item->image) : 'https://images.unsplash.com/photo-1522708323590-d24dbb6b0267?auto=format&fit=crop&w=500&q=80',
                    'occupancy' => $item->occupancy ?? ($item->status === 'Tersewa' ? 'Tersewa' : '0/1 Unit'),
                ];
            });

        return Inertia::render('owner/property/index', [
            'properties' => $dbProperties,
            'app_fee_percentage' => 5,
        ]);
    }

    /**
     * Menampilkan detail satu properti/aset milik owner
     */
    public function show(Property $property): Response
    {
        if ($property->user_id !== Auth::id()) {
            abort(403, 'Akses tidak diizinkan.');
        }

        $property->loadMissing('verifier');

        return Inertia::render('owner/property/show', [
            'property' => [
                'id' => $property->id,
                'title' => $property->title,
                'category' => $property->category,
                'type' => $property->type ?? '-',
                'price' => (float) $property->price,
                'rent_period' => $property->rent_period,
                'city' => $property->city,
                'address' => $property->address,
                'status' => $property->status,
                'verification_status' => $property->verification_status,
                'verification_note' => $property->verification_note,
                'verified_by' => $property->verifier?->name,
                'verified_at' => $property->verified_at,
                'tenant' => $property->tenant,
                'image' => $property->image ? Storage::url($property->image) : null,
                'occupancy' => $property->occupancy,
            ],
        ]);
    }

    /**
     * Menampilkan form UI tambah properti baru
     */
    public function create(): Response
    {
        return Inertia::render('owner/property/create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nama_properti' => 'required|string|max:255',
            'kategori' => 'required|string|max:255',
            'tipe_sewa' => 'required|in:Harian,Bulanan,Tahunan',
            'alamat_lengkap' => 'required|string|max:1000',
            'kota' => 'required|string|max:255',
            'kecamatan' => 'required|string|max:255',
            'harga_sewa' => 'required|numeric|min:0',
        ]);

        Property::create([
            'user_id' => Auth::id(),
            'title' => $validated['nama_properti'],
            'category' => $validated['kategori'] === 'Kos & Rumah' ? 'Kos' : $validated['kategori'],
            'price' => $validated['harga_sewa'],
            'rent_period' => match ($validated['tipe_sewa']) {
                'Harian' => 'Hari',
                'Tahunan' => 'Tahun',
                default => 'Bulan',
            },
            'city' => $validated['kota'],
            'address' => $validated['alamat_lengkap'] . ', ' . $validated['kecamatan'],
            'status' => 'Tersedia',
            'verification_status' => 'pending',
        ]);

        return redirect()->route('owner.property.index')
            ->with('success', 'Properti berhasil diajukan dan menunggu verifikasi admin.');
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
            'tenant' => 'nullable|string|max:255',
            'image' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('image')) {
            if ($property->image) {
                Storage::disk('public')->delete($property->image);
            }

            $validated['image'] = $request->file('image')->store('properties', 'public');
        }

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