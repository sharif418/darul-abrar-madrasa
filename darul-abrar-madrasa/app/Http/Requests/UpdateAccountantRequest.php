<?php

namespace App\Http\Requests;

use App\Models\Accountant;
use Illuminate\Foundation\Http\FormRequest;

class UpdateAccountantRequest extends FormRequest
{
    public function authorize(): bool
    {
        $accountant = $this->route('accountant');

        return $this->user()
            && ($this->user()->can('update', $accountant)
                || (method_exists($this->user(), 'isAdmin') && $this->user()->isAdmin()));
    }

    public function rules(): array
    {
        $accountant = $this->route('accountant');
        $userId = is_object($accountant) ? $accountant->user_id : null;
        $accountantId = is_object($accountant) ? $accountant->id : null;

        return [
            // User fields
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $userId,
            'phone' => 'required|string|max:15|unique:users,phone,' . $userId,
            'password' => 'nullable|string|min:8|confirmed',

            // Accountant fields
            'employee_id' => 'required|string|unique:accountants,employee_id,' . $accountantId,
            'designation' => 'required|string|max:255',
            'qualification' => 'nullable|string|max:255',
            'address' => 'required|string',
            'joining_date' => 'required|date',
            'salary' => 'required|numeric|min:0',
            'can_approve_waivers' => 'boolean',
            'can_approve_refunds' => 'boolean',
            'max_waiver_amount' => 'nullable|numeric|min:0|required_if:can_approve_waivers,true',
            'is_active' => 'boolean',
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
            'is_active' => $this->boolean('is_active'),
        ]);
    }

    public function withValidator($validator)
    {
        $validator->after(function ($v) {
            $canApprove = $this->boolean('can_approve_waivers');
            $max = $this->input('max_waiver_amount');
            if ($canApprove && ($max === null || $max === '')) {
                $v->errors()->add('max_waiver_amount', 'Maximum waiver amount is required when approval permission is granted.');
            }
        });
    }
}
