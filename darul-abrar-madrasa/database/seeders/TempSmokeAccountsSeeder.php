<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Guardian;
use App\Models\Accountant;
use App\Models\Student;

class TempSmokeAccountsSeeder extends Seeder
{
    /**
     * Create predictable Guardian and Accountant accounts for smoke testing,
     * and link the Guardian to the first available student with financial responsibility.
     */
    public function run(): void
    {
        // Guardian user
        $guardianUser = User::updateOrCreate(
            ['email' => 'guardian@test.local'],
            [
                'name' => 'Test Guardian',
                'password' => Hash::make('Guardian@2025'),
                'role' => 'guardian', // keep legacy role for backward compatibility
                'is_active' => true,
                'email_verified_at' => now(),
                'phone' => '01780000001',
            ]
        );

        // Assign Spatie role if available
        if (method_exists($guardianUser, 'assignRole')) {
            try {
                $guardianUser->assignRole('guardian');
            } catch (\Throwable $e) {
                // ignore if role already assigned
            }
        }

        // Guardian profile
        $guardian = Guardian::firstOrCreate(
            ['user_id' => $guardianUser->id],
            [
                'national_id' => null,
                'occupation' => null,
                'address' => 'Dhaka',
                'phone' => '01780000001',
                'alternative_phone' => null,
                'email' => 'guardian@test.local',
                'relationship_type' => 'legal_guardian',
                'is_primary_contact' => true,
                'emergency_contact' => false,
                'is_active' => true,
            ]
        );

        // Link guardian to first student (if any) with financial responsibility
        $student = Student::with('user')->first();
        if ($student) {
            $exists = DB::table('guardian_student')
                ->where('guardian_id', $guardian->id)
                ->where('student_id', $student->id)
                ->exists();

            if (!$exists) {
                DB::table('guardian_student')->insert([
                    'guardian_id' => $guardian->id,
                    'student_id' => $student->id,
                    'relationship' => 'legal_guardian',
                    'is_primary_guardian' => true,
                    'can_pickup' => true,
                    'financial_responsibility' => true,
                    'receive_notifications' => true,
                    'notes' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // Accountant user
        $accountantUser = User::updateOrCreate(
            ['email' => 'accountant@test.local'],
            [
                'name' => 'Test Accountant',
                'password' => Hash::make('Accountant@2025'),
                'role' => 'accountant', // keep legacy role for backward compatibility
                'is_active' => true,
                'email_verified_at' => now(),
                'phone' => '01780000002',
            ]
        );

        // Assign Spatie role if available
        if (method_exists($accountantUser, 'assignRole')) {
            try {
                $accountantUser->assignRole('accountant');
            } catch (\Throwable $e) {
                // ignore if role already assigned
            }
        }

        // Accountant profile
        Accountant::firstOrCreate(
            ['user_id' => $accountantUser->id],
            [
                'employee_id' => 'ACC-TEST-001',
                'designation' => 'Senior Accountant',
                'qualification' => null,
                'phone' => '01780000002',
                'address' => 'Dhaka',
                'joining_date' => now()->subYear(),
                'salary' => 50000,
                'can_approve_waivers' => true,
                'can_approve_refunds' => false,
                'max_waiver_amount' => 10000,
                'is_active' => true,
            ]
        );

        $this->command?->info('Temp smoke test accounts ensured: guardian@test.local / accountant@test.local');
    }
}
