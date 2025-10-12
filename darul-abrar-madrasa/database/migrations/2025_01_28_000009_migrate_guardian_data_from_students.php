<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        // Safety checks
        if (!DB::getSchemaBuilder()->hasTable('students') ||
            !DB::getSchemaBuilder()->hasTable('users') ||
            !DB::getSchemaBuilder()->hasTable('guardians') ||
            !DB::getSchemaBuilder()->hasTable('guardian_student')) {
            return;
        }

        // Process students in chunks to avoid memory issues
        DB::table('students')->orderBy('id')->chunk(200, function ($students) {
            foreach ($students as $student) {
                // Load student user for name and email fallbacks
                $studentUser = DB::table('users')->where('id', $student->user_id)->first();

                // Prepare potential guardians from student record
                $potentialGuardians = [];

                // Father
                if (!empty($student->father_name)) {
                    $potentialGuardians[] = [
                        'name' => $student->father_name,
                        'relationship' => 'father',
                        'phone' => $student->guardian_phone,
                        'email' => $student->guardian_email,
                        'is_primary' => true, // Prefer father as primary if present
                        'financial' => true,
                    ];
                }

                // Mother
                if (!empty($student->mother_name)) {
                    $potentialGuardians[] = [
                        'name' => $student->mother_name,
                        'relationship' => 'mother',
                        'phone' => $student->guardian_phone,
                        'email' => $student->guardian_email,
                        'is_primary' => empty($student->father_name), // primary if father not present
                        'financial' => empty($student->father_name),   // financial if father not present
                    ];
                }

                // Generic guardian based on phone/email if no names provided
                if (empty($potentialGuardians) && (!empty($student->guardian_phone) || !empty($student->guardian_email))) {
                    $potentialGuardians[] = [
                        'name' => $studentUser ? ('Guardian of ' . $studentUser->name) : 'Guardian',
                        'relationship' => 'legal_guardian',
                        'phone' => $student->guardian_phone,
                        'email' => $student->guardian_email,
                        'is_primary' => true,
                        'financial' => true,
                    ];
                }

                // If nothing to migrate, continue
                if (empty($potentialGuardians)) {
                    continue;
                }

                $createdPrimary = false;

                foreach ($potentialGuardians as $pg) {
                    // Try to find existing guardian by phone first (handles siblings)
                    $guardianId = null;
                    if (!empty($pg['phone'])) {
                        $existingGuardian = DB::table('guardians')->where('phone', $pg['phone'])->first();
                        if ($existingGuardian) {
                            $guardianId = $existingGuardian->id;
                        }
                    }

                    // If not found by phone, try by email
                    if (!$guardianId && !empty($pg['email'])) {
                        $existingGuardianByEmail = DB::table('guardians')->where('email', $pg['email'])->first();
                        if ($existingGuardianByEmail) {
                            $guardianId = $existingGuardianByEmail->id;
                        }
                    }

                    // If still not found, create or reuse a User (role=guardian) and then create Guardian
                    if (!$guardianId) {
                        // Determine email
                        $email = $pg['email'];
                        if (empty($email)) {
                            if (!empty($pg['phone'])) {
                                $sanitizedPhone = preg_replace('/\D+/', '', $pg['phone']);
                                $email = 'guardian_' . $sanitizedPhone . '@temp.darulabrar.edu';
                            } else {
                                $email = 'guardian_' . Str::uuid()->toString() . '@temp.darulabrar.edu';
                            }
                        }

                        // Determine phone
                        $phone = $pg['phone'] ?? null;

                        // Try to reuse existing user by email or phone to avoid uniqueness violations
                        $useUser = null;
                        $byEmail = null;
                        $byPhone = null;

                        if (!empty($email)) {
                            $byEmail = DB::table('users')->where('email', $email)->first();
                        }
                        if (!empty($phone)) {
                            $byPhone = DB::table('users')->where('phone', $phone)->first();
                        }

                        // Prefer phone match over email match to better handle siblings sharing contacts
                        if ($byPhone) {
                            $useUser = $byPhone;
                        } elseif ($byEmail) {
                            $useUser = $byEmail;
                        }

                        // If both exist and are different, prefer phone record; adjust email to a temp to avoid collision on create
                        if (!$useUser && $byEmail && $byPhone && $byEmail->id !== $byPhone->id) {
                            $useUser = $byPhone;
                            // Ensure email is unique if later used for creation
                            $email = 'guardian_' . Str::uuid()->toString() . '@temp.darulabrar.edu';
                        }

                        if ($useUser) {
                            $userId = $useUser->id;
                            // Normalize email/phone variables to what user has (useful for guardian record)
                            $email = $useUser->email ?? $email;
                            $phone = $useUser->phone ?? $phone;
                        } else {
                            // Create user with role guardian
                            $userId = DB::table('users')->insertGetId([
                                'name' => $pg['name'] ?: ($studentUser ? ('Guardian of ' . $studentUser->name) : 'Guardian'),
                                'email' => $email,
                                'password' => Hash::make(Str::random(12)),
                                'role' => 'guardian',
                                'phone' => $phone,
                                'is_active' => true,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]);
                        }

                        // Create guardian record
                        $guardianId = DB::table('guardians')->insertGetId([
                            'user_id' => $userId,
                            'national_id' => null,
                            'occupation' => null,
                            'address' => $student->address ?? '',
                            'phone' => $phone ?? '',
                            'alternative_phone' => null,
                            'email' => $email,
                            'relationship_type' => in_array($pg['relationship'], ['father', 'mother', 'legal_guardian', 'other'])
                                ? $pg['relationship']
                                : 'other',
                            'is_primary_contact' => $pg['is_primary'] && !$createdPrimary,
                            'emergency_contact' => false,
                            'is_active' => true,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }

                    // Link guardian to student in pivot
                    // Determine flags: ensure only first linked is primary/financial if multiple
                    $isPrimaryGuardian = ($pg['is_primary'] && !$createdPrimary);
                    $financialResp = ($pg['financial'] && !$createdPrimary);

                    // Upsert-like behavior: avoid duplicate unique constraint on (guardian_id, student_id)
                    $existsPivot = DB::table('guardian_student')
                        ->where('guardian_id', $guardianId)
                        ->where('student_id', $student->id)
                        ->exists();

                    if (!$existsPivot) {
                        DB::table('guardian_student')->insert([
                            'guardian_id' => $guardianId,
                            'student_id' => $student->id,
                            'relationship' => in_array($pg['relationship'], ['father', 'mother', 'legal_guardian', 'sibling', 'other'])
                                ? $pg['relationship']
                                : 'other',
                            'is_primary_guardian' => $isPrimaryGuardian,
                            'can_pickup' => true,
                            'financial_responsibility' => $financialResp,
                            'receive_notifications' => true,
                            'notes' => null,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    } else {
                        // If exists, ensure primary/financial flags are set if not set before
                        if ($isPrimaryGuardian || $financialResp) {
                            DB::table('guardian_student')
                                ->where('guardian_id', $guardianId)
                                ->where('student_id', $student->id)
                                ->update([
                                    'is_primary_guardian' => DB::raw('CASE WHEN is_primary_guardian = 1 THEN 1 ELSE ' . ($isPrimaryGuardian ? 1 : 0) . ' END'),
                                    'financial_responsibility' => DB::raw('CASE WHEN financial_responsibility = 1 THEN 1 ELSE ' . ($financialResp ? 1 : 0) . ' END'),
                                    'updated_at' => now(),
                                ]);
                        }
                    }

                    if ($isPrimaryGuardian) {
                        $createdPrimary = true;
                    }
                }
            }
        });
    }

    public function down(): void
    {
        // This is a data migration; for safety we do not delete created users/guardians automatically.
        // If rollback is required, a custom artisan command should handle cleanup deliberately.
    }
};
