<?php

namespace App\Http\Requests;

use App\Models\Exam;
use App\Models\Subject;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StoreResultRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = Auth::user();
        return $user && ($user->isAdmin() || $user->isTeacher());
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'exam_id' => ['required', 'exists:exams,id'],
            'subject_id' => ['required', 'exists:subjects,id'],
            'student_ids' => ['required', 'array'],
            'student_ids.*' => ['exists:students,id'],
            'marks_obtained' => ['required', 'array'],
            'marks_obtained.*' => ['required', 'numeric', 'min:0'],
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
            'exam_id.required' => 'Please select an exam.',
            'exam_id.exists' => 'Selected exam does not exist.',
            'subject_id.required' => 'Please select a subject.',
            'subject_id.exists' => 'Selected subject does not exist.',
            'student_ids.required' => 'Please select at least one student.',
            'student_ids.*.exists' => 'One or more selected students do not exist.',
            'marks_obtained.required' => 'Marks are required for all students.',
            'marks_obtained.*.required' => 'Marks are required for each student.',
            'marks_obtained.*.numeric' => 'Marks must be a number.',
            'marks_obtained.*.min' => 'Marks must be at least 0.',
            'remarks.*.max' => 'Remarks must not exceed 255 characters.',
        ];
    }

    /**
     * Configure the validator instance.
     *
     * @param  \Illuminate\Validation\Validator  $validator
     * @return void
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Check if exam is completed
            $exam = Exam::find($this->input('exam_id'));
            if ($exam && $exam->end_date > now()) {
                $validator->errors()->add('exam_id', 'Cannot enter results for an exam that has not ended yet.');
            }

            // Check if results are already published
            if ($exam && $exam->is_result_published) {
                $validator->errors()->add('exam_id', 'Cannot modify results for an exam with published results.');
            }

            // Check if marks exceed subject's full mark
            $subject = Subject::find($this->input('subject_id'));
            if ($subject) {
                $marks = $this->input('marks_obtained', []);
                foreach ($marks as $mark) {
                    if ($mark > $subject->full_mark) {
                        $validator->errors()->add('marks_obtained', "Marks cannot exceed the subject's full mark of {$subject->full_mark}.");
                        break;
                    }
                }
            }
        });
    }
}
