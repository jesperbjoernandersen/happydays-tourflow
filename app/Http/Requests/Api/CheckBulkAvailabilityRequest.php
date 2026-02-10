<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class CheckBulkAvailabilityRequest extends FormRequest
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
            'requests' => 'required|array|min:1|max:50',
            'requests.*.stay_type_id' => [
                'required',
                'integer',
                Rule::exists('stay_types', 'id'),
            ],
            'requests.*.check_in_date' => 'required|date|after_or_equal:today',
            'requests.*.nights' => 'nullable|integer|min:1|max:365',
            'requests.*.occupancy' => 'nullable|array',
            'requests.*.occupancy.adults' => 'nullable|integer|min:1',
            'requests.*.occupancy.children' => 'nullable|integer|min:0',
            'requests.*.occupancy.infants' => 'nullable|integer|min:0',
            'requests.*.room_type_id' => 'nullable|exists:room_types,id',
            'requests.*.rate_plan_id' => 'nullable|exists:rate_plans,id',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'requests.required' => 'At least one availability request is required',
            'requests.max' => 'Maximum 50 availability checks allowed per request',
            'requests.*.stay_type_id.required' => 'Stay type ID is required for each request',
            'requests.*.stay_type_id.exists' => 'Stay type not found',
            'requests.*.check_in_date.required' => 'Check-in date is required for each request',
            'requests.*.check_in_date.date' => 'Check-in date must be a valid date',
            'requests.*.check_in_date.after_or_equal' => 'Check-in date cannot be in the past',
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
