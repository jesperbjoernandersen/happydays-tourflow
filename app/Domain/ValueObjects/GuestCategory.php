<?php

namespace App\Domain\ValueObjects;

use App\Models\HotelAgePolicy;
use InvalidArgumentException;

/**
 * GuestCategory Value Object
 *
 * Represents the category of a guest (INFANT, CHILD, ADULT)
 * based on the hotel's age policy.
 */
class GuestCategory
{
    /**
     * Guest category constants
     */
    public const string INFANT = 'INFANT';
    public const string CHILD = 'CHILD';
    public const string ADULT = 'ADULT';

    /**
     * @param string $category The category constant
     */
    public function __construct(
        string $category
    ) {
        $this->validateCategory($category);
        $this->category = $category;
    }

    /**
     * Get the category
     *
     * @return string
     */
    public function getCategory(): string
    {
        return $this->category;
    }

    /**
     * Create a GuestCategory based on age and hotel age policy
     *
     * @param int $age The guest's age
     * @param HotelAgePolicy $policy The hotel's age policy
     * @return GuestCategory
     * @throws InvalidArgumentException If age is negative or policy is invalid
     */
    public static function fromAge(int $age, HotelAgePolicy $policy): GuestCategory
    {
        if ($age < 0) {
            throw new InvalidArgumentException('Age cannot be negative');
        }

        $infantMaxAge = $policy->getAttribute('infant_max_age');
        $childMaxAge = $policy->getAttribute('child_max_age');
        $adultMinAge = $policy->getAttribute('adult_min_age');

        // If infant max age is defined and age is within infant range
        if ($infantMaxAge !== null && $age <= $infantMaxAge) {
            return new self(self::INFANT);
        }

        // If child max age is defined and age is within child range
        if ($childMaxAge !== null && $age <= $childMaxAge) {
            return new self(self::CHILD);
        }

        // Otherwise, adult (default)
        return new self(self::ADULT);
    }

    /**
     * Create an INFANT category
     *
     * @return GuestCategory
     */
    public static function infant(): GuestCategory
    {
        return new self(self::INFANT);
    }

    /**
     * Create a CHILD category
     *
     * @return GuestCategory
     */
    public static function child(): GuestCategory
    {
        return new self(self::CHILD);
    }

    /**
     * Create an ADULT category
     *
     * @return GuestCategory
     */
    public static function adult(): GuestCategory
    {
        return new self(self::ADULT);
    }

    /**
     * Check if this is an infant
     *
     * @return bool
     */
    public function isInfant(): bool
    {
        return $this->category === self::INFANT;
    }

    /**
     * Check if this is a child
     *
     * @return bool
     */
    public function isChild(): bool
    {
        return $this->category === self::CHILD;
    }

    /**
     * Check if this is an adult
     *
     * @return bool
     */
    public function isAdult(): bool
    {
        return $this->category === self::ADULT;
    }

    /**
     * Validate the category
     *
     * @param string $category
     * @throws InvalidArgumentException
     */
    private function validateCategory(string $category): void
    {
        $validCategories = [self::INFANT, self::CHILD, self::ADULT];

        if (!in_array($category, $validCategories, true)) {
            throw new InvalidArgumentException(
                'Invalid guest category: ' . $category . '. Valid categories: ' . implode(', ', $validCategories)
            );
        }
    }

    /**
     * Convert to string
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->category;
    }
}
