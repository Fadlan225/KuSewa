<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OwnerVerificationLog extends Model
{
    public $timestamps = false; // hanya ada created_at

    protected $fillable = [
        'owner_profile_id',
        'actor_id',
        'action',
        'reason',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function ownerProfile()
    {
        return $this->belongsTo(owner_profile::class, 'owner_profile_id');
    }

    public function actor()
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}
