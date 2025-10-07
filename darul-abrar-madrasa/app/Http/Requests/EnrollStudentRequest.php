<?php

namespace App\Http\Requests;

use App\Models\ClassRoom;
use App\Models\Student;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EnrollStudentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     * Only admin can perform enrollment per plan.
     */
    public function authorize(): bool
    {
        return $this->user() ? $this->user()->isAdmin() : false;
    }

    /**
     * Get the validation rules that apply to the request.
     * - student_id: required, exists
     * Additional constraints (class capacity, already enrolled, student active) are handled in withValidator.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'student_id' => ['required', 'integer', 'exists:students,id'],
        ];
    }

    /**
     * Custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'student_id.required' => 'Please select a student to enroll.',
            'student_id.exists' => 'The selected student does not exist.',
        ];
    }

    /**
     * Configure the validator instance for custom validation:
     * - Check class capacity
     * - Ensure student is active
     * - Ensure student is not already in the class
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $classParam = $this->route('class');
            $class = $classParam instanceof ClassRoom ? $classParam : ClassRoom::find($classParam);
            $studentId = $this->input('student_id');

            if (!$class) {
                $validator->errors()->add('class', 'Invalid class selected.');
                return;
            }

            // Check capacity
            if ($class->isFull()) {
                $validator->errors()->add('student_id', 'This class has reached its maximum capacity.');
            }

            // Validate student record
            if ($studentId) {
                $student = Student::with('user')->find($studentId);
                if (!$student) {
                    $validator->errors()->add('student_id', 'The selected student could not be found.');
                    return;
                }

                // Ensure student is active
                if (!$student->is_active) {
                    $validator->errors()->add('student_id', 'The selected student is not active.');
                }

                // Ensure student is not already in this class
                if ((int) $student->class_id === (int) $class->id) {
                    $validator->errors()->add('student_id', 'The selected student is already enrolled in this class.');
                }
            }
        });
    }
}
