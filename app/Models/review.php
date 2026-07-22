<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class review extends Model
{
    protected $fillable = [
        'user_id',
        'booking_id',
        'rating',
        'review'
    ];

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function booking(){
        return $this->belongsTo(booking::class);
    }
}
