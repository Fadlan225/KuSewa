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
    'verification_status',
    'verification_note',
    'verified_by',
    'verified_at',
    'tenant',
    'image',
    'occupancy',
    'property_name', 'property_type', 'sub_category', 'rental_scheme', 'description',
    'room_count', 'capacity', 'floor_count', 'land_area', 'building_area', 'dimensions',
    'room_types', 'district', 'country', 'province', 'latitude', 'longitude', 'facilities',
    'deposit', 'property_photos',
])]
class Property extends Model
{
    protected function casts(): array
    {
        return [
            'verified_at' => 'datetime',
            'room_types' => 'array',
            'facilities' => 'array',
            'property_photos' => 'array',
        ];
    }

    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
