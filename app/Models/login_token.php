<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class login_token extends Model
{
    protected $fillable = [
        'email',
        'purpose',
        'token',
        'magic_token',
        'ip_address',
        'device',
        'expired_at',
        'used_at',
    ];

    protected function casts(): array
    {
        return [
            'expired_at' => 'datetime',
            'used_at' => 'datetime',
        ];
    }
}
