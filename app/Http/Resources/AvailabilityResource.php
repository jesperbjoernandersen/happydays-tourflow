<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AvailabilityResource extends JsonResource
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
            'success' => $this['success'] ?? true,
            'is_available' => $this['is_available'] ?? false,
            'stay_type_id' => $this['stay_type_id'] ?? null,
            'stay_type_name' => $this['stay_type_name'] ?? null,
            'check_in_date' => $this['check_in_date'] ?? null,
            'check_out_date' => $this['check_out_date'] ?? null,
            'nights' => $this['nights'] ?? 1,
            'currency' => $this['currency'] ?? 'EUR',
            'total_price' => $this['total_price'] ?? 0,
            'per_night_average' => $this['per_night_average'] ?? 0,
            'available_dates' => $this['available_dates'] ?? [],
            'minimum_stay_met' => $this['minimum_stay_met'] ?? true,
            'maximum_stay_met' => $this['maximum_stay_met'] ?? true,
            'occupancy' => $this['occupancy'] ?? [
                'adults' => 2,
                'children' => 0,
                'infants' => 0,
                'total_guests' => 2,
            ],
            'extra_beds' => $this['extra_beds'] ?? 0,
            'rate_rule' => $this['rate_rule'] ?? null,
            'restrictions' => $this['restrictions'] ?? null,
            'stay_type' => $this['stay_type'] ?? null,
            'room_type' => $this['room_type'] ?? null,
        ];
    }
}
