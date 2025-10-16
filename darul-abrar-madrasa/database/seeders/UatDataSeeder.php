<?php

namespace Database\Seeders;

use App\Models\ClassRoom;
use App\Models\Department;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\Subject;
use App\Models\User;
use App\Models\Fee;
use App\Models\Attendance;
use App\Models\Exam;
use App\Models\Result;
use App\Models\Notice;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UatDataSeeder extends Seeder
{
    /**
     * Run the database seeds for UAT (User Acceptance Testing).
     */
    public function run(): void
    {
        $this->command->info('Creating UAT demo data...');

        DB::transaction(function () {
            // 1. Create Departments
            $this->command->info('Creating departments...');
            $departments = $this->createDepartments();

            // 2. Create Classes
            $this->command->info('Creating classes...');
            $classes = $this->createClasses($departments);

            // 3. Create Teachers
            $this->command->info('Creating teachers...');
            $teachers = $this->createTeachers($departments);

            // 4. Create Students
            $this->command->info('Creating students...');
            $students = $this->createStudents($classes);

            // 5. Create Subjects
            $this->command->info('Creating subjects...');
            $subjects = $this->createSubjects($classes, $teachers);

            // 6. Create Exams
            $this->command->info('Creating exams...');
            $exams = $this->createExams($classes);

            // 7. Create Results
            $this->command->info('Creating results...');
            $this->createResults($exams, $subjects, $students);

            // 8. Create Fees
            $this->command->info('Creating fees...');
            $this->createFees($students);

            // 9. Create Attendance
            $this->command->info('Creating attendance records...');
            $this->createAttendance($classes, $students);

            // 10. Create Notices
            $this->command->info('Creating notices...');
            $this->createNotices();
        });

        $this->command->info('UAT demo data created successfully!');
    }

    private function createDepartments()
    {
        $departments = [
            ['name' => 'Islamic Studies', 'code' => 'IS', 'description' => 'Quran, Hadith, Fiqh', 'is_active' => true],
            ['name' => 'General Education', 'code' => 'GE', 'description' => 'Math, Science, English', 'is_active' => true],
            ['name' => 'Arabic Language', 'code' => 'AR', 'description' => 'Arabic Grammar and Literature', 'is_active' => true],
        ];

        return collect($departments)->map(fn($dept) => Department::create($dept));
    }

    private function createClasses($departments)
    {
        $classes = [
            ['name' => 'Class 1-A', 'department_id' => $departments[0]->id, 'class_numeric' => '1', 'section' => 'A', 'capacity' => 30],
            ['name' => 'Class 2-A', 'department_id' => $departments[0]->id, 'class_numeric' => '2', 'section' => 'A', 'capacity' => 30],
            ['name' => 'Class 3-A', 'department_id' => $departments[1]->id, 'class_numeric' => '3', 'section' => 'A', 'capacity' => 35],
            ['name' => 'Class 4-A', 'department_id' => $departments[1]->id, 'class_numeric' => '4', 'section' => 'A', 'capacity' => 35],
            ['name' => 'Class 5-A', 'department_id' => $departments[2]->id, 'class_numeric' => '5', 'section' => 'A', 'capacity' => 25],
        ];

        return collect($classes)->map(fn($class) => ClassRoom::create($class + ['is_active' => true]));
    }

    private function createTeachers($departments)
    {
        $teachers = [];
        $names = ['Ahmed Ali', 'Fatima Rahman', 'Ibrahim Hassan', 'Aisha Khan', 'Omar Farooq', 'Zainab Ahmed', 'Yusuf Ibrahim', 'Maryam Ali'];

        foreach ($names as $index => $name) {
            $email = strtolower(str_replace(' ', '.', $name)) . '@darulabrar.com';
            
            $user = User::create([
                'name' => $name,
                'email' => $email,
                'password' => Hash::make('password'),
                'role' => 'teacher',
                'phone' => '01' . rand(700000000, 799999999),
                'is_active' => true,
                'email_verified_at' => now(),
            ]);

            // Generate unique employee_id for each teacher
            $employeeId = 'TCH-' . strtoupper(Str::random(6));

            $teachers[] = Teacher::create([
                'user_id' => $user->id,
                'employee_id' => $employeeId,  // FIXED: Added employee_id
                'department_id' => $departments[$index % count($departments)]->id,
                'designation' => ['Senior Teacher', 'Assistant Teacher', 'Head Teacher'][$index % 3],
                'qualification' => ['MA in Islamic Studies', 'BA in Education', 'MA in Arabic'][$index % 3],
                'joining_date' => now()->subMonths(rand(6, 36)),
                'address' => 'Dhaka, Bangladesh',
                'salary' => rand(20000, 40000),
                'is_active' => true,
            ]);
        }

        return collect($teachers);
    }

    private function createStudents($classes)
    {
        $students = [];
        $firstNames = ['Abdullah', 'Aisha', 'Ibrahim', 'Fatima', 'Omar', 'Zainab', 'Ali', 'Maryam', 'Hassan', 'Khadija'];
        $lastNames = ['Rahman', 'Ahmed', 'Khan', 'Ali', 'Hassan', 'Hussain', 'Malik', 'Siddiqui'];

        foreach ($classes as $classIndex => $class) {
            for ($i = 1; $i <= 8; $i++) {
                $name = $firstNames[array_rand($firstNames)] . ' ' . $lastNames[array_rand($lastNames)];
                $email = 'student' . ($classIndex * 10 + $i) . '@darulabrar.com';

                $user = User::create([
                    'name' => $name,
                    'email' => $email,
                    'password' => Hash::make('password'),
                    'role' => 'student',
                    'phone' => '01' . rand(700000000, 799999999),
                    'is_active' => true,
                    'email_verified_at' => now(),
                ]);

                $students[] = Student::create([
                    'user_id' => $user->id,
                    'class_id' => $class->id,
                    'roll_number' => str_pad($i, 3, '0', STR_PAD_LEFT),
                    'admission_number' => 'DA' . date('Y') . str_pad(($classIndex * 10 + $i), 4, '0', STR_PAD_LEFT),
                    'admission_date' => now()->subMonths(rand(1, 12)),
                    'father_name' => 'Mr. ' . $lastNames[array_rand($lastNames)],
                    'mother_name' => 'Mrs. ' . $lastNames[array_rand($lastNames)],
                    'guardian_phone' => '01' . rand(700000000, 799999999),
                    'guardian_email' => 'guardian' . ($classIndex * 10 + $i) . '@example.com',
                    'address' => 'Dhaka, Bangladesh',
                    'date_of_birth' => now()->subYears(rand(6, 15)),
                    'gender' => ['male', 'female'][rand(0, 1)],
                    'blood_group' => ['A+', 'B+', 'O+', 'AB+'][rand(0, 3)],
                    'is_active' => true,
                ]);
            }
        }

        return collect($students);
    }

    private function createSubjects($classes, $teachers)
    {
        $subjects = [];
        $subjectData = [
            ['name' => 'Quran', 'code' => 'QUR', 'full_mark' => 100, 'pass_mark' => 40],
            ['name' => 'Hadith', 'code' => 'HAD', 'full_mark' => 100, 'pass_mark' => 40],
            ['name' => 'Fiqh', 'code' => 'FIQ', 'full_mark' => 100, 'pass_mark' => 40],
            ['name' => 'Arabic', 'code' => 'ARB', 'full_mark' => 100, 'pass_mark' => 40],
            ['name' => 'Mathematics', 'code' => 'MAT', 'full_mark' => 100, 'pass_mark' => 33],
        ];

        foreach ($classes as $class) {
            foreach ($subjectData as $index => $subj) {
                $subjects[] = Subject::create([
                    'name' => $subj['name'],
                    'code' => $subj['code'] . '-' . $class->class_numeric,
                    'class_id' => $class->id,
                    'teacher_id' => $teachers[$index % count($teachers)]->id,
                    'full_mark' => $subj['full_mark'],
                    'pass_mark' => $subj['pass_mark'],
                    'is_active' => true,
                ]);
            }
        }

        return collect($subjects);
    }

    private function createExams($classes)
    {
        $exams = [];
        $examTypes = ['First Term', 'Mid Term', 'Final Term'];

        foreach ($classes as $class) {
            foreach ($examTypes as $index => $type) {
                $exams[] = Exam::create([
                    'name' => $type . ' Exam ' . date('Y'),
                    'class_id' => $class->id,
                    'start_date' => now()->addMonths($index * 3),
                    'end_date' => now()->addMonths($index * 3)->addDays(7),
                    'description' => $type . ' examination for ' . $class->name,
                    'is_active' => true,
                    'is_result_published' => $index == 0, // Only first term published
                ]);
            }
        }

        return collect($exams);
    }

    private function createResults($exams, $subjects, $students)
    {
        // Only create results for published exams
        $publishedExams = $exams->where('is_result_published', true);

        foreach ($publishedExams as $exam) {
            $classSubjects = $subjects->where('class_id', $exam->class_id);
            $classStudents = $students->where('class_id', $exam->class_id);

            foreach ($classStudents as $student) {
                foreach ($classSubjects as $subject) {
                    $marks = rand($subject->pass_mark - 10, $subject->full_mark);

                    // Create minimal result record, then calculate grade/GPA via model
                    $result = Result::updateOrCreate(
                        [
                            'exam_id' => $exam->id,
                            'student_id' => $student->id,
                            'subject_id' => $subject->id,
                        ],
                        [
                            'marks_obtained' => max(0, $marks),
                            'remarks' => $marks >= $subject->pass_mark ? 'Good' : 'Needs improvement',
                            'created_by' => 1, // Admin
                        ]
                    );

                    // Persist grade, gpa_point, and is_passed using model logic
                    $result->calculateGradeAndGpa()->save();
                }
            }
        }
    }

    private function createFees($students)
    {
        $feeTypes = ['monthly', 'exam', 'library', 'transport'];
        
        foreach ($students as $student) {
            foreach ($feeTypes as $index => $type) {
                $amount = ['monthly' => 2000, 'exam' => 500, 'library' => 300, 'transport' => 1000][$type];
                $status = ['paid', 'unpaid', 'partial'][rand(0, 2)];
                $paidAmount = $status === 'paid' ? $amount : ($status === 'partial' ? $amount * 0.5 : 0);

                Fee::create([
                    'student_id' => $student->id,
                    'fee_type' => $type,
                    'amount' => $amount,
                    'due_date' => now()->addDays(rand(1, 30)),
                    'status' => $status,
                    'paid_amount' => $paidAmount,
                    'payment_method' => $status !== 'unpaid' ? 'cash' : null,
                    'payment_date' => $status !== 'unpaid' ? now()->subDays(rand(1, 10)) : null,
                    'collected_by' => $status !== 'unpaid' ? 1 : null,
                ]);
            }
        }
    }

    private function createAttendance($classes, $students)
    {
        // Create attendance for last 30 days
        for ($day = 30; $day >= 0; $day--) {
            $date = now()->subDays($day);
            
            // Skip weekends
            if ($date->isFriday()) continue;

            foreach ($classes as $class) {
                $classStudents = $students->where('class_id', $class->id);
                
                foreach ($classStudents as $student) {
                    $statuses = ['present', 'present', 'present', 'present', 'absent', 'late'];
                    
                    Attendance::create([
                        'student_id' => $student->id,
                        'class_id' => $class->id,
                        'date' => $date,
                        'status' => $statuses[array_rand($statuses)],
                        'remarks' => null,
                        'marked_by' => 1,
                    ]);
                }
            }
        }
    }

    private function createNotices()
    {
        $notices = [
            [
                'title' => 'Welcome to New Academic Year',
                'description' => 'We welcome all students to the new academic year. Classes will start from next week.',
                'notice_for' => 'all',
                'publish_date' => now()->subDays(10),
                'expiry_date' => now()->addDays(20),
            ],
            [
                'title' => 'Exam Schedule Announced',
                'description' => 'The first term examination schedule has been announced. Please check the notice board.',
                'notice_for' => 'students',
                'publish_date' => now()->subDays(5),
                'expiry_date' => now()->addDays(25),
            ],
            [
                'title' => 'Teacher Training Program',
                'description' => 'All teachers are requested to attend the training program on teaching methodologies.',
                'notice_for' => 'teachers',
                'publish_date' => now()->subDays(3),
                'expiry_date' => now()->addDays(7),
            ],
        ];

        foreach ($notices as $notice) {
            Notice::create($notice + [
                'is_active' => true,
                'published_by' => 1, // Admin
            ]);
        }
    }
}
