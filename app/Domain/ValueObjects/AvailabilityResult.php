<?php

namespace App\Domain\ValueObjects;

use InvalidArgumentException;

/**
 * AvailabilityResult Value Object
 *
 * Represents the result of an availability check.
 * Immutable - all operations return new instances.
 */
class AvailabilityResult
{
    /**
     * @param bool $isAvailable Whether the stay is available
     * @param string|null $reason Reason for unavailability (null if available)
     * @param array $allotments Array of available allotment dates with details
     * @param int $totalAvailable Total number of available rooms across all dates
     * @param array $blockedDates Array of blocked dates with reasons
     */
    public function __construct(
        bool $isAvailable,
        ?string $reason = null,
        array $allotments = [],
        int $totalAvailable = 0,
        array $blockedDates = []
    ) {
        $this->validateReason($isAvailable, $reason);
        $this->validateTotalAvailable($totalAvailable, $isAvailable);

        $this->isAvailable = $isAvailable;
        $this->reason = $reason;
        $this->allotments = $allotments;
        $this->totalAvailable = $totalAvailable;
        $this->blockedDates = $blockedDates;
    }

    /**
     * Check if the stay is available
     *
     * @return bool
     */
    public function isAvailable(): bool
    {
        return $this->isAvailable;
    }

    /**
     * Get the reason for unavailability
     *
     * @return string|null
     */
    public function getReason(): ?string
    {
        return $this->reason;
    }

    /**
     * Get available allotment details
     *
     * @return array
     */
    public function getAllotments(): array
    {
        return $this->allotments;
    }

    /**
     * Get total available rooms
     *
     * @return int
     */
    public function getTotalAvailable(): int
    {
        return $this->totalAvailable;
    }

    /**
     * Get blocked dates with reasons
     *
     * @return array
     */
    public function getBlockedDates(): array
    {
        return $this->blockedDates;
    }

    /**
     * Check if there are any blocked dates
     *
     * @return bool
     */
    public function hasBlockedDates(): bool
    {
        return !empty($this->blockedDates);
    }

    /**
     * Get the minimum available rooms across all dates
     *
     * Useful for understanding the bottleneck in the availability
     *
     * @return int
     */
    public function getMinAvailable(): int
    {
        if (empty($this->allotments)) {
            return 0;
        }

        $minAvailable = PHP_INT_MAX;
        foreach ($this->allotments as $allotment) {
            if (isset($allotment['available']) && $allotment['available'] < $minAvailable) {
                $minAvailable = $allotment['available'];
            }
        }

        return $minAvailable === PHP_INT_MAX ? 0 : $minAvailable;
    }

    /**
     * Create an unavailable result
     *
     * @param string $reason Reason for unavailability
     * @param array $blockedDates Array of blocked dates
     * @return self
     */
    public static function unavailable(string $reason, array $blockedDates = []): self
    {
        return new self(false, $reason, [], 0, $blockedDates);
    }

    /**
     * Create an available result
     *
     * @param array $allotments Available allotment details
     * @param int $totalAvailable Total available rooms
     * @return self
     */
    public static function available(array $allotments, int $totalAvailable): self
    {
        return new self(true, null, $allotments, $totalAvailable, []);
    }

    /**
     * Convert to array
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'is_available' => $this->isAvailable,
            'reason' => $this->reason,
            'allotments' => $this->allotments,
            'total_available' => $this->totalAvailable,
            'blocked_dates' => $this->blockedDates,
        ];
    }

    /**
     * Validate that reason is set when not available
     *
     * @param bool $isAvailable
     * @param string|null $reason
     * @throws InvalidArgumentException
     */
    private function validateReason(bool $isAvailable, ?string $reason): void
    {
        if (!$isAvailable && empty($reason)) {
            throw new InvalidArgumentException('Reason must be provided when availability is false');
        }

        if ($isAvailable && !empty($reason)) {
            throw new InvalidArgumentException('Reason should be null when availability is true');
        }
    }

    /**
     * Validate total available matches the state
     *
     * @param int $totalAvailable
     * @param bool $isAvailable
     * @throws InvalidArgumentException
     */
    private function validateTotalAvailable(int $totalAvailable, bool $isAvailable): void
    {
        if ($isAvailable && $totalAvailable <= 0) {
            throw new InvalidArgumentException('Total available must be greater than 0 when availability is true');
        }

        if (!$isAvailable && $totalAvailable > 0) {
            throw new InvalidArgumentException('Total available should be 0 when availability is false');
        }
    }

    /**
     * Convert to string
     *
     * @return string
     */
    public function __toString(): string
    {
        if ($this->isAvailable) {
            return sprintf('Available (%d rooms)', $this->totalAvailable);
        }

        return sprintf('Not Available: %s', $this->reason);
    }
}
