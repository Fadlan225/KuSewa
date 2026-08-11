<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class asset_faq extends Model
{
    protected $fillable = [
        'asset_id',
        'question',
        'answer',
        'sort_order',
    ];

    public function asset()
    {
        return $this->belongsTo(asset::class);
    }
}
