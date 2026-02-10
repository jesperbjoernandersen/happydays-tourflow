<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PricingCalendarResource extends JsonResource
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
            'success' => true,
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
            'days' => $this['days'] ?? [],
        ];
    }
}
