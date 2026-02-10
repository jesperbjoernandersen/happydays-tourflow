<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class GetAvailabilityCalendarRequest extends FormRequest
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
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'year' => 'required|integer|min:2020|max:2100',
            'month' => 'required|integer|min:1|max:12',
            'occupancy' => 'nullable|array',
            'occupancy.adults' => 'nullable|integer|min:1',
            'occupancy.children' => 'nullable|integer|min:0',
            'occupancy.infants' => 'nullable|integer|min:0',
            'room_type_id' => 'nullable|exists:room_types,id',
            'rate_plan_id' => 'nullable|exists:rate_plans,id',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'year.required' => 'Year is required',
            'year.integer' => 'Year must be a number',
            'year.min' => 'Year must be 2020 or later',
            'year.max' => 'Year must be 2100 or earlier',
            'month.required' => 'Month is required',
            'month.integer' => 'Month must be a number',
            'month.min' => 'Month must be between 1 and 12',
            'month.max' => 'Month must be between 1 and 12',
        ];
    }

    /**
     * Handle a failed validation attempt.
     */
    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422)
        );
    }
}
