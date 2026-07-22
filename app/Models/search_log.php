<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class search_log extends Model
{
    protected $fillable = [
        'user_id',
        'keyword',
        'searched_at',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
