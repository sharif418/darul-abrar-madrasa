<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Department;
use App\Models\ClassRoom;
use App\Models\Teacher;
use App\Models\Subject;
use App\Models\Student;
use App\Models\Guardian;
use App\Models\Accountant;
use App\Models\Fee;
use App\Models\FeeWaiver;
use App\Models\FeeInstallment;
use App\Models\StudyMaterial;

/**
 * This file groups multiple factory classes used across the test suite.
 * Laravel auto-discovers factory classes in this namespace.
 */

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Department>
 */
class DepartmentFactory extends Factory
{
    protected $model = Department::class;

    public function definition(): array
    {
        return [
            'name' => 'Dept ' . fake()->unique()->word(),
            'code' => strtoupper(Str::random(4)),
            'description' => fake()->optional()->sentence(),
            'is_active' => true,
        ];
    }
}

/**
 * @extends \Illuminate\Database\Eloquent\Factories.Factory<\App\Models\ClassRoom>
 */
class ClassRoomFactory extends Factory
{
    protected $model = ClassRoom::class;

    public function definition(): array
    {
        return [
            'name' => 'Class ' . fake()->unique()->randomDigitNotNull(),
            'department_id' => Department::factory(),
            'class_numeric' => (string) fake()->numberBetween(1, 12),
            'section' => fake()->randomElement(['A', 'B', 'C']),
            'capacity' => fake()->numberBetween(20, 60),
            'description' => fake()->optional()->sentence(),
            'is_active' => true,
        ];
    }
}

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Teacher>
 */
class TeacherFactory extends Factory
{
    protected $model = Teacher::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory()->state(function () {
                return [
                    'name' => fake()->name(),
                    'email' => fake()->unique()->safeEmail(),
                    'password' => Hash::make('password'),
                    'role' => 'teacher',
                    'is_active' => true,
                ];
            }),
            'department_id' => Department::factory(),
            'employee_id' => strtoupper(Str::random(6)),
            'designation' => fake()->randomElement(['Teacher', 'Senior Teacher']),
            'qualification' => fake()->randomElement(['MA in Islamic Studies', 'BA in Education', 'MA in Arabic', 'BEd']),
            'phone' => fake()->phoneNumber(),
            'address' => fake()->address(),
            'joining_date' => now()->toDateString(),
            'is_active' => true,
        ];
    }
}

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Subject>
 */
class SubjectFactory extends Factory
{
    protected $model = Subject::class;

    public function definition(): array
    {
        return [
            'name' => 'Subject ' . fake()->unique()->word(),
            'code' => strtoupper(Str::random(5)),
            'class_id' => ClassRoom::factory(),
            'teacher_id' => null, // can be set explicitly
            'full_mark' => 100,
            'pass_mark' => 33,
            'description' => fake()->optional()->sentence(),
            'is_active' => true,
        ];
    }

    public function assignedTo(Teacher $teacher): static
    {
        return $this->state(fn () => [
            'teacher_id' => $teacher->id,
        ]);
    }
}

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Student>
 */
class StudentFactory extends Factory
{
    protected $model = Student::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory()->state(function () {
                return [
                    'name' => fake()->name(),
                    'email' => fake()->unique()->safeEmail(),
                    'password' => Hash::make('password'),
                    'role' => 'student',
                    'is_active' => true,
                ];
            }),
            'class_id' => ClassRoom::factory(),
            'roll_number' => (string) fake()->unique()->numberBetween(1, 1000),
            'admission_number' => strtoupper(Str::random(8)),
            'admission_date' => now()->toDateString(),
            'father_name' => fake()->name('male'),
            'mother_name' => fake()->name('female'),
            'guardian_phone' => fake()->phoneNumber(),
            'guardian_email' => fake()->optional()->safeEmail(),
            'address' => fake()->address(),
            'date_of_birth' => fake()->date('Y-m-d', '-10 years'),
            'gender' => fake()->randomElement(['male', 'female']),
            'blood_group' => fake()->optional()->randomElement(['A+', 'A-', 'B+', 'B-', 'O+', 'O-', 'AB+', 'AB-']),
            'is_active' => true,
        ];
    }
}

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Guardian>
 */
class GuardianFactory extends Factory
{
    protected $model = Guardian::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory()->state(function () {
                return [
                    'name' => 'Guardian ' . fake()->lastName(),
                    'email' => fake()->unique()->safeEmail(),
                    'password' => Hash::make('password'),
                    'role' => 'guardian',
                    'is_active' => true,
                ];
            }),
            'national_id' => fake()->optional()->numerify('#############'),
            'occupation' => fake()->optional()->jobTitle(),
            'address' => fake()->address(),
            'phone' => fake()->phoneNumber(),
            'alternative_phone' => fake()->optional()->phoneNumber(),
            'email' => fake()->optional()->safeEmail(),
            'relationship_type' => fake()->randomElement(['father', 'mother', 'legal_guardian', 'other']),
            'is_primary_contact' => true,
            'emergency_contact' => false,
            'is_active' => true,
        ];
    }
}

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Accountant>
 */
class AccountantFactory extends Factory
{
    protected $model = Accountant::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory()->state(function () {
                return [
                    'name' => 'Accountant ' . fake()->lastName(),
                    'email' => fake()->unique()->safeEmail(),
                    'password' => Hash::make('password'),
                    'role' => 'accountant',
                    'is_active' => true,
                ];
            }),
            'employee_id' => strtoupper(Str::random(6)),
            'designation' => fake()->randomElement(['Accountant', 'Senior Accountant']),
            'qualification' => fake()->optional()->word(),
            'phone' => fake()->phoneNumber(),
            'address' => fake()->address(),
            'joining_date' => now()->toDateString(),
            'salary' => 0,
            'can_approve_waivers' => true,
            'can_approve_refunds' => false,
            'max_waiver_amount' => 10000,
            'is_active' => true,
        ];
    }
}

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Fee>
 */
class FeeFactory extends Factory
{
    protected $model = Fee::class;

    public function definition(): array
    {
        return [
            'student_id' => Student::factory(),
            'fee_type' => fake()->randomElement(['admission', 'monthly', 'exam', 'other']),
            'amount' => fake()->randomFloat(2, 100, 2000),
            'paid_amount' => 0,
            'due_date' => now()->addDays(7)->toDateString(),
            'payment_date' => null,
            'status' => 'unpaid',
            'payment_method' => null,
            'transaction_id' => null,
            'invoice_number' => null,
            'remarks' => fake()->optional()->sentence(),
            'collected_by' => null,
            'late_fee_total' => 0,
        ];
    }
}

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\FeeWaiver>
 */
class FeeWaiverFactory extends Factory
{
    protected $model = FeeWaiver::class;

    public function definition(): array
    {
        return [
            'student_id' => Student::factory(),
            'fee_id' => null,
            'waiver_type' => fake()->randomElement(['scholarship', 'financial_aid', 'merit', 'sibling_discount', 'staff_child', 'other']),
            'amount_type' => fake()->randomElement(['percentage', 'fixed']),
            'amount' => fake()->randomFloat(2, 5, 500),
            'reason' => fake()->sentence(),
            'valid_from' => now()->subDay()->toDateString(),
            'valid_until' => now()->addMonth()->toDateString(),
            'approved_by' => null,
            'approved_at' => null,
            'status' => 'approved',
            'rejection_reason' => null,
            'created_by' => User::factory()->state(['role' => 'accountant']),
        ];
    }
}

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\FeeInstallment>
 */
class FeeInstallmentFactory extends Factory
{
    protected $model = FeeInstallment::class;

    public function definition(): array
    {
        return [
            'fee_id' => Fee::factory(),
            'installment_number' => 1,
            'amount' => fake()->randomFloat(2, 100, 1000),
            'due_date' => now()->addWeek()->toDateString(),
            'paid_amount' => 0,
            'payment_date' => null,
            'status' => 'pending',
            'payment_method' => null,
            'transaction_id' => null,
            'late_fee_applied' => 0,
            'collected_by' => null,
            'remarks' => null,
        ];
    }
}

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\StudyMaterial>
 */
class StudyMaterialFactory extends Factory
{
    protected $model = StudyMaterial::class;

    public function definition(): array
    {
        $teacher = Teacher::factory()->create();
        $subject = Subject::factory()->assignedTo($teacher)->create();

        return [
            'title' => 'Material ' . fake()->unique()->word(),
            'description' => fake()->optional()->sentence(),
            'content_type' => fake()->randomElement(['note', 'document']),
            'teacher_id' => $teacher->id,
            'class_id' => $subject->class_id,
            'subject_id' => $subject->id,
            'is_published' => true,
            'file_path' => null,
        ];
    }
}
