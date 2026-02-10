<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StayTypeResource extends JsonResource
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
            'hotel_id' => $this->hotel_id,
            'name' => $this->name,
            'description' => $this->description,
            'code' => $this->code,
            'nights' => $this->nights,
            'included_board_type' => $this->included_board_type,
            'is_active' => $this->is_active,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
            'hotel' => $this->whenLoaded('hotel', fn() => [
                'id' => $this->hotel->id,
                'name' => $this->hotel->name,
                'code' => $this->hotel->code,
                'city' => $this->hotel->city,
                'country' => $this->hotel->country,
            ]),
            'age_policy' => $this->whenLoaded('hotel', function () {
                if ($this->hotel->relationLoaded('agePolicies')) {
                    return $this->hotel->agePolicies->map(fn($policy) => [
                        'id' => $policy->id,
                        'name' => $policy->name,
                        'infant_max_age' => $policy->infant_max_age,
                        'child_max_age' => $policy->child_max_age,
                        'adult_min_age' => $policy->adult_min_age,
                    ]);
                }
                return null;
            }),
            'pricing_hints' => $this->whenLoaded('rateRules', function () {
                return $this->rateRules->map(fn($rule) => [
                    'rate_plan_id' => $rule->rate_plan_id,
                    'rate_plan_name' => $rule->ratePlan->name ?? null,
                    'base_price' => $rule->base_price,
                    'price_per_adult' => $rule->price_per_adult,
                    'price_per_child' => $rule->price_per_child,
                    'price_per_infant' => $rule->price_per_infant,
                    'included_occupancy' => $rule->included_occupancy,
                ]);
            }),
        ];
    }
}
