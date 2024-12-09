<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            [
                'name' => 'Muhammad Bintang',
                'email' => 'muhbintang650@gmail.com',
                'role' => 'admin',
                'password' => bcrypt('bintang123'),
            ],
            [
                'name' => 'Fery Fadul Rahman',
                'email' => 'feryfadulrahman@gmail.com',
                'role' => 'admin',
                'password' => bcrypt('fery123'),
            ]
        ];

        foreach ($users as $user) {
            User::create($user);
        }
    }
}
