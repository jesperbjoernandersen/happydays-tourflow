<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DailyPriceResource extends JsonResource
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
            'date' => $this['date'] ?? null,
            'day_of_week' => $this['day_of_week'] ?? null,
            'day_name' => $this['day_name'] ?? null,
            'is_available' => $this['is_available'] ?? true,
            'is_blocked' => $this['is_blocked'] ?? false,
            'has_rate' => $this['has_rate'] ?? false,
            'price' => $this['price'] ?? null,
            'base_price' => $this['base_price'] ?? null,
            'currency' => $this['currency'] ?? 'EUR',
            'rate_rule_id' => $this['rate_rule_id'] ?? null,
            'occupancy_pricing' => $this['occupancy_pricing'] ?? null,
            'minimum_stay' => $this['minimum_stay'] ?? null,
            'restrictions' => $this['restrictions'] ?? null,
        ];
    }
}
