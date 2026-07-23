<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class owner_profile extends Model
{
    use HasFactory;

    // Menentukan nama tabel secara eksplisit
    protected $table = 'owner_profiles';

    protected $fillable = [
        'user_id',
        'national_id',
        'address',
        'place_of_birth',
        'date_of_birth',
        'ktp_photo',
        'status',
        'verification_at',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'verification_at' => 'datetime',
    ];

    /**
     * Relasi balik ke User
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}