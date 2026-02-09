<?php

namespace App\Services;

use App\Domain\ValueObjects\GuestCategory;
use App\Models\HotelAgePolicy;
use Carbon\Carbon;
use InvalidArgumentException;

/**
 * AgeClassificationService
 *
 * Classifies guests by age category based on hotel age policies.
 * Age is calculated AT the check-in date, NOT the booking date.
 */
class AgeClassificationService
{
    /**
     * Classify a guest by age category.
     *
     * @param Carbon|Carbon\Carbon|string $birthdate The guest's birthdate
     * @param Carbon|Carbon\Carbon|string $checkinDate The check-in date (age calculated at this date)
     * @param HotelAgePolicy $policy The hotel age policy
     * @return GuestCategory The guest category
     * @throws InvalidArgumentException If birthdate is null, invalid, or in the future
     */
    public function classify($birthdate, $checkinDate, HotelAgePolicy $policy): GuestCategory
    {
        // Parse dates
        $birthdate = $this->parseDate($birthdate, 'birthdate');
        $checkinDate = $this->parseDate($checkinDate, 'checkin_date');

        // Validate birthdate
        $this->validateBirthdate($birthdate, $checkinDate);

        // Calculate age at check-in date
        $age = $this->calculateAge($birthdate, $checkinDate);

        return $this->determineCategory($age, $policy);
    }

    /**
     * Parse a date value to Carbon instance.
     *
     * @param mixed $date The date value
     * @param string $fieldName The field name for error messages
     * @return Carbon
     * @throws InvalidArgumentException If date is invalid
     */
    private function parseDate($date, string $fieldName): Carbon
    {
        if ($date === null) {
            throw new InvalidArgumentException($fieldName . ' cannot be null');
        }

        if ($date instanceof Carbon) {
            return $date->copy();
        }

        if ($date instanceof \Carbon\CarbonImmutable) {
            return Carbon::instance($date);
        }

        try {
            return Carbon::parse($date);
        } catch (\Exception $e) {
            throw new InvalidArgumentException('Invalid ' . $fieldName . ': ' . $date);
        }
    }

    /**
     * Validate the birthdate.
     *
     * @param Carbon $birthdate The birthdate
     * @param Carbon $checkinDate The check-in date
     * @throws InvalidArgumentException If birthdate is invalid
     */
    private function validateBirthdate(Carbon $birthdate, Carbon $checkinDate): void
    {
        // Birthdate cannot be in the future relative to check-in date
        if ($birthdate->gt($checkinDate)) {
            throw new InvalidArgumentException('Birthdate cannot be in the future relative to check-in date');
        }

        // Sanity check: birthdate cannot be too far in the past (e.g., more than 150 years ago)
        $maxAge = 150;
        $minBirthdate = $checkinDate->copy()->subYears($maxAge);
        if ($birthdate->lt($minBirthdate)) {
            throw new InvalidArgumentException('Birthdate is too far in the past (超过' . $maxAge . ' years ago)');
        }
    }

    /**
     * Calculate age at a specific date.
     *
     * @param Carbon $birthdate The birthdate
     * @param Carbon $atDate The date to calculate age at
     * @return int The age in years
     */
    private function calculateAge(Carbon $birthdate, Carbon $atDate): int
    {
        $age = $atDate->year - $birthdate->year;

        // Adjust if birthday hasn't occurred yet this year
        if ($atDate->month < $birthdate->month || 
            ($atDate->month === $birthdate->month && $atDate->day < $birthdate->day)) {
            $age--;
        }

        return max(0, $age);
    }

    /**
     * Determine the guest category based on age and policy.
     *
     * @param int $age The guest's age
     * @param HotelAgePolicy $policy The hotel age policy
     * @return GuestCategory
     */
    private function determineCategory(int $age, HotelAgePolicy $policy): GuestCategory
    {
        $infantMaxAge = $policy->getAttribute('infant_max_age');
        $childMaxAge = $policy->getAttribute('child_max_age');
        $adultMinAge = $policy->getAttribute('adult_min_age');

        // Infant: 0 <= age < infant_max_age
        // If infant_max_age is not set, no one is classified as infant
        if ($infantMaxAge !== null && $age < $infantMaxAge) {
            return GuestCategory::infant();
        }

        // Child: infant_max_age <= age < child_max_age
        // If child_max_age is set, check if age falls in child range
        if ($childMaxAge !== null) {
            if ($age < $childMaxAge) {
                return GuestCategory::child();
            }
        } elseif ($adultMinAge !== null) {
            // If no child_max_age but adult_min_age is set,
            // use adult_min_age as threshold
            if ($age < $adultMinAge) {
                return GuestCategory::child();
            }
        }

        // Adult: age >= child_max_age OR (if child_max_age not set, age >= adult_min_age)
        return GuestCategory::adult();
    }
}
