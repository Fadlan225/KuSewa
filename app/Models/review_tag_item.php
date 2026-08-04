<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class review_tag_item extends Model
{
    protected $fillable = [
        'review_id',
        'review_tag_id',
    ];

    public function review()
    {
        return $this->belongsTo(review::class);
    }

    public function reviewTag()
    {
        return $this->belongsTo(review_tag::class);
    }
}
