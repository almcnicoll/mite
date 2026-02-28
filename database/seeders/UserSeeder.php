<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            ['name' => 'Florence', 'email' => 'florencelydiaalisonmcnicoll@gmail.com', 'rotation_order' => 1, 'colour' => '#e74c3c'],
            ['name' => 'Poppy',   'email' => 'poppyrebeccalouise@gmail.com',   'rotation_order' => 2, 'colour' => '#3498db'],
            ['name' => 'Élysée', 'email' => 'elyseemcnicoll@gmail.com', 'rotation_order' => 3, 'colour' => '#2ecc71'],
            ['name' => 'Caroline', 'email' => 'carolinemcnicoll@gmail.com', 'rotation_order' => 4, 'colour' => '#fff787'],
            ['name' => 'Al', 'email' => 'almcnicoll@gmail.com', 'rotation_order' => 5, 'colour' => '#943ee4'],
        ];

        foreach ($users as $userData) {
            User::create(array_merge($userData, [
                'password' => Hash::make('password'),
            ]));
        }
    }
}