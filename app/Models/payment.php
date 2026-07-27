<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class payment extends Model
{
    protected $fillable = [
        'booking_id',
        'payment_method',
        'payment_date',
        'expires_at',
        'payment_status',
        'proof_of_payment'
    ];

    public function booking(){
        return $this->belongsTo(booking::class);
    }
}
