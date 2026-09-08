<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\asset_category;
use App\Models\asset_type;
use App\Models\facility_category;
use App\Models\facility;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

class KategoriFasilitasController extends Controller
{
    // ═══════════════════════════════════════════════════════════════════════════
    // INDEX — kirim semua data ke halaman
    // ═══════════════════════════════════════════════════════════════════════════

    public function kategoriTipe()
    {
        return Inertia::render('admin/KonfigurasiAset/KategoriTipeAset', [
            'kategoriAset'      => asset_category::orderBy('name')->get(['id','name','description','icon','is_active']),
            'jenisAset'         => asset_type::with('category:id,name')->orderBy('name')->get(['id','category_id','name','description','is_active']),
        ]);
    }

    public function fasilitas()
    {
        return Inertia::render('admin/KonfigurasiAset/KategoriFasilitas', [
            'jenisAset'         => asset_type::with('category:id,name')->orderBy('name')->get(['id','category_id','name','description','is_active','allow_units']),
            'kategoriFasilitas' => facility_category::with('facilities')->orderBy('sort_order')->orderBy('name')->get(['id','name','slug','sort_order','is_active']),
            // Tipe aset beserta kategori fasilitas wajib yang sudah dipilih
            'tipeAsetMandatory' => asset_type::with([
                                            'mandatoryFacilityCategories:id,name',
                                            'optionalFacilityCategories:id,name',
                                            'mandatoryUnitFacilityCategories:id,name',
                                            'optionalUnitFacilityCategories:id,name'
                                        ])
                                        ->orderBy('name')
                                        ->get(['id','name','description','allow_units'])
                                        ->map(fn($t) => [
                                            'id'          => $t->id,
                                            'name'        => $t->name,
                                            'description' => $t->description,
                                            'allow_units' => $t->allow_units,
                                            'mandatory_ids' => $t->mandatoryFacilityCategories->pluck('id')->values(),
                                            'mandatory_names' => $t->mandatoryFacilityCategories->pluck('name')->values(),
                                            'optional_ids' => $t->optionalFacilityCategories->pluck('id')->values(),
                                            'optional_names' => $t->optionalFacilityCategories->pluck('name')->values(),
                                            'unit_mandatory_ids' => $t->mandatoryUnitFacilityCategories->pluck('id')->values(),
                                            'unit_mandatory_names' => $t->mandatoryUnitFacilityCategories->pluck('name')->values(),
                                            'unit_optional_ids' => $t->optionalUnitFacilityCategories->pluck('id')->values(),
                                            'unit_optional_names' => $t->optionalUnitFacilityCategories->pluck('name')->values(),
                                        ]),
        ]);
    }

    // ═══════════════════════════════════════════════════════════════════════════
    // KATEGORI ASET
    // ═══════════════════════════════════════════════════════════════════════════

    public function storeKategoriAset(Request $request)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:100|unique:asset_categories,name',
            'description' => 'nullable|string|max:255',
            'is_active'   => 'boolean',
        ]);

        asset_category::create($data);
        return back()->with('success', "Kategori Aset \"{$data['name']}\" berhasil ditambahkan.");
    }

    public function updateKategoriAset(Request $request, asset_category $kategoriAset)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:100|unique:asset_categories,name,' . $kategoriAset->id,
            'description' => 'nullable|string|max:255',
            'is_active'   => 'boolean',
        ]);

        $kategoriAset->update($data);
        return back()->with('success', "Kategori Aset \"{$data['name']}\" berhasil diperbarui.");
    }

    public function toggleKategoriAset(asset_category $kategoriAset)
    {
        $kategoriAset->update(['is_active' => !$kategoriAset->is_active]);
        $status = $kategoriAset->is_active ? 'diaktifkan' : 'dinonaktifkan';
        return back()->with('success', "Kategori Aset \"{$kategoriAset->name}\" berhasil {$status}.");
    }

    public function destroyKategoriAset(asset_category $kategoriAset)
    {
        // Cek apakah ada jenis aset yang menggunakan kategori ini
        if ($kategoriAset->types()->count() > 0) {
            return back()->with('error', "Tidak dapat menghapus \"{$kategoriAset->name}\" karena masih memiliki jenis aset terkait.");
        }

        $name = $kategoriAset->name;
        $kategoriAset->delete();
        return back()->with('success', "Kategori Aset \"{$name}\" berhasil dihapus.");
    }

    // ═══════════════════════════════════════════════════════════════════════════
    // JENIS ASET (asset_type)
    // ═══════════════════════════════════════════════════════════════════════════

    public function storeJenisAset(Request $request)
    {
        $data = $request->validate([
            'category_id' => 'required|exists:asset_categories,id',
            'name'        => 'required|string|max:100',
            'description' => 'nullable|string|max:255',
            'is_active'   => 'boolean',
        ]);

        asset_type::create($data);
        return back()->with('success', "Jenis Aset \"{$data['name']}\" berhasil ditambahkan.");
    }

    public function updateJenisAset(Request $request, asset_type $jenisAset)
    {
        $data = $request->validate([
            'category_id' => 'required|exists:asset_categories,id',
            'name'        => 'required|string|max:100',
            'description' => 'nullable|string|max:255',
            'is_active'   => 'boolean',
        ]);

        $jenisAset->update($data);
        return back()->with('success', "Jenis Aset \"{$data['name']}\" berhasil diperbarui.");
    }

    public function toggleJenisAset(asset_type $jenisAset)
    {
        $jenisAset->update(['is_active' => !$jenisAset->is_active]);
        $status = $jenisAset->is_active ? 'diaktifkan' : 'dinonaktifkan';
        return back()->with('success', "Jenis Aset \"{$jenisAset->name}\" berhasil {$status}.");
    }

    public function destroyJenisAset(asset_type $jenisAset)
    {
        if ($jenisAset->assets()->count() > 0) {
            return back()->with('error', "Tidak dapat menghapus \"{$jenisAset->name}\" karena masih ada aset yang menggunakan jenis ini.");
        }

        $name = $jenisAset->name;
        $jenisAset->delete();
        return back()->with('success', "Jenis Aset \"{$name}\" berhasil dihapus.");
    }

    // ═══════════════════════════════════════════════════════════════════════════
    // KATEGORI FASILITAS
    // ═══════════════════════════════════════════════════════════════════════════

    public function storeKategoriFasilitas(Request $request)
    {
        $data = $request->validate([
            'name'      => 'required|string|max:100|unique:facility_categories,name',
            'is_active' => 'boolean',
        ]);

        $data['slug']       = Str::slug($data['name']);
        $data['sort_order'] = facility_category::max('sort_order') + 1;

        facility_category::create($data);
        return back()->with('success', "Kategori Fasilitas \"{$data['name']}\" berhasil ditambahkan.");
    }

    public function updateKategoriFasilitas(Request $request, facility_category $kategoriFasilitas)
    {
        $data = $request->validate([
            'name'      => 'required|string|max:100|unique:facility_categories,name,' . $kategoriFasilitas->id,
            'is_active' => 'boolean',
        ]);

        $data['slug'] = Str::slug($data['name']);
        $kategoriFasilitas->update($data);
        return back()->with('success', "Kategori Fasilitas \"{$data['name']}\" berhasil diperbarui.");
    }

    public function toggleKategoriFasilitas(facility_category $kategoriFasilitas)
    {
        $kategoriFasilitas->update(['is_active' => !$kategoriFasilitas->is_active]);
        $status = $kategoriFasilitas->is_active ? 'diaktifkan' : 'dinonaktifkan';
        return back()->with('success', "Kategori Fasilitas \"{$kategoriFasilitas->name}\" berhasil {$status}.");
    }

    public function destroyKategoriFasilitas(facility_category $kategoriFasilitas)
    {
        if ($kategoriFasilitas->facilities()->count() > 0) {
            return back()->with('error', "Tidak dapat menghapus \"{$kategoriFasilitas->name}\" karena masih memiliki fasilitas terkait.");
        }

        $name = $kategoriFasilitas->name;
        $kategoriFasilitas->delete();
        return back()->with('success', "Kategori Fasilitas \"{$name}\" berhasil dihapus.");
    }

    // ═══════════════════════════════════════════════════════════════════════════
    // KATEGORI WAJIB PER TIPE ASET
    // ═══════════════════════════════════════════════════════════════════════════

    /**
     * Simpan/update kategori fasilitas wajib untuk satu tipe aset.
     * Menerima array facility_category_ids dan melakukan sync.
     */
    public function syncMandatoryCategories(Request $request, asset_type $assetType)
    {
        $data = $request->validate([
            'scope'                   => 'nullable|string|in:asset,unit',
            'facility_category_ids'   => 'nullable|array',
            'facility_category_ids.*' => 'integer|exists:facility_categories,id',
            'optional_category_ids'   => 'nullable|array',
            'optional_category_ids.*' => 'integer|exists:facility_categories,id',
        ]);

        $scope = $data['scope'] ?? 'asset';

        $syncData = [];
        if (!empty($data['facility_category_ids'])) {
            foreach ($data['facility_category_ids'] as $id) {
                $syncData[$id] = ['scope' => $scope, 'is_mandatory' => true];
            }
        }
        if (!empty($data['optional_category_ids'])) {
            foreach ($data['optional_category_ids'] as $id) {
                if (!isset($syncData[$id])) {
                    $syncData[$id] = ['scope' => $scope, 'is_mandatory' => false];
                }
            }
        }

        // Hapus mapping untuk scope ini saja agar scope lain tidak terpengaruh
        \DB::table('asset_type_mandatory_categories')
            ->where('asset_type_id', $assetType->id)
            ->where('scope', $scope)
            ->delete();

        if (!empty($syncData)) {
            $assetType->allFacilityCategories()->attach($syncData);
        }

        return back()->with('success', "Kategori fasilitas untuk tipe aset \"" . $assetType->name . "\" berhasil disimpan.");
    }


    public function storeJenisFasilitas(Request $request)
    {
        $data = $request->validate([
            'facility_category_id' => 'required|exists:facility_categories,id',
            'name'                 => 'required|string|max:100|unique:facilities,name',
            'description'          => 'nullable|string|max:255',
            'is_active'            => 'boolean',
        ]);

        $data['slug']       = Str::slug($data['name']);
        $data['sort_order'] = facility::where('facility_category_id', $data['facility_category_id'])->max('sort_order') + 1;

        facility::create($data);
        return back()->with('success', "Fasilitas \"{$data['name']}\" berhasil ditambahkan.");
    }

    public function updateJenisFasilitas(Request $request, facility $jenisFasilitas)
    {
        $data = $request->validate([
            'facility_category_id' => 'required|exists:facility_categories,id',
            'name'                 => 'required|string|max:100|unique:facilities,name,' . $jenisFasilitas->id,
            'description'          => 'nullable|string|max:255',
            'is_active'            => 'boolean',
        ]);

        $data['slug'] = Str::slug($data['name']);
        $jenisFasilitas->update($data);
        return back()->with('success', "Fasilitas \"{$data['name']}\" berhasil diperbarui.");
    }

    public function toggleJenisFasilitas(facility $jenisFasilitas)
    {
        $jenisFasilitas->update(['is_active' => !$jenisFasilitas->is_active]);
        $status = $jenisFasilitas->is_active ? 'diaktifkan' : 'dinonaktifkan';
        return back()->with('success', "Fasilitas \"{$jenisFasilitas->name}\" berhasil {$status}.");
    }

    public function destroyJenisFasilitas(facility $jenisFasilitas)
    {
        $name = $jenisFasilitas->name;
        $jenisFasilitas->delete();
        return back()->with('success', "Fasilitas \"{$name}\" berhasil dihapus.");
    }
}
