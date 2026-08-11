<?php

namespace App\Http\Requests\Owner;

use App\Models\asset_type;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAssetRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Hanya owner yang sudah memiliki profil yang boleh membuat aset
        return auth()->check() && auth()->user()->ownerProfile !== null;
    }

    public function rules(): array
    {
        $assetTypeId = $this->input('asset_type_id');
        $assetType = $assetTypeId ? asset_type::find($assetTypeId) : null;
        $allowUnits = $assetType ? (bool) $assetType->allow_units : false;

        // Validasi dasar (berlaku untuk semua jenis aset)
        $rules = [
            // --- Step 1: Informasi Aset ---
            'title'              => ['required', 'string', 'max:255'],
            'description'        => ['required', 'string', 'min:20'],
            'category_id'        => ['required', 'integer', 'exists:asset_categories,id'],
            'asset_type_id'      => ['required', 'integer', 'exists:asset_types,id'],
            'detail'             => ['nullable', 'array'],

            // Fasilitas aset (pivot asset_facilities, scope=asset)
            'facility_ids'       => ['nullable', 'array'],
            'facility_ids.*'     => ['integer', 'exists:facilities,id'],

            // --- Step 2: Lokasi ---
            'province_code'      => ['required', 'string', 'exists:provinces,code'],
            'city_code'          => ['required', 'string', 'exists:cities,code'],
            'district_code'      => ['required', 'string', 'exists:districts,code'],
            'village_code'       => ['required', 'string', 'exists:villages,code'],
            'postal_code'        => ['nullable', 'string', 'size:5'],
            'address'            => ['required', 'string', 'max:500'],
            'latitude'           => ['required', 'numeric', 'between:-90,90'],
            'longitude'          => ['required', 'numeric', 'between:-180,180'],
            'thumbnail'          => ['nullable', 'image', 'max:5120'],

            // --- Step 3: Foto ---
            'photos'             => ['nullable', 'array'],
            'photos.*.gallery_category_id' => ['required_with:photos', 'integer', 'exists:galery_categories,id'],
            'photos.*.files'     => ['required_with:photos', 'array', 'min:1'],
            'photos.*.files.*'   => ['image', 'max:5120'], // max 5MB per foto
        ];

        if ($allowUnits) {
            // Aset dengan unit — harga per unit, tidak ada harga aset langsung
            $rules['units']              = ['required', 'array', 'min:1'];
            $rules['units.*.name']       = ['required', 'string', 'max:100'];
            $rules['units.*.quantity']   = ['required', 'integer', 'min:1'];
            $rules['units.*.price']      = ['required', 'numeric', 'min:0'];
            $rules['units.*.detail']     = ['nullable', 'array'];
            $rules['units.*.facility_ids']   = ['nullable', 'array'];
            $rules['units.*.facility_ids.*'] = ['integer', 'exists:facilities,id'];
            // Thumbnail Unit
            $rules['units.*.thumbnail'] = ['nullable', 'image', 'max:5120'];

            // Foto Unit
            $rules['units.*.photos']             = ['nullable', 'array'];
            $rules['units.*.photos.*.gallery_category_id'] = ['required_with:units.*.photos', 'integer', 'exists:galery_categories,id'];
            $rules['units.*.photos.*.files']     = ['required_with:units.*.photos', 'array', 'min:1'];
            $rules['units.*.photos.*.files.*']   = ['image', 'max:5120'];
        } else {
            // Aset tanpa unit — harga langsung di aset
            $rules['price'] = ['required', 'numeric', 'min:0'];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'title.required'           => 'Nama aset wajib diisi.',
            'description.required'     => 'Deskripsi wajib diisi.',
            'description.min'          => 'Deskripsi minimal 20 karakter.',
            'category_id.required'     => 'Kategori aset wajib dipilih.',
            'category_id.exists'       => 'Kategori aset tidak valid.',
            'asset_type_id.required'   => 'Jenis aset wajib dipilih.',
            'asset_type_id.exists'     => 'Jenis aset tidak valid.',
            'province_code.required'   => 'Provinsi wajib dipilih.',
            'province_code.exists'     => 'Provinsi tidak valid.',
            'city_code.required'       => 'Kota wajib dipilih.',
            'city_code.exists'         => 'Kota tidak valid.',
            'district_code.required'   => 'Kecamatan wajib dipilih.',
            'district_code.exists'     => 'Kecamatan tidak valid.',
            'village_code.required'    => 'Kelurahan/Desa wajib dipilih.',
            'village_code.exists'      => 'Kelurahan/Desa tidak valid.',
            'postal_code.size'         => 'Kode pos harus 5 digit.',
            'address.required'         => 'Alamat lengkap wajib diisi.',
            'latitude.required'        => 'Titik lokasi di peta wajib ditentukan.',
            'longitude.required'       => 'Titik lokasi di peta wajib ditentukan.',
            'price.required'           => 'Harga sewa wajib diisi.',
            'price.min'                => 'Harga sewa tidak boleh negatif.',
            'thumbnail.image'          => 'Thumbnail harus berupa gambar (JPG, PNG, WebP).',
            'thumbnail.max'            => 'Ukuran thumbnail maksimal 5MB.',
            'units.*.thumbnail.image'  => 'Thumbnail unit harus berupa gambar (JPG, PNG, WebP).',
            'units.*.thumbnail.max'    => 'Ukuran thumbnail unit maksimal 5MB.',
            'photos.*.files.required_with'=> 'Anda telah menambahkan kategori foto aset, tetapi belum ada file yang dipilih (Atau total upload melebihi batas sistem).',
            'photos.*.files.*.image'   => 'File foto harus berupa gambar (JPG, PNG, WebP).',
            'photos.*.files.*.max'     => 'Ukuran foto maksimal 5MB per file.',
            'units.required'           => 'Tambahkan minimal 1 tipe unit.',
            'units.*.name.required'    => 'Nama tipe unit wajib diisi.',
            'units.*.quantity.required'=> 'Jumlah unit wajib diisi.',
            'units.*.price.required'   => 'Harga unit wajib diisi.',
            'units.*.photos.*.files.required_with' => 'Anda telah menambahkan kategori foto unit, tetapi belum ada file yang dipilih (Atau total upload melebihi batas sistem).',
        ];
    }
}
