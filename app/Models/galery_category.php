<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class galery_category extends Model
{
    protected $fillable = [
        'name',
    ];

    public function asset_images()
    {
        return $this->hasMany(asset_images::class);
    }
}
