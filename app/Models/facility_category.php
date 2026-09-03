<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class facility_category extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'icon',
        'sort_order',
        'is_active',
    ];

    public function facilities()
    {
        return $this->hasMany(facility::class, 'facility_category_id');
    }
}
