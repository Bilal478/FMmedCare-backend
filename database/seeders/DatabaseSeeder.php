<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $this->call([
            UsersTableSeeder::class,
            // PatientIntakesTableSeeder::class,
            // PaymentsBillingTableSeeder::class,
            // AuditTrailsTableSeeder::class,
        ]);
        // Create admin user
        // User::create([
        //     'name' => 'Admin User',
        //     'email' => 'admin@healthcare.com',
        //     'password' => Hash::make('password'),
        //     'role' => 'admin',
        // ]);

        // // Create regular user
        // User::create([
        //     'name' => 'Regular User',
        //     'email' => 'user@healthcare.com',
        //     'password' => Hash::make('password'),
        //     'role' => 'user',
        // ]);
    }
}