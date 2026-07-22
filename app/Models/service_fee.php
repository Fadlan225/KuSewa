<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class service_fee extends Model
{
    protected $fillable = [
        'fee_type',
        'fee_value',
    ];

}
