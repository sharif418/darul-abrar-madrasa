<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        // Prevent accidental runs on production without explicit confirmation
        if (app()->environment('production')) {
            $this->command?->warn('AdminUserSeeder: Skipping in production environment.');
            return;
        }

        // Create or update the default admin user idempotently
        $admin = User::updateOrCreate(
            ['email' => 'admin@darulabrar.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('Admin@2025'),
                'role' => 'admin',
                'is_active' => true,
                'email_verified_at' => now(),
                'phone' => $this->defaultPhone(),
            ]
        );

        $this->command?->info('Admin user ensured.');
        $this->command?->info('Email: admin@darulabrar.com');
        $this->command?->info('Password: Admin@2025');
    }

    /**
     * Generate a default phone for admin if not present.
     */
    private function defaultPhone(): string
    {
        return '017' . random_int(10000000, 99999999);
    }
}
