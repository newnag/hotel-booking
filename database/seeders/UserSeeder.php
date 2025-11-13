<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create admin user
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@hotel.com',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'role' => 'admin',
            'phone' => '0812345678',
            'line_user_id' => null,
            'language' => 'th',
        ]);

        // Create staff users
        User::create([
            'name' => 'Staff User 1',
            'email' => 'staff1@hotel.com',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'role' => 'staff',
            'phone' => '0823456789',
            'line_user_id' => null,
            'language' => 'th',
        ]);

        User::create([
            'name' => 'Staff User 2',
            'email' => 'staff2@hotel.com',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'role' => 'staff',
            'phone' => '0834567890',
            'line_user_id' => null,
            'language' => 'th',
        ]);

        // Create guest users
        User::create([
            'name' => 'Guest User 1',
            'email' => 'guest1@example.com',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'role' => 'guest',
            'phone' => '0845678901',
            'line_user_id' => 'U'.strtolower(str_replace('-', '', fake()->uuid())),
            'language' => 'th',
        ]);

        User::create([
            'name' => 'Guest User 2',
            'email' => 'guest2@example.com',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'role' => 'guest',
            'phone' => '0856789012',
            'line_user_id' => 'U'.strtolower(str_replace('-', '', fake()->uuid())),
            'language' => 'en',
        ]);

        User::create([
            'name' => 'Guest User 3',
            'email' => 'guest3@example.com',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'role' => 'guest',
            'phone' => '0867890123',
            'line_user_id' => null,
            'language' => 'th',
        ]);
    }
}
