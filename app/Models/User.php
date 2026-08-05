<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password','phone','profile_photo','date_of_birth','gender','role', 'status','last_login_at'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function ownerProfile()
    {
        return $this->hasOne(owner_profile::class);
    }

    public function bookings(){
        return $this->hasMany(booking::class);
    }

    public function favorites(){
        return $this->hasMany(favorite::class);
    }

    public function roomChats(){
        return $this->hasMany(room_chat::class);
    }

    public function reviews(){
        return $this->hasMany(review::class);
    }

    public function searchLogs(){
        return $this->hasMany(search_log::class);
    }

    public function assetViews(){
        return $this->hasMany(AssetView::class);
    }

    public function providers(){
        return $this->hasMany(auth_provider::class);
    }
}
