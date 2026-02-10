<?php

namespace App\Actions;

use App\Models\Allotment;
use App\Models\Booking;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use RuntimeException;

/**
 * CancelBooking
 *
 * Handles booking cancellations, restores inventory, and calculates refunds.
 * Uses database transactions for safety and ensures data consistency.
 */
class CancelBooking
{
    /**
     * Default cancellation policy percentages by days before check-in.
     */
    private const CANCELLATION_POLICY = [
        0 => 0.0,      // Same day: 0% refund (100% penalty)
        1 => 0.5,      // 1 day before: 50% refund
        3 => 0.7,      // 3 days before: 70% refund
        7 => 0.9,      // 7 days before: 90% refund
        14 => 1.0,     // 14+ days before: 100% refund
    ];

    /**
     * Execute the booking cancellation.
     *
     * @param int $bookingId The booking ID to cancel
     * @param string $reason The cancellation reason
     * @param bool $notifyCustomer Whether to notify the customer
     * @param int|null $cancelledByUserId The user ID who cancelled (if authenticated)
     * @return array Cancellation result with status and refund details
     *
     * @throws InvalidArgumentException If input parameters are invalid
     * @throws RuntimeException If booking cannot be cancelled
     */
    public function execute(
        int $bookingId,
        string $reason,
        bool $notifyCustomer = true,
        ?int $cancelledByUserId = null
    ): array {
        // Validate input parameters
        if ($bookingId <= 0) {
            throw new InvalidArgumentException('Booking ID must be a positive integer');
        }

        if (empty(trim($reason))) {
            throw new InvalidArgumentException('Cancellation reason is required');
        }

        return DB::transaction(function () use ($bookingId, $reason, $notifyCustomer, $cancelledByUserId) {
            return $this->performCancellation($bookingId, $reason, $notifyCustomer, $cancelledByUserId);
        });
    }

    /**
     * Perform the cancellation logic within a transaction.
     *
     * @param int $bookingId
     * @param string $reason
     * @param bool $notifyCustomer
     * @param int|null $cancelledByUserId
     * @return array
     */
    private function performCancellation(
        int $bookingId,
        string $reason,
        bool $notifyCustomer,
        ?int $cancelledByUserId
    ): array {
        // Find the booking
        $booking = Booking::find($bookingId);

        if (!$booking) {
            throw new RuntimeException("Booking with ID {$bookingId} not found");
        }

        // Validate booking can be cancelled
        $this->validateBookingCanBeCancelled($booking);

        // Calculate refund amount
        $refundAmount = $this->calculateRefundAmount($booking);

        // Restore inventory (allotments)
        $restoredAllotments = $this->restoreAllotments($booking);

        // Update booking status
        $booking->status = 'cancelled';
        $booking->notes = $this->appendCancellationNote($booking, $reason, $cancelledByUserId);
        $booking->save();

        // Log cancellation (audit trail)
        $this->logCancellation($booking, $reason, $refundAmount, $cancelledByUserId);

        // Trigger customer notification if requested
        if ($notifyCustomer) {
            $this->sendCancellationNotification($booking, $refundAmount);
        }

        return [
            'status' => 'cancelled',
            'booking_id' => $booking->id,
            'booking_reference' => $booking->booking_reference,
            'refund_amount' => $refundAmount,
            'total_price' => $booking->total_price,
            'currency' => $booking->currency,
            'cancellation_reason' => $reason,
            'cancelled_at' => now()->toIso8601String(),
            'cancelled_by' => $cancelledByUserId,
            'allotments_restored' => $restoredAllotments,
            'customer_notified' => $notifyCustomer,
        ];
    }

    /**
     * Validate that the booking can be cancelled.
     *
     * @param Booking $booking
     * @throws RuntimeException If booking cannot be cancelled
     */
    private function validateBookingCanBeCancelled(Booking $booking): void
    {
        $cancellableStatuses = ['pending', 'confirmed'];

        if (!in_array($booking->status, $cancellableStatuses, true)) {
            $nonCancellableStatuses = ['checked_in', 'checked_out', 'cancelled'];
            throw new RuntimeException(
                "Cannot cancel booking with status '{$booking->status}'. " .
                "Only " . implode(', ', $cancellableStatuses) . " bookings can be cancelled."
            );
        }

        // Additional validation: cannot cancel if check-in has already passed
        if ($booking->check_in_date->isPast()) {
            throw new RuntimeException(
                "Cannot cancel booking: check-in date ({$booking->check_in_date->format('Y-m-d')}) has already passed"
            );
        }
    }

    /**
     * Calculate the refund amount based on cancellation policy.
     *
     * @param Booking $booking
     * @return float The refund amount
     */
    private function calculateRefundAmount(Booking $booking): float
    {
        $totalPrice = (float) $booking->total_price;
        $daysUntilCheckIn = Carbon::today()->diffInDays($booking->check_in_date, false);

        $refundPercentage = $this->getRefundPercentage($daysUntilCheckIn);
        
        // Calculate refund amount
        $refundAmount = $totalPrice * $refundPercentage;

        // In a real implementation, you would also consider:
        // - Already paid amounts (deposits, partial payments)
        // - Payment gateway fees
        // - Any non-refundable fees
        
        return round($refundAmount, 2);
    }

    /**
     * Get the refund percentage based on days until check-in.
     *
     * @param int $daysUntilCheckIn
     * @return float The refund percentage (0.0 to 1.0)
     */
    private function getRefundPercentage(int $daysUntilCheckIn): float
    {
        foreach (self::CANCELLATION_POLICY as $thresholdDays => $percentage) {
            if ($daysUntilCheckIn <= $thresholdDays) {
                return $percentage;
            }
        }

        // Default to 100% refund for very early cancellations
        return 1.0;
    }

    /**
     * Restore allotments for all dates in the booking.
     *
     * @param Booking $booking
     * @return array Details of restored allotments
     *
     * @throws RuntimeException If any allotment is not found
     */
    private function restoreAllotments(Booking $booking): array
    {
        $dates = $this->generateStayDates($booking->check_in_date, $booking->check_out_date);
        $restoredAllotments = [];

        foreach ($dates as $date) {
            $allotment = Allotment::where('room_type_id', $booking->room_type_id)
                ->whereDate('date', $date->format('Y-m-d'))
                ->lockForUpdate()
                ->first();

            if (!$allotment) {
                throw new RuntimeException(
                    "No allotment found for room type {$booking->room_type_id} on date {$date->format('Y-m-d')}"
                );
            }

            // Only restore if allocation is greater than 0
            if ($allotment->allocated > 0) {
                $allotment->allocated -= 1;
                $allotment->save();
            }

            $restoredAllotments[] = [
                'date' => $date->format('Y-m-d'),
                'room_type_id' => $booking->room_type_id,
                'allocated' => $allotment->allocated,
                'quantity' => $allotment->quantity,
                'remaining' => $allotment->quantity - $allotment->allocated,
            ];
        }

        return [
            'dates' => $restoredAllotments,
            'total_restored' => count($restoredAllotments),
        ];
    }

    /**
     * Generate all dates for the stay (check-in to check-out).
     *
     * @param Carbon $checkinDate
     * @param Carbon $checkoutDate
     * @return array Array of Carbon dates
     */
    private function generateStayDates(Carbon $checkinDate, Carbon $checkoutDate): array
    {
        $dates = [];
        $nights = $checkinDate->diffInDays($checkoutDate);

        for ($i = 0; $i < $nights; $i++) {
            $dates[] = $checkinDate->copy()->addDays($i);
        }

        return $dates;
    }

    /**
     * Append cancellation details to the booking notes.
     *
     * @param Booking $booking
     * @param string $reason
     * @param int|null $cancelledByUserId
     * @return string Updated notes
     */
    private function appendCancellationNote(Booking $booking, string $reason, ?int $cancelledByUserId): string
    {
        $existingNotes = $booking->notes ?? '';
        $cancellationNote = sprintf(
            "[%s] CANCELLED - Reason: %s | Cancelled by user ID: %s\n",
            now()->format('Y-m-d H:i:s'),
            $reason,
            $cancelledByUserId ?? 'System'
        );

        return $existingNotes . $cancellationNote;
    }

    /**
     * Log cancellation for audit trail.
     *
     * @param Booking $booking
     * @param string $reason
     * @param float $refundAmount
     * @param int|null $cancelledByUserId
     */
    private function logCancellation(Booking $booking, string $reason, float $refundAmount, ?int $cancelledByUserId): void
    {
        // In a real implementation, you might:
        // - Write to an audit log table
        // - Dispatch a job for async processing
        // - Send to external logging service
        
        $logEntry = [
            'booking_id' => $booking->id,
            'booking_reference' => $booking->booking_reference,
            'previous_status' => $booking->getOriginal('status'),
            'new_status' => 'cancelled',
            'cancellation_reason' => $reason,
            'refund_amount' => $refundAmount,
            'cancelled_by' => $cancelledByUserId,
            'cancelled_at' => now()->toIso8601String(),
            'stay_dates' => [
                'check_in' => $booking->check_in_date->format('Y-m-d'),
                'check_out' => $booking->check_out_date->format('Y-m-d'),
            ],
        ];

        // For now, we'll just log to the Laravel log
        \Log::channel('booking')->info('Booking cancelled', $logEntry);
    }

    /**
     * Send cancellation notification to customer.
     *
     * @param Booking $booking
     * @param float $refundAmount
     */
    private function sendCancellationNotification(Booking $booking, float $refundAmount): void
    {
        // In a real implementation, you might:
        // - Send email notification
        // - Send SMS notification
        // - Push notification to mobile app
        
        // For now, we'll just log the intent
        \Log::channel('booking')->info('Cancellation notification should be sent', [
            'booking_id' => $booking->id,
            'customer_email' => $booking->guests->first()?->email ?? 'N/A',
            'refund_amount' => $refundAmount,
            'currency' => $booking->currency,
        ]);
    }
}
