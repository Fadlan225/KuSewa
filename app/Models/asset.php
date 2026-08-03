<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class asset extends Model
{
    protected $table = 'assets';

    protected $fillable = [
        'owner_profile_id',
        'asset_type_id',
        'title',
        'slug',
        'description',
        'country',
        'province',
        'city',
        'subdistrict',
        'postal_code',
        'address',
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

    public function getRouteKeyName()
    {
        return 'slug';
    }

    public function images(){
        return $this->hasMany(asset_image::class);
    }

    public function firstImage(){
        return $this->hasOne(asset_image::class)->orderBy('id');
    }

    public function thumbnailImages(){
        return $this->hasMany(asset_image::class)->orderBy('id');
    }

    public function type(){
        return $this->belongsTo(asset_type::class, 'asset_type_id');
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

    public function units(){
        return $this->hasMany(asset_units::class);
    }

    public function views(){
        return $this->hasMany(AssetView::class);
    }

    public function facilities()
    {
        return $this->belongsToMany(facility::class, 'asset_facilities')
                    ->withTimestamps();
    }

    public function scopeWithCommonRelations($query)
    {
        return $query->with([
            'thumbnailImages' => fn($q) => $q->select(['id', 'asset_id', 'image'])->orderBy('id')->limit(3),
            'defaultPricing:id,asset_id,price',
            'type:id,name,allow_units,rental_unit,category_id',
            'favorites' => function ($q) {
                if (auth()->check()) {
                    $q->select(['id', 'user_id', 'asset_id'])->where('user_id', auth()->id());
                } else {
                    $q->whereRaw('1=0');
                }
            }
        ]);
    }
}
