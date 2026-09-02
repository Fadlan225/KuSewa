<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

class bank_account extends Model
{
    protected $fillable = [
        'owner_profile_id',
        'bank_code',
        'account_number',
        'account_holder',
        'status'
    ];

    public function ownerProfile()
    {
        return $this->belongsTo(owner_profile::class);
    }

    public function bank()
    {
        return $this->belongsTo(bank::class, 'bank_code', 'code');
    }
}
