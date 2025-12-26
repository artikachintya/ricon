<?php

namespace Database\Seeders;

use App\Models\LockerItem;
use App\Models\LockerSession;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LockerItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $session = LockerSession::where('status', 'active')->first();

        LockerItem::create([
            'locker_id' => $session->id,
            'item_name' => 'Laptop',
            'item_detail' => 'MacBook Pro 14-inch',
        ]);

        LockerItem::create([
            'locker_id' => $session->id,
            'item_name' => 'Backpack',
            'item_detail' => 'Black backpack with charger',
        ]);
    }
}
