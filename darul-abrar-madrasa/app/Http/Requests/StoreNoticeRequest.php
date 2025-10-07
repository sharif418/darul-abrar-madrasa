<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreNoticeRequest extends FormRequest
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
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'publish_date' => ['required', 'date'],
            'expiry_date' => ['nullable', 'date', 'after:publish_date'],
            'notice_for' => ['required', 'in:all,students,teachers,staff'],
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
            'title.required' => 'Notice title is required.',
            'title.max' => 'Notice title must not exceed 255 characters.',
            'description.required' => 'Notice description is required.',
            'publish_date.required' => 'Publish date is required.',
            'publish_date.date' => 'Please provide a valid publish date.',
            'expiry_date.date' => 'Please provide a valid expiry date.',
            'expiry_date.after' => 'Expiry date must be after publish date.',
            'notice_for.required' => 'Please select target audience.',
            'notice_for.in' => 'Invalid target audience selected.',
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
