<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Locker extends Model
{
    protected $fillable = [
        'status',
    ];

    public function sessions() {
        return $this->hasMany(LockerSession::class);
    }
}
