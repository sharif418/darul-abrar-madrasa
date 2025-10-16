<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\TeacherAttendance;

class StoreTeacherAttendanceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'date' => ['required', 'date', 'before_or_equal:today'],
            'teacher_ids' => ['required', 'array', 'min:1'],
            'teacher_ids.*' => ['exists:teachers,id'],
            'status' => ['required', 'array'],
            'status.*' => ['required', Rule::in(TeacherAttendance::STATUSES)],
            'check_in_time' => ['nullable', 'array'],
            'check_in_time.*' => ['nullable', 'date_format:H:i'],
            'check_out_time' => ['nullable', 'array'],
            'check_out_time.*' => ['nullable', 'date_format:H:i'],
            'remarks' => ['nullable', 'array'],
            'remarks.*' => ['nullable', 'string', 'max:500'],
        ];
    }

    /**
     * Get custom error messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'date.required' => 'Attendance date is required.',
            'date.date' => 'Please provide a valid date.',
            'date.before_or_equal' => 'Cannot mark attendance for future dates.',
            'teacher_ids.required' => 'Please select at least one teacher.',
            'teacher_ids.min' => 'Please select at least one teacher.',
            'teacher_ids.*.exists' => 'One or more selected teachers do not exist.',
            'status.required' => 'Attendance status is required for all teachers.',
            'status.*.in' => 'Invalid attendance status selected. Must be: present, absent, leave, or half_day.',
            'check_in_time.*.date_format' => 'Check-in time must be in HH:MM format (e.g., 08:30).',
            'check_out_time.*.date_format' => 'Check-out time must be in HH:MM format (e.g., 16:30).',
            'remarks.*.max' => 'Remarks must not exceed 500 characters.',
        ];
    }

    /**
     * Configure the validator instance.
     *
     * @param \Illuminate\Validation\Validator $validator
     * @return void
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $date = $this->input('date');
            $teacherIds = $this->input('teacher_ids', []);
            $statuses = (array) $this->input('status', []);
            $checkInTimes = (array) $this->input('check_in_time', []);
            $checkOutTimes = (array) $this->input('check_out_time', []);

            // Validate time logic based on status
            foreach ($teacherIds as $index => $teacherId) {
                // Ensure status exists for this teacher
                if (!isset($statuses[$teacherId])) {
                    $validator->errors()->add(
                        "status.{$teacherId}",
                        "Status is required for this teacher."
                    );
                    continue;
                }

                $status = $statuses[$teacherId];
                $checkInTime = $checkInTimes[$teacherId] ?? null;
                $checkOutTime = $checkOutTimes[$teacherId] ?? null;

                // If status is absent or leave, times should not be provided
                if (in_array($status, ['absent', 'leave'])) {
                    if ($checkInTime) {
                        $validator->errors()->add(
                            "check_in_time.{$teacherId}",
                            "Check-in time should not be provided for absent or leave status."
                        );
                    }
                    if ($checkOutTime) {
                        $validator->errors()->add(
                            "check_out_time.{$teacherId}",
                            "Check-out time should not be provided for absent or leave status."
                        );
                    }
                }

                // If status is present, check-in time should be provided
                if ($status === 'present' && !$checkInTime) {
                    $validator->errors()->add(
                        "check_in_time.{$teacherId}",
                        "Check-in time is required for present status."
                    );
                }

                // Validate check-out time is after check-in time
                if ($checkInTime && $checkOutTime) {
                    if (strtotime($checkOutTime) <= strtotime($checkInTime)) {
                        $validator->errors()->add(
                            "check_out_time.{$teacherId}",
                            "Check-out time must be after check-in time."
                        );
                    }
                }
            }
        });
    }
}
