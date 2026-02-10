<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookingResource extends JsonResource
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
            'booking_reference' => $this->booking_reference,
            'status' => $this->status,
            'check_in_date' => $this->check_in_date?->format('Y-m-d'),
            'check_out_date' => $this->check_out_date?->format('Y-m-d'),
            'nights' => $this->check_in_date && $this->check_out_date 
                ? $this->check_in_date->diffInDays($this->check_out_date) 
                : null,
            'currency' => $this->currency,
            'total_price' => $this->total_price,
            'guest_count' => $this->guest_count,
            'notes' => $this->notes,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
            'stay_type' => new StayTypeResource($this->whenLoaded('stayType')),
            'room_type' => new RoomTypeResource($this->whenLoaded('roomType')),
            'hotel' => new HotelResource($this->whenLoaded('hotel')),
            'guests' => BookingGuestResource::collection($this->whenLoaded('guests')),
            'price_breakdown' => $this->price_breakdown_json,
            'rate_rule_snapshot' => $this->rate_rule_snapshot,
            'hotel_age_policy_snapshot' => $this->hotel_age_policy_snapshot,
        ];
    }
}
