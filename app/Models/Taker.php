<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Taker extends Model
{
     protected $fillable = [
        'user_id',
    ];

      public function user() {
        return $this->belongsTo(User::class);
    }

    public function assignedSessions() {
        return $this->hasMany(LockerSession::class, 'assigned_taker_id');
    }

    // public function takenSessions() {
    //     return $this->hasMany(LockerSession::class, 'taken_by');
    // }
}

