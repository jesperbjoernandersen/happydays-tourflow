<?php

namespace App\Domain\ValueObjects;

use InvalidArgumentException;

/**
 * Occupancy Value Object
 *
 * Represents the occupancy of a room or booking.
 * Immutable - all operations return new instances.
 */
class Occupancy
{
    public int $adults;
    public int $children;
    public int $infants;
    public int $extraBeds;

    /**
     * @param int $adults Number of adults
     * @param int $children Number of children
     * @param int $infants Number of infants
     * @param int $extraBeds Number of extra beds
     */
    public function __construct(
        int $adults = 1,
        int $children = 0,
        int $infants = 0,
        int $extraBeds = 0
    ) {
        $this->validateOccupancy($adults, $children, $infants, $extraBeds);

        $this->adults = $adults;
        $this->children = $children;
        $this->infants = $infants;
        $this->extraBeds = $extraBeds;
    }

    /**
     * Get the number of adults
     *
     * @return int
     */
    public function getAdults(): int
    {
        return $this->adults;
    }

    /**
     * Get the number of children
     *
     * @return int
     */
    public function getChildren(): int
    {
        return $this->children;
    }

    /**
     * Get the number of infants
     *
     * @return int
     */
    public function getInfants(): int
    {
        return $this->infants;
    }

    /**
     * Get the number of extra beds
     *
     * @return int
     */
    public function getExtraBeds(): int
    {
        return $this->extraBeds;
    }

    /**
     * Get the total number of people (adults + children)
     * Note: Infants are typically not counted as occupying a bed
     *
     * @return int Total people
     */
    public function total(): int
    {
        return $this->adults + $this->children;
    }

    /**
     * Get the total number of people for sleeping purposes
     * This includes adults + children + extra beds
     *
     * @return int Total for sleeping
     */
    public function sleeps(): int
    {
        return $this->adults + $this->children + $this->extraBeds;
    }

    /**
     * Add an adult to the occupancy
     *
     * @return Occupancy New instance with additional adult
     */
    public function addAdult(): Occupancy
    {
        return new self(
            $this->adults + 1,
            $this->children,
            $this->infants,
            $this->extraBeds
        );
    }

    /**
     * Add a child to the occupancy
     *
     * @return Occupancy New instance with additional child
     */
    public function addChild(): Occupancy
    {
        return new self(
            $this->adults,
            $this->children + 1,
            $this->infants,
            $this->extraBeds
        );
    }

    /**
     * Add an infant to the occupancy
     *
     * @return Occupancy New instance with additional infant
     */
    public function addInfant(): Occupancy
    {
        return new self(
            $this->adults,
            $this->children,
            $this->infants + 1,
            $this->extraBeds
        );
    }

    /**
     * Add an extra bed to the occupancy
     *
     * @return Occupancy New instance with additional extra bed
     */
    public function addExtraBed(): Occupancy
    {
        return new self(
            $this->adults,
            $this->children,
            $this->infants,
            $this->extraBeds + 1
        );
    }

    /**
     * Check if the occupancy is valid
     * Business rule: at least 1 adult is required
     *
     * @return bool True if valid
     */
    public function isValid(): bool
    {
        return $this->adults >= 1;
    }

    /**
     * Validate the occupancy values
     *
     * @param int $adults
     * @param int $children
     * @param int $infants
     * @param int $extraBeds
     * @throws InvalidArgumentException
     */
    private function validateOccupancy(int $adults, int $children, int $infants, int $extraBeds): void
    {
        if ($adults < 0) {
            throw new InvalidArgumentException('Adults cannot be negative');
        }

        if ($children < 0) {
            throw new InvalidArgumentException('Children cannot be negative');
        }

        if ($infants < 0) {
            throw new InvalidArgumentException('Infants cannot be negative');
        }

        if ($extraBeds < 0) {
            throw new InvalidArgumentException('Extra beds cannot be negative');
        }

        if ($adults < 1) {
            throw new InvalidArgumentException('At least one adult is required');
        }
    }

    /**
     * Convert to string
     *
     * @return string
     */
    public function __toString(): string
    {
        $parts = [];

        if ($this->adults > 0) {
            $parts[] = $this->adults . ' adult' . ($this->adults > 1 ? 's' : '');
        }

        if ($this->children > 0) {
            $parts[] = $this->children . ' child' . ($this->children > 1 ? 'ren' : '');
        }

        if ($this->infants > 0) {
            $parts[] = $this->infants . ' infant' . ($this->infants > 1 ? 's' : '');
        }

        if ($this->extraBeds > 0) {
            $parts[] = $this->extraBeds . ' extra bed' . ($this->extraBeds > 1 ? 's' : '');
        }

        return implode(', ', $parts);
    }
}
