<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class room_chat extends Model
{
    protected $fillable = [
        'user_id',
        'asset_id',
        'owner_profile_id'
    ];

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function asset(){
        return $this->belongsTo(asset::class);
    }

    public function ownerProfile(){
        return $this->belongsTo(owner_profile::class);
    }

    public function messages(){
        return $this->hasMany(message::class);
    }
}
