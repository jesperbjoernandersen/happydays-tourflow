<?php

namespace App\Actions;

use App\Models\Booking;
use App\Models\BookingGuest;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;

/**
 * CreateBookingSnapshot
 *
 * Creates a booking record with price snapshots when a booking is confirmed.
 * This locks in the price for auditing purposes.
 */
class CreateBookingSnapshot
{
    /**
     * Execute the booking snapshot creation.
     *
     * @param array $data Booking data including guest information and price snapshots
     * @return Booking The created booking with guests
     *
     * @throws InvalidArgumentException If required data is missing or invalid
     */
    public function execute(array $data): Booking
    {
        // Validate required fields
        $this->validateInput($data);

        // Generate unique booking reference
        $bookingReference = $this->generateBookingReference();

        // Create booking within a transaction
        $booking = DB::transaction(function () use ($data, $bookingReference) {
            // Create the booking record
            $booking = Booking::create([
                'booking_reference' => $bookingReference,
                'stay_type_id' => $data['stay_type_id'],
                'room_type_id' => $data['room_type_id'],
                'hotel_id' => $data['hotel_id'],
                'check_in_date' => $data['check_in_date'],
                'check_out_date' => $data['check_out_date'],
                'total_price' => $data['total_price'],
                'currency' => $data['currency'] ?? 'EUR',
                'status' => 'confirmed',
                'hotel_age_policy_snapshot' => $data['hotel_age_policy_snapshot'] ?? null,
                'rate_rule_snapshot' => $data['rate_rule_snapshot'] ?? null,
                'price_breakdown_json' => $data['price_breakdown_json'] ?? null,
                'guest_count' => count($data['guests']),
                'notes' => $data['notes'] ?? null,
            ]);

            // Create guest records with age calculations
            $this->createGuestRecords($booking, $data['guests']);

            return $booking;
        });

        return $booking;
    }

    /**
     * Validate required input data.
     *
     * @param array $data
     * @throws InvalidArgumentException
     */
    private function validateInput(array $data): void
    {
        $requiredFields = [
            'stay_type_id',
            'room_type_id',
            'hotel_id',
            'check_in_date',
            'check_out_date',
            'total_price',
            'guests',
        ];

        foreach ($requiredFields as $field) {
            if (!isset($data[$field])) {
                throw new InvalidArgumentException("Missing required field: {$field}");
            }
        }

        if (!is_array($data['guests']) || empty($data['guests'])) {
            throw new InvalidArgumentException('Guests must be a non-empty array');
        }

        if (!is_numeric($data['total_price']) || $data['total_price'] < 0) {
            throw new InvalidArgumentException('Total price must be a non-negative number');
        }

        // Validate dates
        $checkIn = Carbon::parse($data['check_in_date']);
        $checkOut = Carbon::parse($data['check_out_date']);

        if ($checkOut->lte($checkIn)) {
            throw new InvalidArgumentException('Check-out date must be after check-in date');
        }
    }

    /**
     * Generate a unique booking reference.
     *
     * Format: BK + YYYYMMDD + random 6 characters
     *
     * @return string
     */
    private function generateBookingReference(): string
    {
        $datePart = Carbon::now()->format('Ymd');
        $randomPart = Str::upper(Str::random(6));

        // Ensure uniqueness by checking if it exists
        $reference = "BK{$datePart}{$randomPart}";

        while (Booking::where('booking_reference', $reference)->exists()) {
            $randomPart = Str::upper(Str::random(6));
            $reference = "BK{$datePart}{$randomPart}";
        }

        return $reference;
    }

    /**
     * Create guest records with calculated ages and categories.
     *
     * @param Booking $booking
     * @param array $guests
     */
    private function createGuestRecords(Booking $booking, array $guests): void
    {
        foreach ($guests as $guestData) {
            $birthdate = Carbon::parse($guestData['birthdate']);
            $age = $birthdate->age;
            $category = $this->determineGuestCategory($age);

            BookingGuest::create([
                'booking_id' => $booking->id,
                'name' => $guestData['name'],
                'birthdate' => $birthdate,
                'guest_category' => $category,
            ]);
        }
    }

    /**
     * Determine guest category based on age.
     *
     * @param int $age
     * @return string
     */
    private function determineGuestCategory(int $age): string
    {
        if ($age < 2) {
            return 'infant';
        } elseif ($age < 12) {
            return 'child';
        } elseif ($age < 18) {
            return 'teen';
        } else {
            return 'adult';
        }
    }
}
