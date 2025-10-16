<?php

namespace App\Http\Requests;

use App\Models\Accountant;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;

class StoreAccountantRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Allow only admins or users authorized by policy to create accountants
        $authorized = $this->user()
            && ($this->user()->can('create', Accountant::class)
                || (method_exists($this->user(), 'isAdmin') && $this->user()->isAdmin()));

        // Log failed authorization attempts
        if (!$authorized && $this->user()) {
            Log::warning('Unauthorized accountant creation attempt', [
                'user_id' => $this->user()->id,
                'user_role' => $this->user()->role ?? 'unknown',
                'user_email' => $this->user()->email,
            ]);
        }

        return $authorized;
    }

    protected function failedAuthorization()
    {
        abort(403, 'You do not have permission to create accountants. Please contact your administrator.');
    }

    public function rules(): array
    {
        return [
            // User fields
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|string|max:15|unique:users,phone',
            'password' => 'required|string|min:8|confirmed',

            // Accountant fields
            'employee_id' => 'required|string|unique:accountants,employee_id',
            'designation' => 'required|string|max:255',
            'qualification' => 'nullable|string|max:255',
            'address' => 'required|string',
            'joining_date' => 'required|date',
            'salary' => 'required|numeric|min:0',
            'can_approve_waivers' => 'boolean',
            'can_approve_refunds' => 'boolean',
            'max_waiver_amount' => 'nullable|numeric|min:0|required_if:can_approve_waivers,true',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Accountant name is required.',
            'email.required' => 'Email is required.',
            'email.unique' => 'This email is already in use.',
            'phone.required' => 'Phone number is required.',
            'phone.unique' => 'This phone number is already in use.',
            'password.required' => 'A password is required.',
            'password.confirmed' => 'Password confirmation does not match.',

            'employee_id.required' => 'Employee ID is required.',
            'employee_id.unique' => 'This employee ID is already in use.',
            'designation.required' => 'Designation is required.',
            'address.required' => 'Address is required.',
            'joining_date.required' => 'Joining date is required.',
            'salary.required' => 'Salary is required.',
            'salary.min' => 'Salary cannot be negative.',
            'max_waiver_amount.required_if' => 'Maximum waiver amount is required when approval permission is granted.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'can_approve_waivers' => $this->boolean('can_approve_waivers'),
            'can_approve_refunds' => $this->boolean('can_approve_refunds'),
        ]);
    }
}
