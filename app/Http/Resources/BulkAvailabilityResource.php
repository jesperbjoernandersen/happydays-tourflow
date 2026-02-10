<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BulkAvailabilityResource extends JsonResource
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
            'total_requests' => $this['total_requests'] ?? 0,
            'successful_requests' => $this['successful_requests'] ?? 0,
            'failed_requests' => $this['failed_requests'] ?? 0,
            'available_count' => $this['available_count'] ?? 0,
            'unavailable_count' => $this['unavailable_count'] ?? 0,
            'results' => $this['results'] ?? [],
        ];
    }
}
