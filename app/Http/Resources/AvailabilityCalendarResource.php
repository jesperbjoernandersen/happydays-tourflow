<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AvailabilityCalendarResource extends JsonResource
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
            'stay_type_id' => $this['stay_type_id'] ?? null,
            'stay_type_name' => $this['stay_type_name'] ?? null,
            'year' => $this['year'] ?? null,
            'month' => $this['month'] ?? null,
            'month_name' => $this['month_name'] ?? null,
            'currency' => $this['currency'] ?? 'EUR',
            'summary' => $this['summary'] ?? [
                'total_days' => 0,
                'available_days' => 0,
                'min_price' => null,
                'max_price' => null,
                'avg_price' => null,
            ],
            'available_dates' => $this['available_dates'] ?? [],
            'days' => $this['days'] ?? [],
            'stay_type' => $this['stay_type'] ?? null,
            'occupancy' => $this['occupancy'] ?? null,
            'room_type' => $this['room_type'] ?? null,
            'rate_plan' => $this['rate_plan'] ?? null,
        ];
    }
}
