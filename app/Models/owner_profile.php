<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class owner_profile extends Model
{
    protected $fillable = [
        'user_id',
        'national_id',
        'province_code',
        'city_code',
        'district_code',
        'village_code',
        'postal_code',
        'address',
        'ktp_photo',
        'status',
        'rejection_reason',
        'verification_at'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function bankAccounts()
    {
        return $this->hasMany(bank_account::class);
    }

    public function assets(){
        return $this->hasMany(asset::class);
    }

    public function roomChats(){
        return $this->hasMany(room_chat::class);
    }
}
