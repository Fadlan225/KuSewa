<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable([
    'user_id',
    'title',
    'category',
    'type',
    'price',
    'rent_period',
    'city',
    'address',
    'status',
    'tenant',
    'image',
    'occupancy',
])]
class Property extends Model
{
}
