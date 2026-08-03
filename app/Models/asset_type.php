<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class asset_type extends Model
{
    protected $fillable = [
        'category_id',
        'name',
        'rental_unit',
        'allow_units',
    ];

    public function category()
    {
        return $this->belongsTo(asset_category::class);
    }

    public function assets()
    {
        return $this->hasMany(asset::class);
    }

    public function galery_categories()
    {
        return $this->hasMany(galery_category::class);
    }

    public function assetTypeFacilities()
    {
        return $this->hasMany(asset_type_facility::class, 'asset_type_id');
    }

    /**
     * Seluruh fasilitas yang boleh dipilih untuk level asset (scope = 'asset')
     */
    public function allowedFacilities()
    {
        return $this->belongsToMany(facility::class, 'asset_type_facilities')
                    ->wherePivot('scope', 'asset')
                    ->withPivot('scope')
                    ->withTimestamps();
    }

    /**
     * Seluruh fasilitas yang boleh dipilih untuk level unit (scope = 'unit')
     */
    public function allowedUnitFacilities()
    {
        return $this->belongsToMany(facility::class, 'asset_type_facilities')
                    ->wherePivot('scope', 'unit')
                    ->withPivot('scope')
                    ->withTimestamps();
    }
}
