<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class asset extends Model
{
    use SoftDeletes;

    protected $table = 'assets';

    protected $fillable = [
        'owner_profile_id',
        'asset_type_id',
        'title',
        'slug',
        'description',
        'detail',
        'province_code',
        'city_code',
        'district_code',
        'village_code',
        'postal_code',
        'address',
        'latitude',
        'longitude',
        'status',
        'draft_payload',
    ];

    protected $casts = [
        'detail' => 'array',
        'draft_payload' => 'array',
    ];

    public function ownerProfile(){
        return $this->belongsTo(owner_profile::class);
    }

    public function province(){
        return $this->belongsTo(province::class, 'province_code', 'code');
    }

    public function city(){
        return $this->belongsTo(city::class, 'city_code', 'code');
    }

    public function district(){
        return $this->belongsTo(district::class, 'district_code', 'code');
    }

    public function village(){
        return $this->belongsTo(village::class, 'village_code', 'code');
    }

    public function getRouteKeyName()
    {
        return 'slug';
    }

    public function images(){
        return $this->hasMany(asset_image::class);
    }

    public function faqs(){
        return $this->hasMany(asset_faq::class)->orderBy('sort_order');
    }

    public function policies(){
        return $this->hasMany(asset_policy::class)->orderBy('sort_order');
    }

    public function firstImage(){
        return $this->hasOne(asset_image::class)->orderBy('id');
    }

    public function thumbnailImages(){
        return $this->hasMany(asset_image::class)
                    ->where('is_thumbnail', true)
                    ->whereNull('asset_unit_id')
                    ->orderBy('id');
    }

    public function type(){
        return $this->belongsTo(asset_type::class, 'asset_type_id');
    }

    public function pricings(){
        return $this->hasMany(asset_pricing::class);
    }

    public function defaultPricing()
    {
        return $this->hasOne(asset_pricing::class)->orderBy('price', 'asc');
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
            'defaultPricing:id,asset_id,price,rental_unit',
            'type:id,name,allow_units,category_id',
            'city:code,name',
            'district:code,name',
            'province:code,name',
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
