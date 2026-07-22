<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class asset extends Model
{
    protected $table = 'assets';

    protected $fillable = [
        'owner_profile_id',
        'asset_category_id',
        'title',
        'description',
        'latitude',
        'longitude',
        'status',
    ];

    protected $casts = [
        'detail' => 'array',
    ];

    public function ownerProfile(){
        return $this->belongsTo(owner_profile::class);
    }

    public function images(){
        return $this->hasMany(asset_image::class);
    }

    public function firstImage(){
        return $this->hasOne(asset_image::class)->orderBy('id');
    }

    public function category(){
        return $this->belongsTo(asset_category::class, 'asset_category_id');
    }

    public function pricings(){
        return $this->hasMany(asset_pricing::class);
    }

    public function defaultPricing()
    {
        return $this->hasOne(asset_pricing::class)->orderBy('id');
    }

    public function bookings(){
        return $this->hasMany(booking::class);
    }

    public function reviews()
    {
        return $this->hasManyThrough(review::class, booking::class);
    }

    public function favorites(){
        return $this->hasMany(favorite::class);
    }

    public function roomChats(){
        return $this->hasMany(room_chat::class);
    }
}
