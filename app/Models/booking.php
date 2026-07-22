<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class booking extends Model
{
    protected $fillable = [
        'asset_id',
        'asset_units_id',
        'booking_code',
        'user_id',
        'start_date',
        'end_date',
        'subtotal',
        'service_fee',
        'total',
        'booking_status'
    ];

    public function asset(){
        return $this->belongsTo(asset::class);
    }

    public function assetUnits(){
        return $this->belongsTo(asset_units::class);
    }

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function payment(){
        return $this->hasOne(payment::class);
    }

    public function reviews(){
        return $this->hasOne(review::class);
    }
}
