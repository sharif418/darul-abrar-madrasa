<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreFeeRequest extends FormRequest
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
            'student_id' => ['required', 'exists:students,id'],
            'fee_type' => ['required', 'string', 'in:admission,monthly,exam,library,transport,hostel,other'],
            'amount' => ['required', 'numeric', 'min:0'],
            'due_date' => ['required', 'date', 'after_or_equal:today'],
            'status' => ['required', 'in:paid,unpaid,partial'],
            'paid_amount' => ['nullable', 'numeric', 'min:0', 'lte:amount'],
            'payment_method' => ['nullable', 'string', 'required_if:status,paid,partial'],
            'transaction_id' => ['nullable', 'string'],
            'remarks' => ['nullable', 'string'],
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
            'student_id.required' => 'Please select a student.',
            'student_id.exists' => 'Selected student does not exist.',
            'fee_type.required' => 'Fee type is required.',
            'fee_type.in' => 'Invalid fee type selected.',
            'amount.required' => 'Fee amount is required.',
            'amount.numeric' => 'Fee amount must be a number.',
            'amount.min' => 'Fee amount must be at least 0.',
            'due_date.required' => 'Due date is required.',
            'due_date.after_or_equal' => 'Due date must be today or a future date.',
            'status.required' => 'Payment status is required.',
            'status.in' => 'Invalid payment status.',
            'paid_amount.numeric' => 'Paid amount must be a number.',
            'paid_amount.min' => 'Paid amount must be at least 0.',
            'paid_amount.lte' => 'Paid amount cannot exceed total amount.',
            'payment_method.required_if' => 'Payment method is required when status is paid or partial.',
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
            $status = $this->input('status');
            $amount = $this->input('amount');
            $paidAmount = $this->input('paid_amount', 0);

            if ($status === 'paid' && $paidAmount != $amount) {
                $validator->errors()->add('paid_amount', 'Paid amount must equal total amount when status is paid.');
            }

            if ($status === 'partial' && ($paidAmount <= 0 || $paidAmount >= $amount)) {
                $validator->errors()->add('paid_amount', 'Paid amount must be greater than 0 and less than total amount for partial payment.');
            }

            if ($status === 'unpaid' && $paidAmount > 0) {
                $validator->errors()->add('paid_amount', 'Paid amount must be 0 when status is unpaid.');
            }
        });
    }
}
