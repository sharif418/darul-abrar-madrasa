<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        // Create admin user if not exists
        if (!User::where('email', 'admin@darulabrar.com')->exists()) {
            User::create([
                'name' => 'Admin User',
                'email' => 'admin@darulabrar.com',
                'password' => Hash::make('Admin@2025'),
                'role' => 'admin',
                'is_active' => true,
                'email_verified_at' => now(),
            ]);
            
            echo "Admin user created successfully!\n";
            echo "Email: admin@darulabrar.com\n";
            echo "Password: Admin@2025\n";
        } else {
            echo "Admin user already exists.\n";
        }
    }
}
