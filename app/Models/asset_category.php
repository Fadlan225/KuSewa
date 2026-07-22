<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class asset_category extends Model
{
    protected $table = 'asset_categories';

    protected $fillable = [
        'name',
        'icon',
    ];

    // Category → Types (direct children)
    public function types()
    {
        return $this->hasMany(asset_type::class, 'category_id');
    }

    // Category → Assets (via types, untuk whereHas)
    public function assets()
    {
        return $this->hasManyThrough(
            asset::class,
            asset_type::class,
            'category_id',  // FK di asset_types
            'asset_type_id' // FK di assets
        );
    }
}
