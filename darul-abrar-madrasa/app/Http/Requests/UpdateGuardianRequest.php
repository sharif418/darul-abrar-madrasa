<?php

namespace App\Http\Requests;

use App\Models\Guardian;
use Illuminate\Foundation\Http\FormRequest;

class UpdateGuardianRequest extends FormRequest
{
    public function authorize(): bool
    {
        $guardian = $this->route('guardian');

        // Allow only admins or users authorized by policy to update guardians
        if ($this->user() && ($this->user()->can('update', $guardian) || (method_exists($this->user(), 'isAdmin') && $this->user()->isAdmin()))) {
            return true;
        }

        return false;
    }

    public function rules(): array
    {
        // Guardian is type-hinted in routes: /guardians/{guardian}
        $guardian = $this->route('guardian');
        $userId = is_object($guardian) ? $guardian->user_id : null;
        $guardianId = is_object($guardian) ? $guardian->id : null;

        return [
            // User fields
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|unique:users,email,' . $userId,
            'phone' => 'required|string|max:15|unique:users,phone,' . $userId,
            'password' => 'nullable|string|min:8|confirmed',

            // Guardian fields
            'national_id' => 'nullable|string|max:50',
            'occupation' => 'nullable|string|max:255',
            'address' => 'required|string',
            'alternative_phone' => 'nullable|string|max:15',
            'relationship_type' => 'required|in:father,mother,legal_guardian,other',
            'is_primary_contact' => 'boolean',
            'emergency_contact' => 'boolean',
            'is_active' => 'boolean',
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
            'password.confirmed' => 'Password confirmation does not match.',
            'relationship_type.required' => 'Relationship type is required.',
            'relationship_type.in' => 'Relationship type must be one of: father, mother, legal_guardian, other.',
            'address.required' => 'Address is required.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_primary_contact' => $this->boolean('is_primary_contact'),
            'emergency_contact' => $this->boolean('emergency_contact'),
            'is_active' => $this->boolean('is_active'),
        ]);
    }
}
