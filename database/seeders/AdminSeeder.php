<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Admin;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run()
    {
        // Создание суперадминистратора
        Admin::create([
            'name' => 'Victor Kaiatkin',
            'email' => 'kayatkin@mail.ru',
            'password' => Hash::make('password'),
            'role' => 'super_admin',
        ]);

        // Создание обычного администратора
        Admin::create([
            'name' => 'Victor Kaiatkin',
            'email' => 'kayatkin@gmail.com',
            'password' => Hash::make('password123'),
            'role' => 'admin',
        ]);

    }
}