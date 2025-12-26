<?php

namespace Database\Seeders;

use App\Models\Locker;
use App\Models\LockerSession;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LockerSessionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $locker = Locker::where('status', 'available')->first();
        LockerSession::create([
            'locker_id' => $locker->id,
            'user_id' => 1,
            // 'assigned_taker_id' => 2,
            'taken_by' => 'face_recognition',
            'status' => 'active',
        ]);

        $locker->update(['status' => 'occupied']);
    }
}
