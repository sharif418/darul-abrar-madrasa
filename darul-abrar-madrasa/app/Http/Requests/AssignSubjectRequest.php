<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Subject;

class AssignSubjectRequest extends FormRequest
{
    /**
     * Only admin can assign/ create subject per plan.
     */
    public function authorize(): bool
    {
        return $this->user() ? $this->user()->isAdmin() : false;
    }

    /**
     * Validation rules:
     * - subject_id provided OR new subject fields required.
     */
    public function rules(): array
    {
        return [
            'subject_id' => ['nullable', 'integer', 'exists:subjects,id'],
            'name' => ['required_without:subject_id', 'string', 'max:255'],
            'code' => [
                'required_without:subject_id',
                'string',
                'max:50',
                // unique for new subject creation
                Rule::unique('subjects', 'code'),
            ],
            'teacher_id' => ['required_without:subject_id', 'integer', 'exists:teachers,id'],
            'full_mark' => ['required_without:subject_id', 'numeric', 'min:1'],
            'pass_mark' => ['required_without:subject_id', 'numeric', 'min:1'],
            'description' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required_without' => 'Subject name is required when not selecting an existing subject.',
            'code.required_without' => 'Subject code is required when not selecting an existing subject.',
            'teacher_id.required_without' => 'Please select a teacher.',
            'full_mark.required_without' => 'Full mark is required.',
            'pass_mark.required_without' => 'Pass mark is required.',
            'subject_id.exists' => 'The selected subject does not exist.',
        ];
    }

    /**
     * Additional validation:
     * - pass_mark < full_mark
     * - if subject_id provided, ensure not already assigned to the same class
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $data = $this->all();

            if (!empty($data['full_mark']) && !empty($data['pass_mark'])) {
                if ((float)$data['pass_mark'] >= (float)$data['full_mark']) {
                    $validator->errors()->add('pass_mark', 'Pass mark must be less than full mark.');
                }
            }

            if (!empty($data['subject_id'])) {
                $subject = Subject::find($data['subject_id']);
                $classParam = $this->route('class');
                $classId = is_object($classParam) ? $classParam->id : $classParam;

                if ($subject && (int)$subject->class_id === (int)$classId) {
                    $validator->errors()->add('subject_id', 'The selected subject is already assigned to this class.');
                }
            }
        });
    }
}
