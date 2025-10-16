<?php

namespace App\Http\Requests;

use App\Models\Guardian;
use Illuminate\Foundation\Http\FormRequest;

class StoreGuardianRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Allow only admins or users authorized by policy to create guardians
        return $this->user() && ($this->user()->can('create', Guardian::class) || (method_exists($this->user(), 'isAdmin') && $this->user()->isAdmin()));
    }

    public function rules(): array
    {
        return [
            // User fields
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|unique:users,email', // email may be generated if not provided
            'phone' => 'required|string|max:15|unique:users,phone',
            'password' => 'required|string|min:8|confirmed',

            // Guardian fields
            'national_id' => 'nullable|string|max:50',
            'occupation' => 'nullable|string|max:255',
            'address' => 'required|string',
            'alternative_phone' => 'nullable|string|max:15',
            'relationship_type' => 'required|in:father,mother,legal_guardian,other',
            'is_primary_contact' => 'boolean',
            'emergency_contact' => 'boolean',

            // Optional student linking
            'student_ids' => 'nullable|array',
            'student_ids.*' => 'exists:students,id',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Guardian name is required.',
            'email.email' => 'Please provide a valid email address.',
            'email.unique' => 'This email is already in use.',
            'phone.required' => 'Phone number is required.',
            'phone.unique' => 'This phone number is already in use.',
            'password.required' => 'A password is required.',
            'password.confirmed' => 'Password confirmation does not match.',
            'relationship_type.required' => 'Relationship type is required.',
            'relationship_type.in' => 'Relationship type must be one of: father, mother, legal_guardian, other.',
            'address.required' => 'Address is required.',
            'student_ids.*.exists' => 'One or more selected students do not exist.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_primary_contact' => $this->boolean('is_primary_contact'),
            'emergency_contact' => $this->boolean('emergency_contact'),
        ]);
    }
}
