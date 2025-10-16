<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed in proper order to maintain referential integrity
        
        // 1. Create roles and permissions first (requires Spatie tables from migration)
        $this->call(RolePermissionSeeder::class);
        
        // 2. Create admin user (depends on roles)
        $this->call(AdminUserSeeder::class);
        
        // 3. Seed notification templates and triggers
        $this->call(NotificationSeeder::class);
        
        // 4. Create demo data for testing (optional - only in development)
        if (app()->environment(['local', 'development'])) {
            $this->call(DemoDataSeeder::class);
        }
        
        $this->command->info('Database seeding completed successfully!');
    }
}
