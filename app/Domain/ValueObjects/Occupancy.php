<?php

namespace App\Domain\ValueObjects;

use InvalidArgumentException;

/**
 * Occupancy Value Object
 *
 * Represents the guest occupancy configuration for a booking.
 * Immutable - all operations return new instances.
 */
class Occupancy
{
    /**
     * @param int $adults Number of adult guests (must be >= 1)
     * @param int $children Number of child guests
     * @param int $infants Number of infant guests
     * @param int $extraBeds Number of extra beds requested
     * @param int $baseOccupancy Base occupancy included in room rate
     * @param int $maxOccupancy Maximum occupancy allowed
     * @param int $maxExtraBeds Maximum extra beds allowed
     */
    public function __construct(
        int $adults = 1,
        int $children = 0,
        int $infants = 0,
        int $extraBeds = 0,
        int $baseOccupancy = 2,
        int $maxOccupancy = 4,
        int $maxExtraBeds = 2
    ) {
        $this->validateOccupancy($adults, $children, $infants, $extraBeds, $maxOccupancy, $maxExtraBeds);

        $this->adults = $adults;
        $this->children = $children;
        $this->infants = $infants;
        $this->extraBeds = $extraBeds;
        $this->baseOccupancy = $baseOccupancy;
        $this->maxOccupancy = $maxOccupancy;
        $this->maxExtraBeds = $maxExtraBeds;
    }

    /**
     * Get number of adult guests
     */
    public function getAdults(): int
    {
        return $this->adults;
    }

    /**
     * Get number of child guests
     */
    public function getChildren(): int
    {
        return $this->children;
    }

    /**
     * Get number of infant guests
     */
    public function getInfants(): int
    {
        return $this->infants;
    }

    /**
     * Get number of extra beds
     */
    public function getExtraBeds(): int
    {
        return $this->extraBeds;
    }

    /**
     * Get total paying guests (adults + children)
     */
    public function getTotalPayingGuests(): int
    {
        return $this->adults + $this->children;
    }

    /**
     * Get total guests including infants
     */
    public function getTotalGuests(): int
    {
        return $this->adults + $this->children + $this->infants;
    }

    /**
     * Get base occupancy included in rate
     */
    public function getBaseOccupancy(): int
    {
        return $this->baseOccupancy;
    }

    /**
     * Get maximum occupancy allowed
     */
    public function getMaxOccupancy(): int
    {
        return $this->maxOccupancy;
    }

    /**
     * Get maximum extra beds allowed
     */
    public function getMaxExtraBeds(): int
    {
        return $this->maxExtraBeds;
    }

    /**
     * Check if occupancy exceeds base (triggers supplement charges)
     */
    public function exceedsBaseOccupancy(): bool
    {
        return $this->getTotalPayingGuests() > $this->baseOccupancy;
    }

    /**
     * Check if guests exceed maximum occupancy
     */
    public function exceedsMaxOccupancy(): bool
    {
        return $this->getTotalPayingGuests() > $this->maxOccupancy;
    }

    /**
     * Check if extra beds are requested
     */
    public function hasExtraBeds(): bool
    {
        return $this->extraBeds > 0;
    }

    /**
     * Check if single use supplement applies (1 adult in double room)
     */
    public function appliesSingleUseSupplement(): bool
    {
        return $this->adults === 1 && $this->baseOccupancy >= 2;
    }

    /**
     * Get the number of guests beyond base occupancy
     */
    public function getGuestsBeyondBase(): int
    {
        return max(0, $this->getTotalPayingGuests() - $this->baseOccupancy);
    }

    /**
     * Create from an array
     */
    public static function fromArray(array $data): self
    {
        return new self(
            adults: $data['adults'] ?? 1,
            children: $data['children'] ?? 0,
            infants: $data['infants'] ?? 0,
            extraBeds: $data['extra_beds'] ?? 0,
            baseOccupancy: $data['base_occupancy'] ?? 2,
            maxOccupancy: $data['max_occupancy'] ?? 4,
            maxExtraBeds: $data['max_extra_beds'] ?? 2
        );
    }

    /**
     * Convert to array
     */
    public function toArray(): array
    {
        return [
            'adults' => $this->adults,
            'children' => $this->children,
            'infants' => $this->infants,
            'extra_beds' => $this->extraBeds,
            'total_paying_guests' => $this->getTotalPayingGuests(),
            'total_guests' => $this->getTotalGuests(),
            'base_occupancy' => $this->baseOccupancy,
            'max_occupancy' => $this->maxOccupancy,
            'max_extra_beds' => $this->maxExtraBeds,
        ];
    }

    /**
     * Validate occupancy values
     */
    private function validateOccupancy(
        int $adults,
        int $children,
        int $infants,
        int $extraBeds,
        int $maxOccupancy,
        int $maxExtraBeds
    ): void {
        if ($adults < 1) {
            throw new InvalidArgumentException('At least 1 adult is required');
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

        if ($maxOccupancy < 1) {
            throw new InvalidArgumentException('Maximum occupancy must be at least 1');
        }

        if ($maxExtraBeds < 0) {
            throw new InvalidArgumentException('Maximum extra beds cannot be negative');
        }

        if ($adults + $children > $maxOccupancy) {
            throw new InvalidArgumentException(
                sprintf(
                    'Total guests (%d) cannot exceed maximum occupancy (%d)',
                    $adults + $children,
                    $maxOccupancy
                )
            );
        }

        if ($extraBeds > $maxExtraBeds) {
            throw new InvalidArgumentException(
                sprintf(
                    'Extra beds (%d) cannot exceed maximum (%d)',
                    $extraBeds,
                    $maxExtraBeds
                )
            );
        }
    }

    /**
     * String representation
     */
    public function __toString(): string
    {
        $guests = "{$this->adults} adult" . ($this->adults !== 1 ? 's' : '');
        
        if ($this->children > 0) {
            $guests .= ", {$this->children} child" . ($this->children !== 1 ? 'ren' : '');
        }
        
        if ($this->infants > 0) {
            $guests .= ", {$this->infants} infant" . ($this->infants !== 1 ? 's' : '');
        }
        
        if ($this->extraBeds > 0) {
            $guests .= ", {$this->extraBeds} extra bed" . ($this->extraBeds !== 1 ? 's' : '');
        }

        return $guests;
    }
}
