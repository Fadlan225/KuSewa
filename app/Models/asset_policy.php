<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class asset_policy extends Model
{
    protected $fillable = [
        'asset_id',
        'title',
        'description',
        'sort_order',
    ];

    public function asset()
    {
        return $this->belongsTo(asset::class);
    }
}
