<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class galery_category extends Model
{
    protected $fillable = [
        'asset_type_id',
        'name',
    ];

    public function asset_images()
    {
        return $this->hasMany(asset_images::class);
    }

    public function asset_type()
    {
        return $this->belongsTo(asset_type::class);
    }
}
