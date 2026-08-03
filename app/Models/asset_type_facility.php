<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class asset_type_facility extends Model
{
    protected $fillable = [
        'asset_type_id',
        'facility_id',
        'scope',
    ];

    public function asset_type()
    {
        return $this->belongsTo(asset_type::class, 'asset_type_id');
    }

    public function facility()
    {
        return $this->belongsTo(facility::class, 'facility_id');
    }
}
