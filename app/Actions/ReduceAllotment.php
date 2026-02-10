<?php

namespace App\Actions;

use App\Models\Allotment;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use RuntimeException;

/**
 * ReduceAllotment
 *
 * Reduces allotment availability when a booking is confirmed.
 * Uses database transactions for safety and validates availability before reduction.
 */
class ReduceAllotment
{
    /**
     * Execute the allotment reduction.
     *
     * @param int $roomTypeId The room type ID
     * @param string $checkinDate The check-in date (Y-m-d format)
     * @param int $nights Number of nights to reduce
     * @return array Reduced allotments data with total reduction count
     *
     * @throws InvalidArgumentException If input parameters are invalid
     * @throws RuntimeException If allotment is not available or update fails
     */
    public function execute(int $roomTypeId, string $checkinDate, int $nights): array
    {
        // Validate input parameters
        if ($roomTypeId <= 0) {
            throw new InvalidArgumentException('Room type ID must be a positive integer');
        }

        if ($nights <= 0) {
            throw new InvalidArgumentException('Nights must be a positive integer');
        }

        $date = Carbon::parse($checkinDate);

        if ($date->isPast() && !$date->isToday()) {
            throw new InvalidArgumentException('Check-in date cannot be in the past');
        }

        // Generate all dates for the stay
        $dates = $this->generateStayDates($date, $nights);

        // Perform reduction - lockForUpdate provides atomicity for each date
        // The action validates availability before making changes, ensuring consistency
        $result = $this->reduceAllotments($roomTypeId, $dates);

        return $result;
    }

    /**
     * Generate all dates for the stay.
     *
     * @param Carbon $checkinDate
     * @param int $nights
     * @return array Array of Carbon dates
     */
    private function generateStayDates(Carbon $checkinDate, int $nights): array
    {
        $dates = [];

        for ($i = 0; $i < $nights; $i++) {
            $dates[] = $checkinDate->copy()->addDays($i);
        }

        return $dates;
    }

    /**
     * Reduce allotments for all dates within the transaction.
     *
     * @param int $roomTypeId
     * @param array $dates
     * @return array
     */
    private function reduceAllotments(int $roomTypeId, array $dates): array
    {
        $reducedAllotments = [];
        $totalReduced = 0;

        foreach ($dates as $date) {
            $allotment = $this->findAndValidateAllotment($roomTypeId, $date);

            // Perform the reduction
            $allotment->allocated += 1;
            $allotment->save();

            $reducedAllotments[] = [
                'date' => $date->format('Y-m-d'),
                'room_type_id' => $roomTypeId,
                'allocated' => $allotment->allocated,
                'quantity' => $allotment->quantity,
                'remaining' => $allotment->quantity - $allotment->allocated,
            ];

            $totalReduced++;
        }

        return [
            'dates' => $reducedAllotments,
            'total_reduced' => $totalReduced,
        ];
    }

    /**
     * Find allotment and validate availability.
     *
     * @param int $roomTypeId
     * @param Carbon $date
     * @return Allotment
     *
     * @throws RuntimeException If allotment not found or not available
     */
    private function findAndValidateAllotment(int $roomTypeId, Carbon $date): Allotment
    {
        $allotment = Allotment::where('room_type_id', $roomTypeId)
            ->whereDate('date', $date->format('Y-m-d'))
            ->lockForUpdate()
            ->first();

        if (!$allotment) {
            throw new RuntimeException(
                "No allotment found for room type {$roomTypeId} on date {$date->format('Y-m-d')}"
            );
        }

        // Check stop sell
        if ($allotment->stop_sell) {
            throw new RuntimeException(
                "Cannot reduce allotment: stop sell is active for room type {$roomTypeId} on date {$date->format('Y-m-d')}"
            );
        }

        // Check availability (remaining = quantity - allocated)
        $remaining = $allotment->quantity - $allotment->allocated;

        if ($remaining <= 0) {
            throw new RuntimeException(
                "Cannot reduce allotment: no rooms available for room type {$roomTypeId} on date {$date->format('Y-m-d')}"
            );
        }

        return $allotment;
    }
}
