<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class asset_units extends Model
{
    protected $fillable = [
        'asset_id',
        'name',
        'detail',
        'quantity',
        'status',
    ];

    protected $casts = [
        'detail' => 'array',
    ];

    public function asset()
    {
        return $this->belongsTo(asset::class);
    }

    public function pricings()
    {
        return $this->hasMany(asset_pricing::class, 'asset_unit_id')->whereNotNull('asset_unit_id');
    }

    public function bookings()
    {
        return $this->hasMany(booking::class, 'asset_unit_id')->whereNotNull('asset_unit_id');
    }

    public function images()
    {
        return $this->hasMany(asset_image::class, 'asset_unit_id');
    }
}
