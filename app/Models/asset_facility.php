<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class asset_facility extends Pivot
{
    protected $table = 'asset_facilities';

    protected $fillable = [
        'asset_id',
        'facility_id',
    ];

    public function asset()
    {
        return $this->belongsTo(asset::class, 'asset_id');
    }

    public function facility()
    {
        return $this->belongsTo(facility::class, 'facility_id');
    }
}
