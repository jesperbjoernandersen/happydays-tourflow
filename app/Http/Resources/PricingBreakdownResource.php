<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PricingBreakdownResource extends JsonResource
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
            'check_in_date' => $this['check_in_date'] ?? null,
            'nights' => $this['nights'] ?? 0,
            'currency' => $this['currency'] ?? 'EUR',
            'total_price' => $this['total_price'] ?? 0,
            'per_night_average' => $this['per_night_average'] ?? 0,
            'breakdown' => $this['breakdown'] ?? null,
            'rate_rule' => $this['rate_rule'] ? [
                'id' => $this['rate_rule']['id'] ?? null,
                'rate_plan_id' => $this['rate_rule']['rate_plan_id'] ?? null,
                'rate_plan_name' => $this['rate_rule']['rate_plan_name'] ?? null,
                'pricing_model' => $this['rate_rule']['pricing_model'] ?? null,
                'start_date' => $this['rate_rule']['start_date'] ?? null,
                'end_date' => $this['rate_rule']['end_date'] ?? null,
            ] : null,
            'stay_type' => $this['stay_type'] ? [
                'id' => $this['stay_type']['id'] ?? null,
                'name' => $this['stay_type']['name'] ?? null,
                'code' => $this['stay_type']['code'] ?? null,
                'nights' => $this['stay_type']['nights'] ?? null,
                'included_board_type' => $this['stay_type']['included_board_type'] ?? null,
            ] : null,
            'errors' => $this['errors'] ?? null,
        ];
    }
}
