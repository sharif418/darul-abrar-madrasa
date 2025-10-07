<?php

namespace App\Http\Requests;

use App\Models\ClassRoom;
use App\Models\Student;
use Illuminate\Foundation\Http\FormRequest;

class BulkStudentActionRequest extends FormRequest
{
    /**
     * Only admin can perform bulk actions per plan.
     */
    public function authorize(): bool
    {
        return $this->user() ? $this->user()->isAdmin() : false;
    }

    /**
     * Prepare the data for validation.
     * Infer the 'action' based on the route name if not provided.
     */
    protected function prepareForValidation(): void
    {
        $action = $this->input('action');

        if (!$action) {
            if ($this->routeIs('students.bulk-promote')) {
                $action = 'promote';
            } elseif ($this->routeIs('students.bulk-transfer')) {
                $action = 'transfer';
            } elseif ($this->routeIs('students.bulk-status')) {
                $action = 'status_update';
            }
        }

        $this->merge([
            'action' => $action,
        ]);
    }

    /**
     * Validation rules for bulk operations.
     */
    public function rules(): array
    {
        return [
            'action' => ['required', 'in:promote,transfer,status_update'],
            'student_ids' => ['required', 'array', 'min:1'],
            'student_ids.*' => ['integer', 'exists:students,id'],
            'target_class_id' => ['required_if:action,promote,transfer', 'integer', 'exists:classes,id'],
            'status' => ['required_if:action,status_update', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'action.required' => 'Bulk action type is required.',
            'action.in' => 'Invalid bulk action.',
            'student_ids.required' => 'Please select at least one student.',
            'student_ids.array' => 'Invalid students data.',
            'student_ids.min' => 'Please select at least one student.',
            'student_ids.*.exists' => 'One or more selected students do not exist.',
            'target_class_id.required_if' => 'Please select the target class.',
            'target_class_id.exists' => 'The target class does not exist.',
            'status.required_if' => 'Please provide the status value.',
        ];
    }

    /**
     * Additional custom validation checks:
     * - Capacity for target class on promote/transfer
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $action = $this->input('action');
            $studentIds = $this->input('student_ids', []);
            $targetClassId = $this->input('target_class_id');

            if (in_array($action, ['promote', 'transfer'], true)) {
                if (!$targetClassId) {
                    return;
                }

                $class = ClassRoom::find($targetClassId);
                if (!$class) {
                    $validator->errors()->add('target_class_id', 'Target class not found.');
                    return;
                }

                // Count currently active students and ensure capacity
                $currentCount = $class->getStudentsCount();
                $toAdd = count($studentIds);
                $capacity = $class->capacity;

                if ($currentCount + $toAdd > $capacity) {
                    $validator->errors()->add(
                        'target_class_id',
                        "Target class capacity exceeded. Available seats: " . max(0, $capacity - $currentCount) . ". Selected students: {$toAdd}."
                    );
                }
            }

            // Optional: ensure all students exist and are active
            if (!empty($studentIds)) {
                $inactiveCount = Student::whereIn('id', $studentIds)->where('is_active', false)->count();
                if ($inactiveCount > 0 && $action !== 'status_update') {
                    $validator->errors()->add('student_ids', 'One or more selected students are inactive.');
                }
            }
        });
    }
}
