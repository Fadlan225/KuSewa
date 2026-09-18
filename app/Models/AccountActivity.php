<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccountActivity extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'ip_address',
        'user_agent',
        'os',
        'device_name',
        'device_type',
        'browser',
        'province_code',
        'regency_code',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
