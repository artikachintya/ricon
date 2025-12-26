<?php

namespace Database\Seeders;

use App\Models\LockerItem;
use App\Models\Notification;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class NotificationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $item = LockerItem::with('session.user')->first();

        if (
            !$item ||
            !$item->lockerSession ||
            !$item->lockerSession->user
        ) {
            return;
        }

        Notification::create([
            'user_id' => $item->lockerSession->user->id,
            'locker_item_id' => $item->id,
            'title' => 'Item added to your locker',
            'is_read' => false,
        ]);
    }
}
