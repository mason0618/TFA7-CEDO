<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            ['username' => 'marco.dc', 'full_name' => 'Marco Dela Cruz'],
            ['username' => 'jessa.r', 'full_name' => 'Jessa Ramos'],
            ['username' => 'carlo.m', 'full_name' => 'Carlo Mendoza'],
            ['username' => 'lara.a', 'full_name' => 'Lara Aquino'],
            ['username' => 'rico.n', 'full_name' => 'Rico Navarro'],
        ];

        foreach ($users as $user) {
            User::updateOrCreate(['username' => $user['username']], $user);
        }
    }
}
