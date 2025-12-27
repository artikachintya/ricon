<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LockerSession extends Model
{
    protected $fillable = [
        'locker_id',
        'user_id',
        'assigned_taker_id',
        'taken_by',
        'status',
        'taken_at',
        'ended_at',
    ];

    protected $casts = [
        'taken_at' => 'datetime',
        'ended_at' => 'datetime',
    ];

    public function locker()
    {
        return $this->belongsTo(Locker::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function assignedTaker()
    {
        return $this->belongsTo(User::class, 'assigned_taker_id');
    }

    public function items()
    {
        return $this->hasMany(LockerItem::class, 'locker_session_id');
    }
}
