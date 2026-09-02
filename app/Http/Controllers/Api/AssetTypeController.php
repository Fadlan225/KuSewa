<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\asset_type;
use App\Models\galery_category;
use App\Models\PolicyTemplate;
use Illuminate\Http\Request;


class AssetTypeController extends Controller
{
    /**
     * Mengembalikan daftar asset types berdasarkan category_id.
     * Digunakan untuk cascading dropdown pada form Create Asset.
     */
    public function byCategory(Request $request)
    {
        $request->validate([
            'category_id' => 'required|exists:asset_categories,id',
        ]);

        $types = asset_type::where('category_id', $request->category_id)
            ->select('id', 'name', 'allow_units')
            ->orderBy('name')
            ->get();

        return response()->json($types);
    }

    /**
     * Mengembalikan detail lengkap sebuah asset type:
     * - allow_units
     * - fasilitas yang diizinkan (scope: asset)
     * - fasilitas unit yang diizinkan (scope: unit)
     * - kategori galeri foto (GLOBAL — tidak terikat ke asset_type tertentu)
     *
     * Digunakan oleh form Create Asset untuk memuat fasilitas & kategori foto
     * secara dinamis setelah owner memilih jenis aset.
     */
    public function details($id)
    {
        $assetType = asset_type::with([
            'allowedFacilities:id,name,slug,facility_category_id',
            'allowedFacilities.category:id,name',
            'allowedUnitFacilities:id,name,slug,facility_category_id',
            'allowedUnitFacilities.category:id,name',
        ])->findOrFail($id);

        // Kategori galeri bersifat global — ambil semua, diurutkan alfabetis
        $galleryCategories = galery_category::orderBy('name')
            ->select('id', 'name')
            ->get();

        $mandatoryNames = $assetType->getMandatoryCategories();
        $mandatoryCategories = collect($mandatoryNames)->map(function ($name) use ($galleryCategories) {
            return $galleryCategories->firstWhere('name', $name);
        })->filter()->values();

        $mandatoryUnitNames = $assetType->getMandatoryUnitCategories();
        $mandatoryUnitCategories = collect($mandatoryUnitNames)->map(function ($name) use ($galleryCategories) {
            return $galleryCategories->firstWhere('name', $name);
        })->filter()->values();

        $facilityCategories = \App\Models\facility_category::orderBy('name')->select('id', 'name')->get();

        $mandatoryFacilityNames = $assetType->getMandatoryFacilityCategories();
        $mandatoryFacilityCategories = collect($mandatoryFacilityNames)->map(function ($name) use ($facilityCategories) {
            return $facilityCategories->firstWhere('name', $name);
        })->filter()->values();

        $mandatoryUnitFacilityNames = $assetType->getMandatoryUnitFacilityCategories();
        $mandatoryUnitFacilityCategories = collect($mandatoryUnitFacilityNames)->map(function ($name) use ($facilityCategories) {
            return $facilityCategories->firstWhere('name', $name);
        })->filter()->values();

        return response()->json([
            'id'                   => $assetType->id,
            'name'                 => $assetType->name,
            'allow_units'          => (bool) $assetType->allow_units,
            'facilities'           => $assetType->allowedFacilities,
            'unit_facilities'      => $assetType->allowedUnitFacilities,
            'gallery_categories'   => $galleryCategories,
            'mandatory_categories' => $mandatoryCategories,
            'mandatory_unit_categories' => $mandatoryUnitCategories,
            'mandatory_facility_categories' => $mandatoryFacilityCategories,
            'mandatory_unit_facility_categories' => $mandatoryUnitFacilityCategories,
            'detail_fields'        => is_string($assetType->detail_fields) ? json_decode($assetType->detail_fields, true) : ($assetType->detail_fields ?? []),
            'unit_detail_fields'   => is_string($assetType->unit_detail_fields) ? json_decode($assetType->unit_detail_fields, true) : ($assetType->unit_detail_fields ?? []),
            'policy_templates'     => PolicyTemplate::getGroupedForType($assetType->id),
        ]);
    }
}
