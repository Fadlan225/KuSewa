<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class asset_type extends Model
{
    protected $fillable = [
        'category_id',
        'name',
        'rental_unit',
        'allow_units',
    ];

    public function category()
    {
        return $this->belongsTo(asset_category::class);
    }

    public function assets()
    {
        return $this->hasMany(asset::class);
    }
}
