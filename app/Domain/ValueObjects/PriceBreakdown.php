<?php

namespace App\Domain\ValueObjects;

use InvalidArgumentException;

/**
 * PriceBreakdown Value Object
 *
 * Represents a detailed price breakdown for a booking.
 * Immutable - all operations return new instances.
 * 
 * Pricing Models:
 * - OCCUPANCY_BASED: Per-person pricing where price varies by guest count
 * - UNIT_INCLUDED_OCCUPANCY: Fixed price up to X guests, then per-person for additional
 */
class PriceBreakdown
{
    /**
     * Pricing model constants
     */
    public const MODEL_OCCUPANCY_BASED = 'OCCUPANCY_BASED';
    public const MODEL_UNIT_INCLUDED_OCCUPANCY = 'UNIT_INCLUDED_OCCUPANCY';

    /**
     * @param float $basePrice Base room price for the stay
     * @param float $adultSupplement Supplement per adult beyond base occupancy
     * @param float $childSupplement Supplement per child beyond base occupancy
     * @param float $infantSupplement Supplement per infant (usually free or discounted)
     * @param float $extraBedSupplement Supplement per extra bed
     * @param float|null $singleUseSupplement Single use supplement (null if not applicable)
     * @param float $extraOccupancyCharge Additional charge per person beyond base
     * @param string $currency Currency code (e.g., EUR, USD)
     * @param string $pricingModel Pricing model constant
     * @param int $nights Number of nights for the stay
     * @param int $baseOccupancy Base occupancy included in base price
     * @param int $adults Number of adults
     * @param int $children Number of children
     * @param int $infants Number of infants
     * @param int $extraBeds Number of extra beds
     */
    public function __construct(
        float $basePrice = 0,
        float $adultSupplement = 0,
        float $childSupplement = 0,
        float $infantSupplement = 0,
        float $extraBedSupplement = 0,
        ?float $singleUseSupplement = null,
        float $extraOccupancyCharge = 0,
        string $currency = 'EUR',
        string $pricingModel = self::MODEL_UNIT_INCLUDED_OCCUPANCY,
        int $nights = 1,
        int $baseOccupancy = 2,
        int $adults = 1,
        int $children = 0,
        int $infants = 0,
        int $extraBeds = 0
    ) {
        $this->validateInputs($basePrice, $adultSupplement, $childSupplement, $infantSupplement, $extraBedSupplement);
        
        $this->basePrice = $basePrice;
        $this->adultSupplement = $adultSupplement;
        $this->childSupplement = $childSupplement;
        $this->infantSupplement = $infantSupplement;
        $this->extraBedSupplement = $extraBedSupplement;
        $this->singleUseSupplement = $singleUseSupplement;
        $this->extraOccupancyCharge = $extraOccupancyCharge;
        $this->currency = $currency;
        $this->pricingModel = $pricingModel;
        $this->nights = $nights;
        $this->baseOccupancy = $baseOccupancy;
        $this->adults = $adults;
        $this->children = $children;
        $this->infants = $infants;
        $this->extraBeds = $extraBeds;
    }

    /**
     * Get base price for the stay (before supplements)
     */
    public function getBasePrice(): float
    {
        return $this->basePrice;
    }

    /**
     * Get per-night base price
     */
    public function getPerNightBasePrice(): float
    {
        return $this->nights > 0 ? $this->basePrice / $this->nights : $this->basePrice;
    }

    /**
     * Get adult supplement amount (total for stay)
     */
    public function getAdultSupplement(): float
    {
        $guestsBeyondBase = max(0, $this->adults + $this->children - $this->baseOccupancy);
        $adultsBeyondBase = min($guestsBeyondBase, $this->adults);
        
        if ($this->pricingModel === self::MODEL_OCCUPANCY_BASED) {
            // All adults pay supplement in occupancy-based model
            return $this->adultSupplement * $this->adults * $this->nights;
        }
        
        return $adultsBeyondBase * $this->adultSupplement * $this->nights;
    }

    /**
     * Get child supplement amount (total for stay)
     */
    public function getChildSupplement(): float
    {
        if ($this->children === 0) {
            return 0;
        }
        
        $guestsBeyondBase = max(0, $this->adults + $this->children - $this->baseOccupancy);
        
        if ($this->pricingModel === self::MODEL_OCCUPANCY_BASED) {
            return $this->childSupplement * $this->children * $this->nights;
        }
        
        $childrenBeyondBase = min($guestsBeyondBase, $this->children);
        return $childrenBeyondBase * $this->childSupplement * $this->nights;
    }

    /**
     * Get infant supplement amount (usually free)
     */
    public function getInfantSupplement(): float
    {
        return $this->infantSupplement * $this->infants * $this->nights;
    }

    /**
     * Get extra bed supplement amount
     */
    public function getExtraBedSupplement(): float
    {
        return $this->extraBedSupplement * $this->extraBeds * $this->nights;
    }

    /**
     * Get single use supplement if applicable
     */
    public function getSingleUseSupplement(): float
    {
        if ($this->singleUseSupplement === null) {
            return 0;
        }
        
        // Apply when 1 adult in a room designed for 2+
        if ($this->adults === 1 && $this->baseOccupancy >= 2) {
            return $this->singleUseSupplement * $this->nights;
        }
        
        return 0;
    }

    /**
     * Get extra occupancy charge
     */
    public function getExtraOccupancyCharge(): float
    {
        $guestsBeyondBase = max(0, $this->adults + $this->children - $this->baseOccupancy);
        return $guestsBeyondBase * $this->extraOccupancyCharge * $this->nights;
    }

    /**
     * Calculate total price
     */
    public function getTotalPrice(): float
    {
        return $this->basePrice
            + $this->getAdultSupplement()
            + $this->getChildSupplement()
            + $this->getInfantSupplement()
            + $this->getExtraBedSupplement()
            + $this->getSingleUseSupplement()
            + $this->getExtraOccupancyCharge();
    }

    /**
     * Calculate per-night total price
     */
    public function getPerNightTotalPrice(): float
    {
        return $this->nights > 0 ? $this->getTotalPrice() / $this->nights : $this->getTotalPrice();
    }

    /**
     * Get the currency code
     */
    public function getCurrency(): string
    {
        return $this->currency;
    }

    /**
     * Get the pricing model
     */
    public function getPricingModel(): string
    {
        return $this->pricingModel;
    }

    /**
     * Check if pricing is occupancy-based
     */
    public function isOccupancyBased(): bool
    {
        return $this->pricingModel === self::MODEL_OCCUPANCY_BASED;
    }

    /**
     * Check if pricing includes fixed occupancy
     */
    public function isUnitIncludedOccupancy(): bool
    {
        return $this->pricingModel === self::MODEL_UNIT_INCLUDED_OCCUPANCY;
    }

    /**
     * Get number of nights
     */
    public function getNights(): int
    {
        return $this->nights;
    }

    /**
     * Get base occupancy
     */
    public function getBaseOccupancy(): int
    {
        return $this->baseOccupancy;
    }

    /**
     * Get guest counts
     */
    public function getAdults(): int
    {
        return $this->adults;
    }

    public function getChildren(): int
    {
        return $this->children;
    }

    public function getInfants(): int
    {
        return $this->infants;
    }

    public function getExtraBeds(): int
    {
        return $this->extraBeds;
    }

    /**
     * Get total guests
     */
    public function getTotalGuests(): int
    {
        return $this->adults + $this->children + $this->infants;
    }

    /**
     * Get paying guests
     */
    public function getTotalPayingGuests(): int
    {
        return $this->adults + $this->children;
    }

    /**
     * Get formatted base price
     */
    public function formatBasePrice(): string
    {
        return $this->formatAmount($this->basePrice);
    }

    /**
     * Get formatted per-night base price
     */
    public function formatPerNightBasePrice(): string
    {
        return $this->formatAmount($this->getPerNightBasePrice());
    }

    /**
     * Get formatted total price
     */
    public function formatTotalPrice(): string
    {
        return $this->formatAmount($this->getTotalPrice());
    }

    /**
     * Get formatted per-night total price
     */
    public function formatPerNightTotalPrice(): string
    {
        return $this->formatAmount($this->getPerNightTotalPrice());
    }

    /**
     * Format amount with currency symbol
     */
    public function formatAmount(float $amount): string
    {
        return number_format($amount, 2, ',', '.') . ' ' . $this->currency;
    }

    /**
     * Get breakdown as array
     */
    public function toArray(): array
    {
        return [
            'base_price' => $this->basePrice,
            'per_night_base_price' => $this->getPerNightBasePrice(),
            'adult_supplement' => $this->getAdultSupplement(),
            'child_supplement' => $this->getChildSupplement(),
            'infant_supplement' => $this->getInfantSupplement(),
            'extra_bed_supplement' => $this->getExtraBedSupplement(),
            'single_use_supplement' => $this->getSingleUseSupplement(),
            'extra_occupancy_charge' => $this->getExtraOccupancyCharge(),
            'total_price' => $this->getTotalPrice(),
            'per_night_total_price' => $this->getPerNightTotalPrice(),
            'currency' => $this->currency,
            'pricing_model' => $this->pricingModel,
            'nights' => $this->nights,
            'base_occupancy' => $this->baseOccupancy,
            'adults' => $this->adults,
            'children' => $this->children,
            'infants' => $this->infants,
            'extra_beds' => $this->extraBeds,
            'total_guests' => $this->getTotalGuests(),
        ];
    }

    /**
     * Validate input values
     */
    private function validateInputs(
        float $basePrice,
        float $adultSupplement,
        float $childSupplement,
        float $infantSupplement,
        float $extraBedSupplement
    ): void {
        if ($basePrice < 0) {
            throw new InvalidArgumentException('Base price cannot be negative');
        }

        if ($adultSupplement < 0) {
            throw new InvalidArgumentException('Adult supplement cannot be negative');
        }

        if ($childSupplement < 0) {
            throw new InvalidArgumentException('Child supplement cannot be negative');
        }

        if ($infantSupplement < 0) {
            throw new InvalidArgumentException('Infant supplement cannot be negative');
        }

        if ($extraBedSupplement < 0) {
            throw new InvalidArgumentException('Extra bed supplement cannot be negative');
        }
    }

    /**
     * Create from array
     */
    public static function fromArray(array $data): self
    {
        return new self(
            basePrice: $data['base_price'] ?? 0,
            adultSupplement: $data['adult_supplement'] ?? 0,
            childSupplement: $data['child_supplement'] ?? 0,
            infantSupplement: $data['infant_supplement'] ?? 0,
            extraBedSupplement: $data['extra_bed_supplement'] ?? 0,
            singleUseSupplement: $data['single_use_supplement'] ?? null,
            extraOccupancyCharge: $data['extra_occupancy_charge'] ?? 0,
            currency: $data['currency'] ?? 'EUR',
            pricingModel: $data['pricing_model'] ?? self::MODEL_UNIT_INCLUDED_OCCUPANCY,
            nights: $data['nights'] ?? 1,
            baseOccupancy: $data['base_occupancy'] ?? 2,
            adults: $data['adults'] ?? 1,
            children: $data['children'] ?? 0,
            infants: $data['infants'] ?? 0,
            extraBeds: $data['extra_beds'] ?? 0
        );
    }

    /**
     * String representation
     */
    public function __toString(): string
    {
        return sprintf(
            '%s (%s)',
            $this->formatTotalPrice(),
            $this->pricingModel
        );
    }
}
