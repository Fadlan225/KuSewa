<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssetViewLog extends Model
{
    protected $fillable = [
        'asset_id',
        'user_id',
        'session_id',
        'os',
        'browser',
    ];
}
