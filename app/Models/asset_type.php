<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class asset_type extends Model
{
    protected $fillable = [
        'category_id',
        'name',
        'description',
        'is_active',
        'allow_units',
        'payment_countdown_minutes',
        'detail_fields',
        'unit_detail_fields',
    ];

    protected $casts = [
        'detail_fields' => 'array',
        'unit_detail_fields' => 'array',
    ];

    public function category()
    {
        return $this->belongsTo(asset_category::class);
    }

    public function assets()
    {
        return $this->hasMany(asset::class);
    }

    public function assetTypeFacilities()
    {
        return $this->hasMany(asset_type_facility::class, 'asset_type_id');
    }

    public function allowedFacilities()
    {
        return $this->belongsToMany(facility::class, 'asset_type_facilities')
                    ->wherePivot('scope', 'asset')
                    ->withPivot('scope')
                    ->withTimestamps();
    }

    public function allowedUnitFacilities()
    {
        return $this->belongsToMany(facility::class, 'asset_type_facilities')
                    ->wherePivot('scope', 'unit')
                    ->withPivot('scope')
                    ->withTimestamps();
    }

    public function reviewTags()
    {
        return $this->hasMany(review_tag::class);
    }

    /**
     * Get the mandatory gallery categories based on asset type name.
     */
    public function getMandatoryCategories()
    {
        $map = [
            'Hotel' => ['Tampak Depan', 'Lobby', 'Kamar', 'Kamar Mandi'],
            'Baliho' => ['Tampak Baliho', 'Area Sekitar', 'Akses Jalan'],
            'Villa' => ['Tampak Depan', 'Ruang Utama', 'Kamar', 'Kamar Mandi', 'Area Outdoor'],
            'Apartemen' => ['Tampak Gedung', 'Ruang Unit', 'Kamar', 'Kamar Mandi'],
            'Homestay' => ['Tampak Depan', 'Ruang Utama', 'Kamar', 'Kamar Mandi'],
            'Guest House' => ['Tampak Depan', 'Area Bersama', 'Kamar', 'Kamar Mandi'],
            'Kos' => ['Bangunan Kos', 'Bangunan Kos Dari Jalan', 'Kamar Mandi', 'Fasilitas Bersama'],
            'Resort' => ['Exterior', 'Lobby', 'Kamar', 'Kamar Mandi'],
            'Kontrakan' => ['Tampak Depan', 'Ruang Utama', 'Kamar', 'Kamar Mandi'],
            'Ruko' => ['Tampak Depan', 'Area Utama', 'Interior', 'Akses Parkir'],
            'Gudang' => ['Tampak Depan', 'Area Gudang', 'Akses Kendaraan', 'Loading Area'],
            'Lahan' => ['Keseluruhan Lahan', 'Akses Masuk', 'Lingkungan Sekitar'],
            'Gedung' => ['Tampak Depan', 'Ruang Utama', 'Lobby'],
            'Aula' => ['Area Utama', 'Area Masuk'],
            'Ruang Meeting' => ['Ruang Keseluruhan', 'Meja & Kursi', 'Fasilitas Presentasi', 'Area Masuk'],
            'Studio' => ['Ruang Keseluruhan', 'Area Utama', 'Peralatan'],
        ];

        return $map[$this->name] ?? [];
    }

    /**
     * Get the mandatory gallery categories for units based on asset type name.
     */
    public function getMandatoryUnitCategories()
    {
        $map = [
            'Kos' => ['Kamar Tidur', 'Depan Kamar'],
            'Hotel' => ['Kamar Tidur', 'Kamar Mandi'],
            'Apartemen' => ['Kamar Tidur', 'Ruang Unit'],
        ];

        return $map[$this->name] ?? [];
    }

    /**
     * Kategori fasilitas yang WAJIB dipilih untuk tipe aset ini (database-driven).
     */
    public function mandatoryFacilityCategories()
    {
        return $this->belongsToMany(
            facility_category::class,
            'asset_type_mandatory_categories',
            'asset_type_id',
            'facility_category_id'
        )->withTimestamps();
    }

    /**
     * Get the mandatory facility categories based on asset type name.
     * @deprecated Gunakan relasi mandatoryFacilityCategories() — hardcoded hanya sebagai fallback.
     */
    public function getMandatoryFacilityCategories()
    {
        // Prioritaskan data dari database
        $dbCategories = $this->mandatoryFacilityCategories()->pluck('name')->toArray();
        if (!empty($dbCategories)) {
            return $dbCategories;
        }

        // Fallback ke hardcoded (backward compat)
        $map = [
            'Kos'       => ['Internet', 'Parkir', 'Keamanan', 'Kamar Mandi', 'Perabot Kamar Mandi'],
            'Hotel'     => ['Internet', 'Parkir', 'Lobby', 'Kamar Mandi', 'Perabot Kamar Mandi'],
            'Apartemen' => ['Internet', 'Parkir', 'Keamanan', 'Kamar Mandi', 'Perabot Kamar Mandi'],
        ];

        return $map[$this->name] ?? [];
    }

    /**
     * Get the mandatory facility categories for units based on asset type name.
     */
    public function getMandatoryUnitFacilityCategories()
    {
        $map = [
            'Kos' => ['Perabot Kamar Mandi', 'Perlengkapan Kamar', 'Sirkulasi Udara'],
            'Hotel' => ['Perabot Kamar Mandi', 'Perlengkapan Kamar', 'Sirkulasi Udara'],
            'Apartemen' => ['Perabot Kamar Mandi', 'Dapur'],
        ];

        return $map[$this->name] ?? [];
    }
}
