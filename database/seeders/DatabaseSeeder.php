<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'name'     => 'Ivan Cornelius',
                'udomain'  => 'u100001',
                'password' => bcrypt('Ivan@123'),
            ],
            [
                'name'     => 'Jason Amanda Gonidjaja',
                'udomain'  => 'u100002',
                'password' => bcrypt('Jason#789'),
            ],
            [
                'name'     => 'Kadek Artika',
                'udomain'  => 'u100003',
                'password' => bcrypt('Kadek!456'),
            ],
            [
                'name'     => 'Grace Natal Liu',
                'udomain'  => 'u100004',
                'password' => bcrypt('Rahmat$321'),
            ],
            [
                'name'     => 'Samuel Hartono',
                'udomain'  => 'u100005',
                'password' => bcrypt('Sam&999'),
            ],
        ];

        foreach ($users as $u) {
            \App\Models\User::create($u);
        }
       $this->call([
            UserSeeder::class,
            LockerSeeder::class,
            LockerSessionSeeder::class,
            LockerItemSeeder::class,
            NotificationSeeder::class,
        ]);
    }


}
