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
        'detail_fields'      => 'array',
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

    // ── Mandatory Facility Categories ─────────────────────────────────────────

    /** Kategori fasilitas wajib aset. */
    public function mandatoryFacilityCategories()
    {
        return $this->belongsToMany(
            facility_category::class,
            'asset_type_mandatory_categories',
            'asset_type_id',
            'facility_category_id'
        )->wherePivot('scope', 'asset')
         ->wherePivot('is_mandatory', true)
         ->withPivot('scope', 'is_mandatory')
         ->withTimestamps();
    }

    /** Kategori fasilitas opsional aset. */
    public function optionalFacilityCategories()
    {
        return $this->belongsToMany(
            facility_category::class,
            'asset_type_mandatory_categories',
            'asset_type_id',
            'facility_category_id'
        )->wherePivot('scope', 'asset')
         ->wherePivot('is_mandatory', false)
         ->withPivot('scope', 'is_mandatory')
         ->withTimestamps();
    }

    /** Semua kategori fasilitas aset (wajib & opsional). */
    public function allFacilityCategories()
    {
        return $this->belongsToMany(
            facility_category::class,
            'asset_type_mandatory_categories',
            'asset_type_id',
            'facility_category_id'
        )->wherePivot('scope', 'asset')
         ->withPivot('scope', 'is_mandatory')
         ->withTimestamps();
    }

    /** Kategori fasilitas wajib unit. */
    public function mandatoryUnitFacilityCategories()
    {
        return $this->belongsToMany(
            facility_category::class,
            'asset_type_mandatory_categories',
            'asset_type_id',
            'facility_category_id'
        )->wherePivot('scope', 'unit')->withPivot('scope')->withTimestamps();
    }

    // ── Mandatory Gallery Categories ──────────────────────────────────────────

    /** Kategori galeri yang diizinkan untuk aset (bisa wajib/opsional). */
    public function galleryCategories()
    {
        return $this->belongsToMany(
            galery_category::class,
            'asset_type_gallery_categories',
            'asset_type_id',
            'galery_category_id'
        )->wherePivot('scope', 'asset')
         ->withPivot('scope', 'is_mandatory', 'sort_order')
         ->orderByPivot('sort_order', 'asc')
         ->withTimestamps();
    }

    /** Kategori galeri yang diizinkan untuk unit (bisa wajib/opsional). */
    public function unitGalleryCategories()
    {
        return $this->belongsToMany(
            galery_category::class,
            'asset_type_gallery_categories',
            'asset_type_id',
            'galery_category_id'
        )->wherePivot('scope', 'unit')
         ->withPivot('scope', 'is_mandatory', 'sort_order')
         ->orderByPivot('sort_order', 'asc')
         ->withTimestamps();
    }

    // ── Backward-compat helpers (DB-driven, tanpa hardcode) ───────────────────

    /** @deprecated Gunakan relasi mandatoryFacilityCategories() */
    public function getMandatoryFacilityCategories(): array
    {
        return $this->mandatoryFacilityCategories()->pluck('name')->toArray();
    }

    /** @deprecated Gunakan relasi mandatoryUnitFacilityCategories() */
    public function getMandatoryUnitFacilityCategories(): array
    {
        return $this->mandatoryUnitFacilityCategories()->pluck('name')->toArray();
    }

    /** @deprecated Gunakan relasi galleryCategories() dengan kondisi pivot */
    public function getMandatoryCategories(): array
    {
        return $this->galleryCategories()->wherePivot('is_mandatory', true)->pluck('name')->toArray();
    }

    /** @deprecated Gunakan relasi unitGalleryCategories() dengan kondisi pivot */
    public function getMandatoryUnitCategories(): array
    {
        return $this->unitGalleryCategories()->wherePivot('is_mandatory', true)->pluck('name')->toArray();
    }
}
