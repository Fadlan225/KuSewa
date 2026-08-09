<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\asset_type;
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
            ->select('id', 'name', 'rental_unit', 'allow_units')
            ->orderBy('name')
            ->get();

        return response()->json($types);
    }

    /**
     * Mengembalikan detail lengkap sebuah asset type:
     * - allow_units, rental_unit
     * - fasilitas yang diizinkan (scope: asset)
     * - fasilitas unit yang diizinkan (scope: unit)
     * - kategori galeri foto (applies_to: asset / unit)
     *
     * Digunakan oleh form Create Asset untuk memuat fasilitas & kategori foto
     * secara dinamis setelah owner memilih jenis aset.
     */
    public function details($id)
    {
        $assetType = asset_type::with([
            'allowedFacilities:id,name,slug',
            'allowedUnitFacilities:id,name,slug',
            'galery_categories' => function ($q) {
                $q->select('id', 'asset_type_id', 'name', 'applies_to')
                  ->orderBy('applies_to')
                  ->orderBy('name');
            },
        ])->findOrFail($id);

        return response()->json([
            'id'            => $assetType->id,
            'name'          => $assetType->name,
            'rental_unit'   => $assetType->rental_unit,
            'allow_units'   => (bool) $assetType->allow_units,
            'facilities'    => $assetType->allowedFacilities,
            'unit_facilities' => $assetType->allowedUnitFacilities,
            'gallery_categories' => $assetType->galery_categories,
            'detail_fields' => $this->getDetailFields($assetType->id),
            'unit_detail_fields' => $this->getUnitDetailFields($assetType->id),
        ]);
    }

    /**
     * Mapping template field detail aset berdasarkan asset_type_id.
     * Field ini digunakan untuk menampilkan input dinamis di Step 1.
     *
     * Format setiap field:
     * [key, label, type, required, options?]
     */
    private function getDetailFields(int $typeId): array
    {
        $type = asset_type::with('category')->find($typeId);
        $typeName = $type ? strtolower($type->name) : '';
        $categoryName = $type && $type->category ? strtolower($type->category->name) : '';

        // --- HUNIAN: kos-kosan, apartemen, rusun ---
        if (in_array($typeName, ['kos-kosan', 'apartemen', 'rusun / condominium'])) {
            return [
                ['key' => 'floor',       'label' => 'Jumlah Lantai',      'type' => 'number', 'required' => false],
                ['key' => 'building_area','label' => 'Luas Bangunan (m²)','type' => 'number', 'required' => false],
                ['key' => 'year_built',  'label' => 'Tahun Dibangun',     'type' => 'number', 'required' => false],
                ['key' => 'parking',     'label' => 'Kapasitas Parkir',   'type' => 'text',   'required' => false],
            ];
        }

        // --- HUNIAN: hotel ---
        if ($typeName === 'hotel') {
            return [
                ['key' => 'stars',        'label' => 'Bintang Hotel',     'type' => 'select', 'required' => false, 'options' => ['1','2','3','4','5']],
                ['key' => 'floor',        'label' => 'Jumlah Lantai',     'type' => 'number', 'required' => false],
                ['key' => 'building_area','label' => 'Luas Bangunan (m²)','type' => 'number', 'required' => false],
                ['key' => 'land_area',    'label' => 'Luas Tanah (m²)',   'type' => 'number', 'required' => false],
                ['key' => 'checkin',      'label' => 'Waktu Check-in',    'type' => 'time',   'required' => false],
                ['key' => 'checkout',     'label' => 'Waktu Check-out',   'type' => 'time',   'required' => false],
            ];
        }

        // --- HUNIAN: rumah tapak, villa, homestay, guest house, kontrakan ---
        if (in_array($typeName, ['rumah tapak', 'villa', 'homestay', 'guest house', 'kontrakan'])) {
            $fields = [
                ['key' => 'building_area','label' => 'Luas Bangunan (m²)', 'type' => 'number', 'required' => false],
                ['key' => 'land_area',    'label' => 'Luas Tanah (m²)',    'type' => 'number', 'required' => false],
                ['key' => 'floor',        'label' => 'Jumlah Lantai',      'type' => 'number', 'required' => false],
                ['key' => 'year_built',   'label' => 'Tahun Dibangun',     'type' => 'number', 'required' => false],
                ['key' => 'electricity',  'label' => 'Daya Listrik (VA)',  'type' => 'select', 'required' => false, 'options' => ['900','1300','2200','3500','4400','5500']],
                ['key' => 'water_source', 'label' => 'Sumber Air',         'type' => 'select', 'required' => false, 'options' => ['PDAM','Sumur Bor']],
                ['key' => 'parking',      'label' => 'Kapasitas Parkir',   'type' => 'text',   'required' => false],
                ['key' => 'capacity',     'label' => 'Kapasitas Maksimal Tamu','type' => 'number','required' => false],
            ];
            if ($typeName === 'villa') {
                $fields[] = ['key' => 'view','label' => 'Pemandangan / View','type' => 'select','required' => false,'options' => ['Pantai','Pegunungan','Hutan','Kota','Danau']];
            }
            return $fields;
        }

        // --- KOMERSIAL: ruko, kios, kantor, gedung, food court ---
        if (in_array($typeName, ['ruko (rumah toko)', 'kios / lapak pasar', 'kantor / workspace', 'gedung komersial', 'food court / booth'])) {
            $fields = [
                ['key' => 'building_area','label' => 'Luas Bangunan (m²)','type' => 'number','required' => false],
                ['key' => 'floor',        'label' => 'Jumlah Lantai',     'type' => 'number','required' => false],
                ['key' => 'electricity',  'label' => 'Daya Listrik (VA)', 'type' => 'select','required' => false,'options' => ['900','1300','2200','3500','4400','11000']],
                ['key' => 'bathroom',     'label' => 'Kamar Mandi Dalam', 'type' => 'radio', 'required' => false,'options' => ['Ya','Tidak']],
            ];
            if (in_array($typeName, ['kantor / workspace', 'gedung komersial'])) {
                $fields[] = ['key' => 'capacity',     'label' => 'Kapasitas Orang',  'type' => 'number','required' => false];
                $fields[] = ['key' => 'ceiling_height','label' => 'Tinggi Plafon (m)','type' => 'number','required' => false];
            }
            return $fields;
        }

        // --- PENYIMPANAN & INDUSTRI ---
        if (in_array($typeName, ['gudang logistik', 'pabrik / manufaktur', 'cold storage'])) {
            return [
                ['key' => 'land_area',      'label' => 'Luas Tanah (m²)',         'type' => 'number','required' => false],
                ['key' => 'building_area',  'label' => 'Luas Bangunan (m²)',       'type' => 'number','required' => false],
                ['key' => 'ceiling_height', 'label' => 'Tinggi Plafon (m)',        'type' => 'number','required' => false],
                ['key' => 'year_built',     'label' => 'Tahun Dibangun',           'type' => 'number','required' => false],
            ];
        }

        // --- TANAH & LAHAN ---
        if (in_array($typeName, ['lahan / tanah kosong', 'lahan pertanian / perkebunan'])) {
            return [
                ['key' => 'land_area',   'label' => 'Luas Tanah (m²)',        'type' => 'number','required' => true],
                ['key' => 'certificate', 'label' => 'Sertifikat Kepemilikan', 'type' => 'select','required' => false,'options' => ['SHM','HGB','AJB','Girik','Lainnya']],
                ['key' => 'terrain',     'label' => 'Kontur Tanah',           'type' => 'select','required' => false,'options' => ['Datar','Miring','Berbukit']],
            ];
        }

        // --- MEDIA IKLAN ---
        if (in_array($typeName, ['baliho / reklame', 'billboard / videotron', 'neon box / titik toko'])) {
            return [
                ['key' => 'display_type', 'label' => 'Jenis Tampilan',    'type' => 'select','required' => true,'options' => ['Konvensional','Elektronik']],
                ['key' => 'dimension',    'label' => 'Dimensi (m)',        'type' => 'text',  'required' => false],
                ['key' => 'sides',        'label' => 'Jumlah Sisi Tampil', 'type' => 'select','required' => false,'options' => ['1','2']],
                ['key' => 'orientation',  'label' => 'Orientasi',          'type' => 'select','required' => false,'options' => ['Horizontal','Vertical']],
                ['key' => 'lighting',     'label' => 'Penerangan Malam',   'type' => 'checkbox','required' => false],
                ['key' => 'resolution',   'label' => 'Resolusi Layar',     'type' => 'text',  'required' => false],
            ];
        }

        return [];
    }

    /**
     * Template field detail untuk unit (kamar/ruang).
     * Hanya berlaku jika asset_type.allow_units = true.
     */
    private function getUnitDetailFields(int $typeId): array
    {
        $type = asset_type::find($typeId);
        $typeName = $type ? strtolower($type->name) : '';

        if (in_array($typeName, ['kos-kosan', 'hotel', 'apartemen', 'rusun / condominium', 'guest house'])) {
            return [
                ['key' => 'room_size', 'label' => 'Ukuran Kamar (m²)', 'type' => 'number', 'required' => false],
                ['key' => 'bed_type',  'label' => 'Tipe Kasur',         'type' => 'select', 'required' => false, 'options' => ['Single','Double','Queen','King','Twin']],
                ['key' => 'bathroom',  'label' => 'Kamar Mandi',        'type' => 'select', 'required' => false, 'options' => ['Dalam','Luar','Bersama']],
                ['key' => 'floor',     'label' => 'Lantai',             'type' => 'number', 'required' => false],
            ];
        }

        return [];
    }
}
