<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    // Kolom yang bisa diisi massal
    protected $fillable = [
        'name',
        'udomain',
        'password',
        'face_embedding', // Tetap dipakai kalau fitur enrollment wajah
    ];

    // Kolom yang disembunyikan saat serialisasi (misal JSON)
    protected $hidden = [
        'password',
    ];

    // RELATIONS
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

    // ROLE CHECKS
    public function isUser()
    {
        return $this->role === 'user';
    }

    public function isCourier()
    {
        return $this->role === 'courier';
    }
}
