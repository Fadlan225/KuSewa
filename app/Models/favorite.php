<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Favorite extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'asset_id',
    ];

    /**
     * User yang menyimpan favorit.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Asset yang difavoritkan.
     */
    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }
}