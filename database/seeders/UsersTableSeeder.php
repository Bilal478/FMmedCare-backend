<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UsersTableSeeder extends Seeder
{
    public function run()
    {
        $users = [
            [
                'name' => 'Super Admin',
                'email' => 'superadmin@healthcare.com',
                'password' => Hash::make('password123'),
                'role' => 'admin',
                'email_verified_at' => now(),
                'remember_token' => Str::random(10),
            ],
            [
                'name' => 'Dr. John Smith',
                'email' => 'john.smith@healthcare.com',
                'password' => Hash::make('password123'),
                'role' => 'admin',
                'email_verified_at' => now(),
                'remember_token' => Str::random(10),
            ],
            [
                'name' => 'Dr. Sarah Johnson',
                'email' => 'sarah.johnson@healthcare.com',
                'password' => Hash::make('password123'),
                'role' => 'admin',
                'email_verified_at' => now(),
                'remember_token' => Str::random(10),
            ],
            [
                'name' => 'Medical Assistant',
                'email' => 'assistant@healthcare.com',
                'password' => Hash::make('password123'),
                'role' => 'user',
                'email_verified_at' => now(),
                'remember_token' => Str::random(10),
            ],
            [
                'name' => 'Billing Specialist',
                'email' => 'billing@healthcare.com',
                'password' => Hash::make('password123'),
                'role' => 'user',
                'email_verified_at' => now(),
                'remember_token' => Str::random(10),
            ],
        ];

        foreach ($users as $user) {
            User::create($user);
        }

        // Create additional random users
        User::factory(10)->create();
    }
}