<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\TeacherAttendance;

class UpdateTeacherAttendanceRequest extends FormRequest
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
            'status' => ['required', Rule::in(TeacherAttendance::STATUSES)],
            'check_in_time' => ['nullable', 'date_format:H:i'],
            'check_out_time' => ['nullable', 'date_format:H:i'],
            'remarks' => ['nullable', 'string', 'max:500'],
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
            'status.required' => 'Attendance status is required.',
            'status.in' => 'Invalid attendance status selected. Must be: present, absent, leave, or half_day.',
            'check_in_time.date_format' => 'Check-in time must be in HH:MM format (e.g., 08:30).',
            'check_out_time.date_format' => 'Check-out time must be in HH:MM format (e.g., 16:30).',
            'check_out_time.after' => 'Check-out time must be after check-in time.',
            'remarks.max' => 'Remarks must not exceed 500 characters.',
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
            $attendance = $this->route('teacherAttendance');
            $date = $this->input('date');
            $status = $this->input('status');
            $checkInTime = $this->input('check_in_time');
            $checkOutTime = $this->input('check_out_time');

            // Check for duplicate attendance (excluding current record)
            if ($attendance) {
                $exists = TeacherAttendance::where('teacher_id', $attendance->teacher_id)
                    ->where('date', $date)
                    ->where('id', '!=', $attendance->id)
                    ->exists();

                if ($exists) {
                    $validator->errors()->add(
                        'date',
                        "Attendance for this teacher on {$date} already exists."
                    );
                }
            }

            // Validate time logic based on status
            if (in_array($status, ['absent', 'leave'])) {
                if ($checkInTime) {
                    $validator->errors()->add(
                        'check_in_time',
                        'Check-in time should not be provided for absent or leave status.'
                    );
                }
                if ($checkOutTime) {
                    $validator->errors()->add(
                        'check_out_time',
                        'Check-out time should not be provided for absent or leave status.'
                    );
                }
            }

            // If status is present, check-in time should be provided
            if ($status === 'present' && !$checkInTime) {
                $validator->errors()->add(
                    'check_in_time',
                    'Check-in time is required for present status.'
                );
            }

            // Validate check-out time is after check-in time
            if ($checkInTime && $checkOutTime) {
                if (strtotime($checkOutTime) <= strtotime($checkInTime)) {
                    $validator->errors()->add(
                        'check_out_time',
                        'Check-out time must be after check-in time.'
                    );
                }
            }
        });
    }
}
