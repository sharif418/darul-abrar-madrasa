<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\TimetableEntry;
use App\Models\Subject;
use App\Models\Period;

class StoreTimetableEntryRequest extends FormRequest
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
            'timetable_id' => ['required', 'exists:timetables,id'],
            'class_id' => ['required', 'exists:classes,id'],
            'subject_id' => ['required', 'exists:subjects,id'],
            'teacher_id' => ['nullable', 'exists:teachers,id'],
            'period_id' => ['required', 'exists:periods,id'],
            'day_of_week' => ['required', 'in:monday,tuesday,wednesday,thursday,friday,saturday,sunday'],
            'room_number' => ['nullable', 'string', 'max:50'],
            'notes' => ['nullable', 'string', 'max:500'],
            'is_active' => ['boolean'],
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
            'timetable_id.required' => 'Please select a timetable.',
            'timetable_id.exists' => 'Selected timetable does not exist.',
            'class_id.required' => 'Please select a class.',
            'class_id.exists' => 'Selected class does not exist.',
            'subject_id.required' => 'Please select a subject.',
            'subject_id.exists' => 'Selected subject does not exist.',
            'teacher_id.exists' => 'Selected teacher does not exist.',
            'period_id.required' => 'Please select a period.',
            'period_id.exists' => 'Selected period does not exist.',
            'day_of_week.required' => 'Day of week is required.',
            'day_of_week.in' => 'Invalid day of week selected.',
            'room_number.max' => 'Room number must not exceed 50 characters.',
            'notes.max' => 'Notes must not exceed 500 characters.',
        ];
    }

    /**
     * Configure the validator instance.
     *
     * @param  \Illuminate\Validation\Validator  $validator
     * @return void
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if ($validator->errors()->isEmpty()) {
                $timetableId = $this->input('timetable_id');
                $classId = $this->input('class_id');
                $subjectId = $this->input('subject_id');
                $teacherId = $this->input('teacher_id');
                $periodId = $this->input('period_id');
                $dayOfWeek = $this->input('day_of_week');

                // 1. Check for class conflict (class already scheduled in same period/day)
                $classConflict = TimetableEntry::where('timetable_id', $timetableId)
                    ->where('class_id', $classId)
                    ->where('period_id', $periodId)
                    ->where('day_of_week', $dayOfWeek)
                    ->exists();

                if ($classConflict) {
                    $validator->errors()->add('period_id', 'This class is already scheduled in this period on this day.');
                }

                // 2. Check for teacher conflict (teacher assigned to different class at same time)
                if ($teacherId) {
                    $teacherConflict = TimetableEntry::where('timetable_id', $timetableId)
                        ->where('teacher_id', $teacherId)
                        ->where('period_id', $periodId)
                        ->where('day_of_week', $dayOfWeek)
                        ->where('class_id', '!=', $classId)
                        ->exists();

                    if ($teacherConflict) {
                        $validator->errors()->add('teacher_id', 'This teacher is already assigned to another class in this period on this day.');
                    }
                }

                // 3. Validate subject belongs to the selected class
                $subject = Subject::find($subjectId);
                if ($subject && $subject->class_id != $classId) {
                    $validator->errors()->add('subject_id', 'The selected subject does not belong to the selected class.');
                }

                // 4. Validate period's day matches entry's day
                $period = Period::find($periodId);
                if ($period && $period->day_of_week != $dayOfWeek) {
                    $validator->errors()->add('period_id', 'The selected period is not available on the selected day of week.');
                }
            }
        });
    }
}
