<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAttendanceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization handled by middleware
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'class_id' => ['required', 'exists:classes,id'],
            'date' => ['required', 'date'],
            'student_ids' => ['required', 'array', 'min:1'],
            'student_ids.*' => ['exists:students,id'],
            'status' => ['required', 'array'],
            'status.*' => ['in:present,absent,late,leave,half_day'],
            'remarks' => ['nullable', 'array'],
            'remarks.*' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'class_id.required' => 'Please select a class.',
            'class_id.exists' => 'Selected class does not exist.',
            'date.required' => 'Attendance date is required.',
            'student_ids.required' => 'Please select at least one student.',
            'student_ids.min' => 'Please select at least one student.',
            'student_ids.*.exists' => 'One or more selected students do not exist.',
            'status.required' => 'Attendance status is required for all students.',
            'status.*.in' => 'Invalid attendance status selected.',
            'remarks.*.max' => 'Remarks must not exceed 255 characters.',
        ];
    }
}
