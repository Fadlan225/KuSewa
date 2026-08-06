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
            'property' => $this->propertyPayload($property),
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
     * Ubah status ketersediaan properti (manual dari owner)
     * Hanya dapat dilakukan jika verified & tidak sedang sanksi
     */
    public function updateStatus(Property $property, Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'status' => 'required|in:Tersedia,Tersewa,Maintenance',
        ]);

        if ($property->user_id !== Auth::id()) {
            abort(403, 'Akses tidak diizinkan.');
        }

        // Hanya bisa ubah status jika sudah verified (approved)
        if ($property->verification_status !== 'approved') {
            return back()->with('error', 'Tidak dapat mengubah status. Properti harus diverifikasi terlebih dahulu.');
        }

        $property->update(['status' => $validated['status']]);

        return back()->with('success', 'Status properti berhasil diubah menjadi ' . $validated['status']);
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
            'deposit' => 'nullable|numeric|min:0',
            'jenis_properti' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'jumlah_kamar' => 'nullable|integer|min:0',
            'kapasitas_orang' => 'nullable|integer|min:0',
            'jumlah_lantai' => 'nullable|integer|min:0',
            'luas_tanah' => 'nullable|numeric|min:0',
            'luas_bangunan' => 'nullable|numeric|min:0',
            'dimensi' => 'nullable|string|max:255',
            'fasilitas' => 'nullable|array',
            'tipe_kamar' => 'nullable|array',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);

        Property::create(array_merge([
            'user_id' => Auth::id(),
            'title' => $validated['nama_properti'],
            'category' => $validated['kategori'],
            'type' => $validated['jenis_properti'],
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
        ], $this->richPropertyData($request)));

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
            'property' => $this->propertyPayload($property),
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
            'nama_properti' => 'required|string|max:255',
            'kategori' => 'required|string',
            'tipe_sewa' => 'required|string',
            'alamat_lengkap' => 'required|string',
            'kota' => 'required|string',
            'kecamatan' => 'required|string',
            'harga_sewa' => 'required|numeric|min:0',
            'jenis_properti' => 'required|string|max:255',
            'sub_kategori_baliho' => 'nullable|string|max:255',
            'deskripsi' => 'nullable|string',
            'jumlah_kamar' => 'nullable|integer|min:0',
            'kapasitas_orang' => 'nullable|integer|min:0',
            'jumlah_lantai' => 'nullable|integer|min:0',
            'luas_tanah' => 'nullable|numeric|min:0',
            'luas_bangunan' => 'nullable|numeric|min:0',
            'dimensi' => 'nullable|string|max:255',
            'fasilitas' => 'nullable|array',
            'tipe_kamar' => 'nullable|array',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'negara' => 'nullable|string|max:255',
            'provinsi' => 'nullable|string|max:255',
            'status' => 'required|in:Tersedia,Tersewa,Maintenance',
            'tenant' => 'nullable|string|max:255',
            'deposit' => 'nullable|numeric|min:0',
        ]);

        $property->update(array_merge([
            'title' => $validated['nama_properti'],
            'category' => $validated['kategori'],
            'type' => $validated['jenis_properti'],
            'price' => $validated['harga_sewa'],
            'rent_period' => $validated['tipe_sewa'],
            'city' => $validated['kota'],
            'address' => $validated['alamat_lengkap'] . ', ' . $validated['kecamatan'],
            'verification_status' => 'pending',
        ], $this->richPropertyData($request)));

        return redirect()->route('owner.property.index')
            ->with('success', 'Data properti berhasil diperbarui!');
    }

    private function richPropertyData(Request $request): array
    {
        return [
            'property_name' => $request->input('nama_properti'),
            'property_type' => $request->input('jenis_properti'),
            'sub_category' => $request->input('sub_kategori_baliho'),
            'rental_scheme' => $request->input('tipe_sewa'),
            'description' => $request->input('deskripsi'),
            'room_count' => $request->input('jumlah_kamar'),
            'capacity' => $request->input('kapasitas_orang'),
            'floor_count' => $request->input('jumlah_lantai'),
            'land_area' => $request->input('luas_tanah'),
            'building_area' => $request->input('luas_bangunan'),
            'dimensions' => $request->input('dimensi'),
            'room_types' => $request->input('tipe_kamar', []),
            'district' => $request->input('kecamatan'),
            'country' => $request->input('negara'),
            'province' => $request->input('provinsi'),
            'latitude' => $request->input('latitude'),
            'longitude' => $request->input('longitude'),
            'facilities' => $request->input('fasilitas', []),
            'deposit' => $request->input('deposit'),
            'property_photos' => collect($request->input('foto_properti', []))->map(function ($photo) {
                return [
                    'nama_ruangan' => $photo['nama_ruangan'] ?? $photo['nama_ruangan_pilihan'] ?? '',
                    'photos' => $photo['existing_photos'] ?? [],
                ];
            })->values()->all(),
        ];
    }

    private function propertyPayload(Property $property): array
    {
        return array_merge($property->toArray(), [
            'nama_properti' => $property->property_name ?: $property->title,
            'kategori' => $property->category,
            'jenis_properti' => $property->property_type ?: $property->type ?: $property->category,
            'sub_kategori_baliho' => $property->sub_category,
            'tipe_sewa' => $property->rental_scheme ?: match ($property->rent_period) {
                'Hari' => 'Harian',
                'Tahun' => 'Tahunan',
                default => 'Bulanan',
            },
            'deskripsi' => $property->description,
            'jumlah_kamar' => $property->room_count,
            'kapasitas_orang' => $property->capacity,
            'jumlah_lantai' => $property->floor_count,
            'luas_tanah' => $property->land_area,
            'luas_bangunan' => $property->building_area,
            'dimensi' => $property->dimensions,
            'tipe_kamar' => $property->room_types ?: [],
            'alamat_lengkap' => $property->address,
            'kecamatan' => $property->district,
            'negara' => $property->country ?: 'Indonesia',
            'negara_pilihan' => $property->country ?: 'Indonesia',
            'provinsi' => $property->province ?: 'Kalimantan Timur',
            'provinsi_pilihan' => $property->province ?: 'Kalimantan Timur',
            'kota' => $property->city,
            'kota_pilihan' => $property->city,
            'latitude' => $property->latitude,
            'longitude' => $property->longitude,
            'fasilitas' => $property->facilities ?: [],
            'harga_sewa' => (float) $property->price,
            'deposit' => $property->deposit,
            'foto_properti' => collect($property->property_photos ?: [])->map(function ($photo) {
                $photos = $photo['photos'] ?? $photo['existing_photos'] ?? [];
                return [
                    'nama_ruangan' => $photo['nama_ruangan'] ?? '',
                    'photos' => collect($photos)->map(function ($path) {
                        if (is_array($path)) return $path;
                        return ['path' => $path, 'url' => Storage::url($path)];
                    })->values()->all(),
                ];
            })->values()->all(),
            'image' => $property->image ? Storage::url($property->image) : null,
            'verified_by' => $property->verifier?->name,
            'type' => $property->property_type ?: $property->type ?: $property->category,
            'occupancy' => $property->occupancy ?: ($property->room_count ? $property->room_count . ' kamar' : '-'),
        ]);
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