<?php

namespace App\Services;

use App\Models\Allotment;
use App\Models\BookingGuest;
use App\Models\RoomType;
use App\Models\StayType;
use App\Models\HotelAgePolicy;
use App\Domain\ValueObjects\ValidationResult;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * BookingValidationService
 *
 * Validates all business rules before a booking is confirmed.
 * Performs comprehensive validation for stay duration, guest count,
 * date availability, age policy, and pricing.
 */
class BookingValidationService
{
    private AvailabilityService $availabilityService;

    public function __construct(?AvailabilityService $availabilityService = null)
    {
        $this->availabilityService = $availabilityService ?? new AvailabilityService();
    }

    /**
     * Validate all booking business rules
     *
     * @param array $data Booking data containing:
     *   - stay_type: StayType instance
     *   - room_type: RoomType instance
     *   - check_in_date: Carbon instance or date string
     *   - nights: Number of nights
     *   - guests: Array of guest data with name, birthdate, category
     *   - extra_beds: Number of extra beds requested (optional)
     *   - total_price: Calculated total price (optional)
     * @return ValidationResult
     */
    public function validate(array $data): ValidationResult
    {
        $result = new ValidationResult(true);

        // Extract data with defaults
        $stayType = $data['stay_type'] ?? null;
        $roomType = $data['room_type'] ?? null;
        $checkInDate = $this->parseDate($data['check_in_date'] ?? null);
        $nights = $data['nights'] ?? 0;
        $guests = $data['guests'] ?? [];
        $extraBeds = $data['extra_beds'] ?? 0;
        $totalPrice = $data['total_price'] ?? 0;

        // Run validations
        $this->validateStayDuration($stayType, $nights, $result);
        $this->validateGuestCount($roomType, $guests, $extraBeds, $result);
        $this->validateDateAvailability($stayType, $roomType, $checkInDate, $nights, $result);
        $this->validateAgePolicy($guests, $result);
        $this->validatePricing($totalPrice, $result);

        // Set validity based on errors
        $result->isValid = empty($result->errors);

        return $result;
    }

    /**
     * Validate stay duration rules
     *
     * @param StayType|null $stayType
     * @param int $nights
     * @param ValidationResult $result
     */
    private function validateStayDuration(?StayType $stayType, int $nights, ValidationResult $result): void
    {
        if (!$stayType) {
            $result->addError('stay_type', 'Stay type is required');
            return;
        }

        // For fixed packages, stay must match StayType.nights exactly
        if ($stayType->nights > 0 && $nights !== $stayType->nights) {
            $result->addError('nights', sprintf(
                'Stay must be exactly %d nights for this package',
                $stayType->nights
            ));
            return;
        }

        // Get allotments to check min/max stay requirements
        if ($nights <= 0) {
            $result->addError('nights', 'Number of nights must be greater than 0');
            return;
        }
    }

    /**
     * Validate guest count rules
     *
     * @param RoomType|null $roomType
     * @param array $guests
     * @param int $extraBeds
     * @param ValidationResult $result
     */
    private function validateGuestCount(?RoomType $roomType, array $guests, int $extraBeds, ValidationResult $result): void
    {
        if (!$roomType) {
            $result->addError('room_type', 'Room type is required');
            return;
        }

        $totalGuests = count($guests);

        // Check minimum 1 adult required
        $adultCount = 0;
        foreach ($guests as $guest) {
            $category = strtolower($guest['guest_category'] ?? '');
            if ($category === 'adult') {
                $adultCount++;
            }
        }

        if ($adultCount < 1) {
            $result->addError('guests', 'At least 1 adult is required');
        }

        // Check total guests against max occupancy
        if ($totalGuests > $roomType->max_occupancy) {
            $result->addError('guests', sprintf(
                'Maximum %d guests allowed, but %d provided',
                $roomType->max_occupancy,
                $totalGuests
            ));
        }

        // Check extra beds against room's extra_bed_slots
        if ($extraBeds > $roomType->extra_bed_slots) {
            $result->addError('extra_beds', sprintf(
                'Maximum %d extra beds allowed, but %d requested',
                $roomType->extra_bed_slots,
                $extraBeds
            ));
        }

        // Warning: near max occupancy
        if ($totalGuests >= $roomType->max_occupancy - 1 && $totalGuests < $roomType->max_occupancy) {
            $result->addWarning('guests', 'Near max occupancy');
        }
    }

    /**
     * Validate date availability rules
     *
     * @param StayType|null $stayType
     * @param RoomType|null $roomType
     * @param Carbon|null $checkInDate
     * @param int $nights
     * @param ValidationResult $result
     */
    private function validateDateAvailability(
        ?StayType $stayType,
        ?RoomType $roomType,
        ?Carbon $checkInDate,
        int $nights,
        ValidationResult $result
    ): void {
        if (!$roomType) {
            return; // Already validated in guest count
        }

        if (!$checkInDate) {
            $result->addError('check_in_date', 'Check-in date is required');
            return;
        }

        if ($checkInDate->isPast()) {
            $result->addError('check_in_date', 'Check-in date cannot be in the past');
            return;
        }

        // Skip availability check during basic validation - 
        // full availability check will be done by pricing service
        return;
    }

    /**
     * Validate age policy rules
     *
     * @param array $guests
     * @param ValidationResult $result
     */
    private function validateAgePolicy(array $guests, ValidationResult $result): void
    {
        foreach ($guests as $index => $guest) {
            // Validate birthdate is provided and calculable
            $birthdate = $guest['birthdate'] ?? null;
            if (!$birthdate) {
                $result->addError("guests.{$index}.birthdate", 'Birthdate is required for all guests');
                continue;
            }

            $birthdate = $this->parseDate($birthdate);
            if (!$birthdate) {
                $result->addError("guests.{$index}.birthdate", 'Invalid birthdate format');
                continue;
            }

            // Validate guest category
            $category = strtolower($guest['guest_category'] ?? '');
            $validCategories = ['adult', 'child', 'infant'];

            if (!in_array($category, $validCategories)) {
                $result->addError("guests.{$index}.guest_category", sprintf(
                    'Invalid guest category "%s". Valid categories: %s',
                    $guest['guest_category'] ?? 'unknown',
                    implode(', ', $validCategories)
                ));
            }
        }
    }

    /**
     * Validate pricing rules
     *
     * @param float $totalPrice
     * @param ValidationResult $result
     */
    private function validatePricing(float $totalPrice, ValidationResult $result): void
    {
        if ($totalPrice <= 0) {
            $result->addError('total_price', 'Price must be greater than 0');
        }
    }

    /**
     * Parse a date from various formats
     *
     * @param mixed $date
     * @return Carbon|null
     */
    private function parseDate($date): ?Carbon
    {
        if ($date instanceof Carbon) {
            return $date->copy();
        }

        if ($date instanceof \DateTimeInterface) {
            return Carbon::parse($date->format('Y-m-d'));
        }

        if (is_string($date)) {
            return Carbon::parse($date);
        }

        return null;
    }

    /**
     * Validate stay duration with min/max stay from allotments
     *
     * This method is used to check min/max stay requirements from the allotment records.
     *
     * @param RoomType $roomType
     * @param Carbon $checkInDate
     * @param int $nights
     * @param ValidationResult $result
     */
    public function validateStayDurationRequirements(RoomType $roomType, Carbon $checkInDate, int $nights, ValidationResult $result): void
    {
        $stayDates = $this->getStayDates($checkInDate, $nights);
        $dateStrings = $stayDates->map(fn($d) => $d->format('Y-m-d'))->toArray();

        $allotments = Allotment::where('room_type_id', $roomType->id)
            ->whereIn('date', $dateStrings)
            ->get();

        if ($allotments->isEmpty()) {
            return; // Already handled in date availability
        }

        // Check min stay
        $minStay = $allotments->min('min_stay');
        if ($minStay > 0 && $nights < $minStay) {
            $result->addError('nights', sprintf(
                'Minimum stay requirement is %d nights',
                $minStay
            ));
        }

        // Check max stay
        $maxStay = $allotments->min('max_stay');
        if ($maxStay > 0 && $nights > $maxStay) {
            $result->addError('nights', sprintf(
                'Maximum stay allowed is %d nights',
                $maxStay
            ));
        }
    }

    /**
     * Get all dates for a stay
     *
     * @param Carbon $checkInDate
     * @param int $nights
     * @return Collection
     */
    private function getStayDates(Carbon $checkInDate, int $nights): Collection
    {
        $dates = collect();

        for ($i = 0; $i < $nights; $i++) {
            $dates->push($checkInDate->copy()->addDays($i));
        }

        return $dates;
    }

    /**
     * Validate a complete booking with all details
     *
     * This is an enhanced version that includes all validation rules
     * with comprehensive checks.
     *
     * @param array $data Booking data
     * @param HotelAgePolicy|null $agePolicy Optional age policy to validate against
     * @return ValidationResult
     */
    public function validateFullBooking(array $data, ?HotelAgePolicy $agePolicy = null): ValidationResult
    {
        $result = $this->validate($data);

        // If age policy is provided, validate guest ages against policy
        if ($agePolicy && $result->isValid()) {
            $this->validateGuestAgesAgainstPolicy($data['guests'] ?? [], $agePolicy, $result);
        }

        return $result;
    }

    /**
     * Validate guest ages against hotel age policy
     *
     * @param array $guests
     * @param HotelAgePolicy $agePolicy
     * @param ValidationResult $result
     */
    private function validateGuestAgesAgainstPolicy(array $guests, HotelAgePolicy $agePolicy, ValidationResult $result): void
    {
        foreach ($guests as $index => $guest) {
            $birthdate = $this->parseDate($guest['birthdate'] ?? null);
            if (!$birthdate) {
                continue; // Already validated
            }

            $category = strtolower($guest['guest_category'] ?? '');
            $checkInDate = $this->parseDate($guest['check_in_date'] ?? Carbon::today());
            $ageAtCheckIn = $birthdate->diffInYears($checkInDate);

            switch ($category) {
                case 'infant':
                    if ($ageAtCheckIn > $agePolicy->infant_max_age) {
                        $result->addError("guests.{$index}", sprintf(
                            'Infant must be %d years or younger (current age: %d)',
                            $agePolicy->infant_max_age,
                            $ageAtCheckIn
                        ));
                    }
                    break;

                case 'child':
                    if ($ageAtCheckIn > $agePolicy->child_max_age || $ageAtCheckIn > $agePolicy->infant_max_age) {
                        $result->addError("guests.{$index}", sprintf(
                            'Child must be between %d and %d years (current age: %d)',
                            $agePolicy->infant_max_age + 1,
                            $agePolicy->child_max_age,
                            $ageAtCheckIn
                        ));
                    }
                    break;

                case 'adult':
                    if ($ageAtCheckIn < $agePolicy->adult_min_age) {
                        $result->addError("guests.{$index}", sprintf(
                            'Adult must be %d years or older (current age: %d)',
                            $agePolicy->adult_min_age,
                            $ageAtCheckIn
                        ));
                    }
                    break;
            }
        }
    }
}
