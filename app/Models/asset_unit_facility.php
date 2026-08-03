<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class asset_unit_facility extends Pivot
{
    protected $table = 'asset_unit_facilities';

    protected $fillable = [
        'asset_unit_id',
        'facility_id',
    ];

    public function asset_unit()
    {
        return $this->belongsTo(asset_units::class, 'asset_unit_id');
    }

    public function facility()
    {
        return $this->belongsTo(facility::class, 'facility_id');
    }
}
