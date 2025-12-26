<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
     protected $fillable = [
        'user_id',
        'locker_item_id',
        'title',
        'is_read',
    ];

     protected $casts = [
        'is_read' => 'boolean',
    ];

    public function lockerItem()
    {
        return $this->belongsTo(LockerItem::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

}
