<?php
// database/seeders/UserSeeder.php

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run()
    {
        // Créer un admin
        User::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        // Créer un formateur
        User::create([
            'name' => 'Formateur Test',
            'email' => 'formateur@example.com',
            'password' => Hash::make('password'),
            'role' => 'formateur',
            'bio' => 'Formateur expérimenté en développement web',
        ]);

        // Créer un participant
        User::create([
            'name' => 'Participant Test',
            'email' => 'participant@example.com',
            'password' => Hash::make('password'),
            'role' => 'participant',
        ]);
    }
}
