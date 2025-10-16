<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Period;

class UpdatePeriodRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
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
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'day_of_week' => ['required', 'in:monday,tuesday,wednesday,thursday,friday,saturday,sunday'],
            'order' => ['required', 'integer', 'min:0'],
            'is_active' => ['boolean'],
        ];
    }

    /**
     * Get custom error messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Period name is required.',
            'start_time.required' => 'Start time is required.',
            'start_time.date_format' => 'Start time must be in HH:MM format (e.g., 08:00).',
            'end_time.required' => 'End time is required.',
            'end_time.date_format' => 'End time must be in HH:MM format (e.g., 09:00).',
            'end_time.after' => 'End time must be after start time.',
            'day_of_week.required' => 'Day of week is required.',
            'day_of_week.in' => 'Invalid day of week selected.',
            'order.required' => 'Order is required.',
            'order.integer' => 'Order must be a number.',
            'order.min' => 'Order must be at least 0.',
        ];
    }

    /**
     * Configure the validator instance.
     *
     * @param  \Illuminate\Validation\Validator  $validator
     * @return void
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if (!$validator->errors()->has('start_time') && 
                !$validator->errors()->has('end_time') && 
                !$validator->errors()->has('day_of_week')) {
                
                $startTime = $this->input('start_time');
                $endTime = $this->input('end_time');
                $dayOfWeek = $this->input('day_of_week');
                // Normalize route parameter to ID (handles both model binding and direct ID)
                $param = $this->route('period');
                $currentPeriodId = is_object($param) ? $param->id : $param;

                // Normalize times to H:i:s format for DB comparison
                $startTimeNormalized = $startTime . ':00';
                $endTimeNormalized = $endTime . ':00';

                // Check for overlapping periods on the same day, excluding current period
                $overlapping = Period::where('day_of_week', $dayOfWeek)
                    ->where('id', '!=', $currentPeriodId)
                    ->where(function ($query) use ($startTimeNormalized, $endTimeNormalized) {
                        $query->where(function ($q) use ($startTimeNormalized, $endTimeNormalized) {
                            $q->where('start_time', '<', $endTimeNormalized)
                              ->where('end_time', '>', $startTimeNormalized);
                        });
                    })
                    ->exists();

                if ($overlapping) {
                    $validator->errors()->add('start_time', 'This period overlaps with an existing period on the same day.');
                }
            }
        });
    }
}
