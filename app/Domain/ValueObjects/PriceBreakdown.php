<?php

namespace App\Domain\ValueObjects;

use InvalidArgumentException;

/**
 * PriceBreakdown Value Object
 *
 * Represents the detailed breakdown of a booking price.
 * Immutable - all operations return new instances.
 */
class PriceBreakdown
{
    /**
     * @param Money $basePrice Base price for the room/night
     * @param Money $adultSupplement Additional charge per adult
     * @param Money $childSupplement Additional charge per child
     * @param Money $infantSupplement Additional charge per infant (usually 0)
     * @param Money $extraBedSupplement Additional charge per extra bed
     * @param Money $singleUseSupplement Single room supplement
     * @param Money $extraOccupancyCharge Charge for extra guests beyond included occupancy
     * @param Money $totalPrice Total calculated price
     * @param string $currency Currency code (EUR, DKK, USD, etc.)
     */
    public function __construct(
        Money $basePrice,
        Money $adultSupplement,
        Money $childSupplement,
        Money $infantSupplement,
        Money $extraBedSupplement,
        Money $singleUseSupplement,
        Money $extraOccupancyCharge,
        Money $totalPrice,
        string $currency = 'EUR'
    ) {
        $this->basePrice = $basePrice;
        $this->adultSupplement = $adultSupplement;
        $this->childSupplement = $childSupplement;
        $this->infantSupplement = $infantSupplement;
        $this->extraBedSupplement = $extraBedSupplement;
        $this->singleUseSupplement = $singleUseSupplement;
        $this->extraOccupancyCharge = $extraOccupancyCharge;
        $this->totalPrice = $totalPrice;
        $this->currency = $currency;
    }

    /**
     * Get the base price.
     */
    public function getBasePrice(): Money
    {
        return $this->basePrice;
    }

    /**
     * Get the adult supplement.
     */
    public function getAdultSupplement(): Money
    {
        return $this->adultSupplement;
    }

    /**
     * Get the child supplement.
     */
    public function getChildSupplement(): Money
    {
        return $this->childSupplement;
    }

    /**
     * Get the infant supplement.
     */
    public function getInfantSupplement(): Money
    {
        return $this->infantSupplement;
    }

    /**
     * Get the extra bed supplement.
     */
    public function getExtraBedSupplement(): Money
    {
        return $this->extraBedSupplement;
    }

    /**
     * Get the single use supplement.
     */
    public function getSingleUseSupplement(): Money
    {
        return $this->singleUseSupplement;
    }

    /**
     * Get the extra occupancy charge.
     */
    public function getExtraOccupancyCharge(): Money
    {
        return $this->extraOccupancyCharge;
    }

    /**
     * Get the total price.
     */
    public function getTotalPrice(): Money
    {
        return $this->totalPrice;
    }

    /**
     * Get the currency.
     */
    public function getCurrency(): string
    {
        return $this->currency;
    }

    /**
     * Convert to array representation for storage.
     *
     * @return array
     */
    public function breakdownJson(): array
    {
        return [
            'base_price' => $this->basePrice->getAmount(),
            'adult_supplement' => $this->adultSupplement->getAmount(),
            'child_supplement' => $this->childSupplement->getAmount(),
            'infant_supplement' => $this->infantSupplement->getAmount(),
            'extra_bed_supplement' => $this->extraBedSupplement->getAmount(),
            'single_use_supplement' => $this->singleUseSupplement->getAmount(),
            'extra_occupancy_charge' => $this->extraOccupancyCharge->getAmount(),
            'total_price' => $this->totalPrice->getAmount(),
            'currency' => $this->currency,
        ];
    }

    /**
     * Create a zero price breakdown.
     *
     * @param string $currency
     * @return PriceBreakdown
     */
    public static function zero(string $currency = 'EUR'): PriceBreakdown
    {
        $zero = Money::zero($currency);

        return new self(
            $zero,
            $zero,
            $zero,
            $zero,
            $zero,
            $zero,
            $zero,
            $zero,
            $currency
        );
    }

    /**
     * Convert to string.
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->totalPrice->format();
    }
}
