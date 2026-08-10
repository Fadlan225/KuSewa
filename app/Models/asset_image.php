<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class asset_image extends Model
{
    protected $fillable = [
        'asset_id',
        'asset_unit_id',
        'gallery_category_id',
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

        // File seeder GD — disimpan langsung di public/assets/ (bukan via storage disk)
        // Path tersimpan: "assets/foto/nama.jpg" → URL: /assets/foto/nama.jpg
        if (str_starts_with($this->image, 'assets/')) {
            return '/' . $this->image;
        }

        // File yang diupload owner via Storage::disk('public')
        // Path tersimpan: "uploads/assets/nama.jpg" → URL: /storage/uploads/assets/nama.jpg
        // (symlink: public/storage → storage/app/public)
        return '/storage/' . $this->image;
    }

    public function asset(){
        return $this->belongsTo(asset::class);
    }

    public function gallery_category()
    {
        return $this->belongsTo(galery_category::class);
    }
}
