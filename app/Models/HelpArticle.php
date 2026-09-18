<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HelpArticle extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function category()
    {
        return $this->belongsTo(HelpCategory::class, 'help_category_id');
    }

    public function feedbacks()
    {
        return $this->hasMany(HelpArticleFeedback::class);
    }
}
