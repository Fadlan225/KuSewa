<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class review_tag extends Model
{
    protected $fillable = [
        'asset_type_id',
        'name',
    ];

    public function assetType()
    {
        return $this->belongsTo(asset_type::class);
    }

    public function reviewTagItems()
    {
        return $this->hasMany(review_tag_item::class);
    }
}
