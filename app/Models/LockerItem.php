<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LockerItem extends Model
{
    protected $fillable = [
        'locker_id',
        'item_name',
        'item_detail',
        'added_at',
    ];

    protected $casts = [
        'added_at' => 'datetime',
    ];

    public function session()
    {
        return $this->belongsTo(LockerSession::class, 'locker_id');
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }
}
