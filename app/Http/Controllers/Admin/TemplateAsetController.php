<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\asset_type;
use App\Models\galery_category;
use App\Services\DefaultSpecService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TemplateAsetController extends Controller
{
    /**
     * Tampilkan halaman Spesifikasi Aset.
     */
    public function spesifikasiAset()
    {
        $assetTypes = asset_type::with([
            'category:id,name',
        ])->orderBy('name')->get()->map(fn($t) => [
            'id'                => $t->id,
            'name'              => $t->name,
            'category'          => $t->category?->name,
            'allow_units'       => (bool) $t->allow_units,
            'detail_fields'     => $t->detail_fields     ?? [],
            'unit_detail_fields' => $t->unit_detail_fields ?? [],
        ]);

        return Inertia::render('admin/KonfigurasiAset/SpesifikasiAset', [
            'assetTypes'       => $assetTypes,
        ]);
    }

    /**
     * Tampilkan halaman Kategori Galeri.
     */
    public function kategoriGaleri()
    {
        $assetTypes = asset_type::with([
            'category:id,name',
            'galleryCategories:id,name',
            'unitGalleryCategories:id,name',
        ])->orderBy('name')->get()->map(fn($t) => [
            'id'                => $t->id,
            'name'              => $t->name,
            'category'          => $t->category?->name,
            'allow_units'       => (bool) $t->allow_units,
            'gallery_categories' => $t->galleryCategories->map(fn($c) => [
                'id' => $c->id,
                'name' => $c->name,
                'description' => $c->pivot->description,
                'is_mandatory' => (bool) $c->pivot->is_mandatory,
                'sort_order' => (int) $c->pivot->sort_order,
                'min_photos' => (int) $c->pivot->min_photos,
                'max_photos' => $c->pivot->max_photos !== null ? (int) $c->pivot->max_photos : null,
            ])->values(),
            'unit_gallery_categories' => $t->unitGalleryCategories->map(fn($c) => [
                'id' => $c->id,
                'name' => $c->name,
                'description' => $c->pivot->description,
                'is_mandatory' => (bool) $c->pivot->is_mandatory,
                'sort_order' => (int) $c->pivot->sort_order,
                'min_photos' => (int) $c->pivot->min_photos,
                'max_photos' => $c->pivot->max_photos !== null ? (int) $c->pivot->max_photos : null,
            ])->values(),
        ]);

        return Inertia::render('admin/KonfigurasiAset/KategoriGaleri', [
            'assetTypes'       => $assetTypes,
            'galleryCategories' => galery_category::orderBy('name')->get(['id', 'name', 'description']),
        ]);
    }

    /**
     * Update detail_fields atau unit_detail_fields untuk satu tipe aset.
     * Payload: { scope: 'asset'|'unit', fields: [...] }
     */
    public function updateFields(Request $request, asset_type $assetType)
    {
        $validated = $request->validate([
            'scope'                                     => 'required|in:asset,unit',
            'fields'                                    => 'required|array',
            'fields.*.key'                              => 'required|string|max:64',
            'fields.*.label'                            => 'required|string|max:128',
            'fields.*.type'                             => 'required|in:text,textarea,number,counter,select,searchable_select,radio,checkbox_list,checkbox,date,time,room_size',
            'fields.*.required'                         => 'required|boolean',
            'fields.*.options'                          => 'nullable|array',
            'fields.*.options.*'                        => 'string|max:64',
            // option_configs: konfigurasi input tambahan per opsi (mis. "Lainnya" pada Apartemen)
            'fields.*.option_configs'                   => 'nullable|array',
            'fields.*.option_configs.*.has_input'       => 'boolean',
            'fields.*.option_configs.*.label'           => 'nullable|string|max:128',
            'fields.*.option_configs.*.type'            => 'nullable|in:text,number,decimal',
            'fields.*.option_configs.*.placeholder'     => 'nullable|string|max:255',
        ]);

        // Ambil fields tervalidasi dari request langsung (validator hanya memvalidasi kunci yang ada,
        // namun kita perlu meneruskan semua kunci termasuk option_configs ke penyimpanan)
        $fields = collect($request->input('fields', []))->map(function ($field) use ($validated) {
            // Pastikan hanya kunci yang diizinkan yang disimpan
            $allowed = ['key', 'label', 'type', 'required', 'options', 'option_configs', 'placeholder'];
            $clean   = array_intersect_key($field, array_flip($allowed));

            // Pastikan option_configs hanya untuk field tipe select
            if (($clean['type'] ?? '') !== 'select') {
                unset($clean['option_configs']);
            }

            return $clean;
        })->values()->all();

        if ($validated['scope'] === 'asset') {
            $assetType->update(['detail_fields' => $fields]);
        } else {
            $assetType->update(['unit_detail_fields' => $fields]);
        }

        return back()->with('success', "Konfigurasi form {$assetType->name} berhasil disimpan.");
    }

    /**
     * Reset detail_fields / unit_detail_fields ke konfigurasi default (dari DefaultSpecService).
     * Payload: { scope: 'asset'|'unit' }
     * Response JSON: { fields: [...], message: '...' }
     */
    public function resetFields(Request $request, asset_type $assetType)
    {
        $request->validate(['scope' => 'required|in:asset,unit']);

        $spec = DefaultSpecService::forType($assetType->name);

        if (!$spec) {
            return response()->json([
                'message' => "Konfigurasi default untuk tipe '{$assetType->name}' tidak tersedia.",
            ], 404);
        }

        $scope  = $request->input('scope');
        $fields = $scope === 'asset' ? $spec['asset'] : $spec['unit'];

        if ($scope === 'asset') {
            $assetType->update(['detail_fields' => $fields]);
        } else {
            $assetType->update(['unit_detail_fields' => $fields]);
        }

        return response()->json([
            'message' => "Spesifikasi {$assetType->name} berhasil direset ke default.",
            'fields'  => $fields,
        ]);
    }

    /**
     * Sync gallery categories untuk satu tipe aset.
     * Payload: { scope: 'asset'|'unit', categories: [ { id, is_mandatory, sort_order }, ... ] }
     */
    public function syncGallery(Request $request, asset_type $assetType)
    {
        $data = $request->validate([
            'scope'                     => 'required|in:asset,unit',
            'categories'                => 'present|array',
            'categories.*.id'           => 'required|integer|exists:galery_categories,id',
            'categories.*.is_mandatory' => 'required|boolean',
            'categories.*.sort_order'   => 'required|integer',
            'categories.*.description'  => 'nullable|string',
            'categories.*.min_photos'   => 'nullable|integer|min:0',
            'categories.*.max_photos'   => 'nullable|integer|min:0',
        ]);

        $relation = $data['scope'] === 'asset'
            ? $assetType->galleryCategories()
            : $assetType->unitGalleryCategories();

        $syncData = [];
        $existingCategories = \App\Models\galery_category::whereIn('name', ['Sampul Utama', 'Lainnya'])->get()->keyBy('name');
        $sampulUtamaId = $existingCategories->get('Sampul Utama')?->id;
        $lainnyaId = $existingCategories->get('Lainnya')?->id;

        $hasSampul = false;
        $hasLainnya = false;

        foreach ($data['categories'] as $cat) {
            if ($cat['id'] == $sampulUtamaId) {
                $hasSampul = true;
                $cat['is_mandatory'] = true;
            }
            if ($cat['id'] == $lainnyaId) {
                $hasLainnya = true;
                $cat['is_mandatory'] = false;
            }

            $syncData[$cat['id']] = [
                'scope' => $data['scope'],
                'is_mandatory' => $cat['is_mandatory'],
                'sort_order' => $cat['sort_order'],
                'description' => $cat['description'] ?? null,
                'min_photos' => $cat['min_photos'] ?? 0,
                'max_photos' => $cat['max_photos'] ?? null,
            ];
        }

        // Enforce defaults if missing
        if (!$hasSampul && $sampulUtamaId) {
            $syncData[$sampulUtamaId] = [
                'scope' => $data['scope'],
                'is_mandatory' => true,
                'sort_order' => 0,
                'min_photos' => 1,
                'max_photos' => null,
            ];
        }

        if (!$hasLainnya && $lainnyaId) {
            $syncData[$lainnyaId] = [
                'scope' => $data['scope'],
                'is_mandatory' => false,
                'sort_order' => 9999,
                'min_photos' => 0,
                'max_photos' => null,
            ];
        }

        $relation->sync($syncData);

        return back()->with('success', "Kategori galeri wajib {$assetType->name} berhasil disimpan.");
    }
}
