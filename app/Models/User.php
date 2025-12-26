<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'udomain',
        'password',
        'face_image_path',
    ];

    protected $hidden = [
        'password',
    ];

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    public function lockerSessions()
    {
        return $this->hasMany(LockerSession::class);
    }

    public function addedItems()
    {
        return $this->hasMany(LockerItem::class, 'added_by');
    }

    public function takerProfile()
    {
        return $this->hasOne(Taker::class);
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    public function isUser()
    {
        return $this->role === 'user';
    }

    public function isCourier()
    {
        return $this->role === 'courier';
    }
}
