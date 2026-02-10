<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class GetPriceBreakdownRequest extends FormRequest
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
            'stay_type_id' => 'required|exists:stay_types,id',
            'check_in_date' => 'required|date',
            'nights' => 'required|integer|min:1|max:365',
            'occupancy' => 'nullable|array',
            'occupancy.adults' => 'nullable|integer|min:1|max:20',
            'occupancy.children' => 'nullable|integer|min:0|max:20',
            'occupancy.infants' => 'nullable|integer|min:0|max:20',
            'room_type_id' => 'nullable|exists:room_types,id',
            'rate_plan_id' => 'nullable|exists:rate_plans,id',
            'extra_beds' => 'nullable|integer|min:0|max:10',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'stay_type_id.required' => 'Stay type ID is required',
            'stay_type_id.exists' => 'Stay type not found',
            'check_in_date.required' => 'Check-in date is required',
            'check_in_date.date' => 'Check-in date must be a valid date',
            'nights.required' => 'Number of nights is required',
            'nights.min' => 'Minimum stay is 1 night',
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
