<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class LocalAdminSeeder extends Seeder
{
    /**
     * Create a local admin user for testing (idempotent).
     */
    public function run(): void
    {
        $email = 'admin@local.test';

        $user = User::firstOrCreate(
            ['email' => $email],
            [
                'name' => 'Local Admin',
                'password' => Hash::make('Password123!'),
                'role' => 'admin',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        // Assign spatie role if available
        if (method_exists($user, 'assignRole')) {
            try {
                $user->assignRole('admin');
            } catch (\Throwable $e) {
                // ignore if role already assigned
            }
        }

        $this->command->info("Local admin ensured: {$email}");
    }
}
