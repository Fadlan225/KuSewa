<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssetView extends Model
{
    protected $table = 'asset_views';

    protected $fillable = [
        'user_id',
        'asset_id',
        'view_count',
        'last_viewed',
    ];

    protected $casts = [
        'last_viewed' => 'datetime',
    ];

    public function asset()
    {
        return $this->belongsTo(asset::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
