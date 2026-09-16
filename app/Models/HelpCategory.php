<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HelpCategory extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function parent()
    {
        return $this->belongsTo(HelpCategory::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(HelpCategory::class, 'parent_id');
    }

    public function articles()
    {
        return $this->hasMany(HelpArticle::class);
    }
}
