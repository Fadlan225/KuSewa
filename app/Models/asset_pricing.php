<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class asset_pricing extends Model
{
    protected $fillable = [
        'asset_id',
        'asset_unit_id',
        'price',
    ];

    public function asset()
    {
        return $this->belongsTo(asset::class);
    }

    public function assetUnit()
    {
        return $this->belongsTo(asset_units::class, 'asset_unit_id');
    }
}
