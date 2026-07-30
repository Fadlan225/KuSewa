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
])]
class Property extends Model
{
    protected function casts(): array
    {
        return [
            'verified_at' => 'datetime',
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
