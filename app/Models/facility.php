<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class facility extends Model
{
    protected $fillable = [
        'facility_category_id',
        'name',
        'slug',
        'is_active',
        'sort_order',
    ];

    public function facilityCategory()
    {
        return $this->belongsTo(facility_category::class, 'facility_category_id');
    }

    public function category()
    {
        return $this->belongsTo(facility_category::class, 'facility_category_id');
    }

    public function assetTypes()
    {
        return $this->belongsToMany(asset_type::class, 'asset_type_facilities')
                    ->withPivot('scope')
                    ->withTimestamps();
    }

    public function assets()
    {
        return $this->belongsToMany(asset::class, 'asset_facilities')
                    ->withTimestamps();
    }

    public function assetUnits()
    {
        return $this->belongsToMany(asset_units::class, 'asset_unit_facilities', 'facility_id', 'asset_unit_id')
                    ->withTimestamps();
    }

    public function assetUnitFacilities()
    {
        return $this->hasMany(asset_unit_facility::class);
    }

    public function assetTypeFacilities()
    {
        return $this->hasMany(asset_type_facility::class);
    }
}
