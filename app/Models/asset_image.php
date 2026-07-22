<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class asset_image extends Model
{
    protected $fillable = [
        'image'
    ];

    protected $appends = ['image_url'];

    public function getImageUrlAttribute(): ?string
    {
        if (!$this->image) {
            return null;
        }

        // Jika sudah URL lengkap (http/https), return langsung
        if (str_starts_with($this->image, 'http')) {
            return $this->image;
        }

        // Path assets/ → disimpan langsung di public/assets/ oleh seeder GD
        if (str_starts_with($this->image, 'assets/')) {
            return '/' . $this->image;
        }

        // Path lainnya → storage (symlink public/storage → storage/app/public)
        return '/storage/' . $this->image;
    }

    public function asset(){
        return $this->belongsTo(asset::class);
    }
}
