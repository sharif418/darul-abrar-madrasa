<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTeacherRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'phone' => ['required', 'string', 'max:15'],
            'avatar' => ['nullable', 'image', 'max:2048'],
            'department_id' => ['required', 'exists:departments,id'],
            'designation' => ['required', 'string', 'max:255'],
            'qualification' => ['required', 'string', 'max:255'],
            'joining_date' => ['required', 'date'],
            'address' => ['required', 'string'],
            'salary' => ['required', 'numeric', 'min:0'],
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
            'name.required' => 'Teacher name is required.',
            'email.required' => 'Email address is required.',
            'email.email' => 'Please provide a valid email address.',
            'email.unique' => 'This email address is already registered.',
            'password.required' => 'Password is required.',
            'password.min' => 'Password must be at least 8 characters.',
            'password.confirmed' => 'Password confirmation does not match.',
            'phone.required' => 'Phone number is required.',
            'department_id.required' => 'Please select a department.',
            'department_id.exists' => 'Selected department does not exist.',
            'designation.required' => 'Designation is required.',
            'qualification.required' => 'Qualification is required.',
            'joining_date.required' => 'Joining date is required.',
            'address.required' => 'Address is required.',
            'salary.required' => 'Salary is required.',
            'salary.numeric' => 'Salary must be a number.',
            'salary.min' => 'Salary must be at least 0.',
            'avatar.image' => 'Avatar must be an image file.',
            'avatar.max' => 'Avatar size must not exceed 2MB.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        if (!$this->has('is_active')) {
            $this->merge([
                'is_active' => true,
            ]);
        }
    }
}
