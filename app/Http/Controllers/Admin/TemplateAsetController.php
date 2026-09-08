<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\asset_type;
use App\Models\galery_category;
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
                'is_mandatory' => (bool) $c->pivot->is_mandatory,
                'sort_order' => (int) $c->pivot->sort_order,
            ])->values(),
            'unit_gallery_categories' => $t->unitGalleryCategories->map(fn($c) => [
                'id' => $c->id,
                'name' => $c->name,
                'is_mandatory' => (bool) $c->pivot->is_mandatory,
                'sort_order' => (int) $c->pivot->sort_order,
            ])->values(),
        ]);

        return Inertia::render('admin/KonfigurasiAset/KategoriGaleri', [
            'assetTypes'       => $assetTypes,
            'galleryCategories' => galery_category::orderBy('name')->get(['id', 'name']),
        ]);
    }

    /**
     * Update detail_fields atau unit_detail_fields untuk satu tipe aset.
     * Payload: { scope: 'asset'|'unit', fields: [...] }
     */
    public function updateFields(Request $request, asset_type $assetType)
    {
        $data = $request->validate([
            'scope'              => 'required|in:asset,unit',
            'fields'             => 'required|array',
            'fields.*.key'       => 'required|string|max:64',
            'fields.*.label'     => 'required|string|max:128',
            'fields.*.type'      => 'required|in:text,number,select,radio,checkbox,time,room_size',
            'fields.*.required'  => 'required|boolean',
            'fields.*.options'   => 'nullable|array',
            'fields.*.options.*' => 'string|max:64',
        ]);

        if ($data['scope'] === 'asset') {
            $assetType->update(['detail_fields' => $data['fields']]);
        } else {
            $assetType->update(['unit_detail_fields' => $data['fields']]);
        }

        return back()->with('success', "Template field {$assetType->name} berhasil disimpan.");
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
        ]);

        $relation = $data['scope'] === 'asset'
            ? $assetType->galleryCategories()
            : $assetType->unitGalleryCategories();

        $syncData = [];
        foreach ($data['categories'] as $cat) {
            $syncData[$cat['id']] = [
                'scope' => $data['scope'],
                'is_mandatory' => $cat['is_mandatory'],
                'sort_order' => $cat['sort_order'],
            ];
        }

        $relation->sync($syncData);

        return back()->with('success', "Kategori galeri wajib {$assetType->name} berhasil disimpan.");
    }
}
