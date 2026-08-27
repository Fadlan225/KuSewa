<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class asset_type extends Model
{
    protected $fillable = [
        'category_id',
        'name',
        'allow_units',
        'payment_countdown_minutes',
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
            'Kos' => ['Tampak Depan', 'Kamar', 'Kamar Mandi', 'Area Bersama'],
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
}
