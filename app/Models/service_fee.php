<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class service_fee extends Model
{
    protected $fillable = [
        'asset_type_id',
        'scope',
        'name',
        'description',
        'fee_type',
        'fee_value',
        'sort_order',
    ];

    public function assetType()
    {
        return $this->belongsTo(asset_type::class, 'asset_type_id');
    }
}
