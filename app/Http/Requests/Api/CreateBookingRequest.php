<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class CreateBookingRequest extends FormRequest
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
            'check_in_date' => 'required|date|after_or_equal:today',
            'nights' => 'nullable|integer|min:1|max:365',
            'occupancy' => 'required|array',
            'occupancy.adults' => 'required|integer|min:1|max:10',
            'occupancy.children' => 'nullable|array',
            'occupancy.children.*.name' => 'nullable|string|max:255',
            'occupancy.children.*.birthdate' => 'required_with:occupancy.children|date|before:check_in_date',
            'guest_info' => 'required|array',
            'guest_info.adults' => 'required|array',
            'guest_info.adults.*.name' => 'required|string|max:255',
            'guest_info.adults.*.birthdate' => 'nullable|date|before:check_in_date',
            'guest_info.email' => 'nullable|email|max:255',
            'guest_info.phone' => 'nullable|string|max:50',
            'room_type_id' => 'nullable|exists:room_types,id',
            'rate_plan_id' => 'nullable|exists:rate_plans,id',
            'extra_beds' => 'nullable|integer|min:0|max:5',
            'notes' => 'nullable|string|max:1000',
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
            'check_in_date.after_or_equal' => 'Check-in date cannot be in the past',
            'nights.min' => 'Minimum stay is 1 night',
            'nights.max' => 'Maximum stay is 365 nights',
            'occupancy.required' => 'Occupancy information is required',
            'occupancy.adults.required' => 'Number of adults is required',
            'occupancy.adults.min' => 'At least one adult is required',
            'occupancy.adults.max' => 'Maximum 10 adults allowed',
            'occupancy.children.*.birthdate.required_with' => 'Children birthdates are required',
            'guest_info.required' => 'Guest information is required',
            'guest_info.adults.required' => 'Adult guest information is required',
            'guest_info.adults.*.name.required' => 'Adult name is required',
            'guest_info.email.email' => 'Please provide a valid email address',
            'extra_beds.max' => 'Maximum 5 extra beds allowed',
            'notes.max' => 'Notes cannot exceed 1000 characters',
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
