<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookingGuestResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'birthdate' => $this->birthdate?->format('Y-m-d'),
            'age_at_check_in' => $this->birthdate ? $this->birthdate->diffInYears($this->whenLoaded('booking')->check_in_date) : null,
            'guest_category' => $this->guest_category,
        ];
    }
}
