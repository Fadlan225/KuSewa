<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class asset_category extends Model
{
    protected $table = 'asset_categories';

    protected $fillable = [
        'name',
        'icon',
        'allow_units',
    ];

    public function assets()
    {
        return $this->hasMany(asset::class);
    }
}
