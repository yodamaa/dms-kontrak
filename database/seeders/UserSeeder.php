<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run()
    {
        User::create([
            'name' => 'Supervisor',
            'email' => 'supervisor@mail.com',
            'password' => Hash::make('supervisor'),
            'role' => 'supervisor',
        ]);

        User::create([
            'name' => 'Staff',
            'email' => 'staff@mail.com',
            'password' => Hash::make('staff123'),
            'role' => 'staff',
        ]);
    }
}
