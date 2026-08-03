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

    public function types()
    {
        return $this->hasMany(asset_type::class, 'category_id');
    }

    public function assets()
    {
        return $this->hasManyThrough(
            asset::class,
            asset_type::class,
            'category_id',
            'asset_type_id'
        );
    }
}
