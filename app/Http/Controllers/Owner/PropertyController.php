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
        // 1. Ambil data dari DB milik user login
        $dbProperties = Property::where('user_id', Auth::id())
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

        // 2. Data Dummy Mockup (Dipakai hanya jika DB masih kosong untuk testing UI)
        $dummyProperties = collect([
            [
                'id' => 1,
                'title' => 'Kos Exclusive Samarinda Indah #01',
                'category' => 'Kos',
                'type' => 'Putra',
                'price' => 1350000,
                'rent_period' => 'Bulan',
                'city' => 'Samarinda',
                'address' => 'Jl. M. Yamin No. 12, Kel. Gunung Kelua',
                'status' => 'Tersewa',
                'tenant' => 'Ahmad Rizky',
                'image' => 'https://images.unsplash.com/photo-1522708323590-d24dbb6b0267?auto=format&fit=crop&w=500&q=80',
                'occupancy' => '1/1 Unit'
            ],
            [
                'id' => 2,
                'title' => 'Apartemen Orchard Tower Unit B12',
                'category' => 'Apartemen',
                'type' => 'Campur',
                'price' => 3500000,
                'rent_period' => 'Bulan',
                'city' => 'Balikpapan',
                'address' => 'Jl. Jend. Sudirman No. 88',
                'status' => 'Tersedia',
                'tenant' => null,
                'image' => 'https://images.unsplash.com/photo-1502672260266-1c1ef2d93688?auto=format&fit=crop&w=500&q=80',
                'occupancy' => '0/1 Unit'
            ],
            [
                'id' => 3,
                'title' => 'Honda Innova Reborn 2.4 V AT',
                'category' => 'Kendaraan',
                'type' => 'Mobil',
                'price' => 450000,
                'rent_period' => 'Hari',
                'city' => 'Samarinda',
                'address' => 'Jl. Pemilik Aset No. 3',
                'status' => 'Tersewa',
                'tenant' => 'Budi Kurniawan',
                'image' => 'https://images.unsplash.com/photo-1549399542-7e3f8b79c341?auto=format&fit=crop&w=500&q=80',
                'occupancy' => 'Aktif'
            ],
            [
                'id' => 4,
                'title' => 'Kos Melati Clean & Cozyman #05',
                'category' => 'Kos',
                'type' => 'Putri',
                'price' => 850000,
                'rent_period' => 'Bulan',
                'city' => 'Samarinda',
                'address' => 'Jl. Pramuka 6 No. 44',
                'status' => 'Tersedia',
                'tenant' => null,
                'image' => 'https://images.unsplash.com/photo-1598928506311-c55ded91a20c?auto=format&fit=crop&w=500&q=80',
                'occupancy' => '0/1 Unit'
            ],
            [
                'id' => 5,
                'title' => 'Rumah Kontrakan Minimalis A2',
                'category' => 'Rumah Kontrakan',
                'type' => 'Pasutri',
                'price' => 25000000,
                'rent_period' => 'Tahun',
                'city' => 'Samarinda',
                'address' => 'Jl. Juanda 8 Blok B',
                'status' => 'Tersewa',
                'tenant' => 'Rava Nanda',
                'image' => 'https://images.unsplash.com/photo-1568605117036-5fe5e7bab0b7?auto=format&fit=crop&w=500&q=80',
                'occupancy' => 'Tersewa s/d Des 2026'
            ]
        ]);

        // 3. Jika DB tidak ada isinya, gunakan data dummy. Jika ada, gunakan DB asli.
        $properties = $dbProperties->isNotEmpty() ? $dbProperties : $dummyProperties;

        return Inertia::render('owner/property/index', [
            'properties' => $properties,
            'app_fee_percentage' => 5,
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
     */
    public function store(Request $request): RedirectResponse
    {
        return back()->with('success', 'Pengajuan properti berhasil disimulasikan!');
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