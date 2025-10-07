<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSubjectRequest extends FormRequest
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
        $subject = $this->route('subject');

        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50', Rule::unique('subjects', 'code')->ignore($subject->id)],
            'class_id' => ['required', 'exists:classes,id'],
            'teacher_id' => ['nullable', 'exists:teachers,id'],
            'full_mark' => ['required', 'integer', 'min:1'],
            'pass_mark' => ['required', 'integer', 'min:1', 'lt:full_mark'],
            'description' => ['nullable', 'string'],
            'is_active' => ['boolean'],
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
            'name.required' => 'Subject name is required.',
            'code.required' => 'Subject code is required.',
            'code.unique' => 'This subject code is already in use.',
            'class_id.required' => 'Please select a class.',
            'class_id.exists' => 'Selected class does not exist.',
            'teacher_id.exists' => 'Selected teacher does not exist.',
            'full_mark.required' => 'Full mark is required.',
            'full_mark.integer' => 'Full mark must be a number.',
            'full_mark.min' => 'Full mark must be at least 1.',
            'pass_mark.required' => 'Pass mark is required.',
            'pass_mark.integer' => 'Pass mark must be a number.',
            'pass_mark.min' => 'Pass mark must be at least 1.',
            'pass_mark.lt' => 'Pass mark must be less than full mark.',
        ];
    }
}
