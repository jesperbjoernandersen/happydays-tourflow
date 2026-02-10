<?php

namespace App\Services;

use App\Domain\ValueObjects\Occupancy;
use App\Domain\ValueObjects\PriceBreakdown;
use App\Models\RatePlan;
use App\Models\RoomType;
use App\Models\StayType;

/**
 * PricingService
 *
 * Calculates pricing for bookings based on room type, stay type, and occupancy.
 */
class PricingService
{
    /**
     * Calculate the full price breakdown for a booking.
     *
     * @param RoomType $roomType The room type being booked
     * @param StayType $stayType The stay type (number of nights)
     * @param Occupancy $occupancy The guest occupancy configuration
     * @return PriceBreakdown Complete price breakdown
     */
    public function calculatePrice(RoomType $roomType, StayType $stayType, Occupancy $occupancy): PriceBreakdown
    {
        $basePrice = $this->getBasePrice($roomType, $stayType);
        $nights = $stayType->nights ?? 1;
        $baseOccupancy = $roomType->base_occupancy ?? 2;
        
        // Get rate plan supplements
        $ratePlan = $this->getApplicableRatePlan($roomType, $stayType);
        
        $adultSupplement = $ratePlan?->adult_supplement ?? 0;
        $childSupplement = $ratePlan?->child_supplement ?? 0;
        $infantSupplement = $ratePlan?->infant_supplement ?? 0;
        $extraBedSupplement = $ratePlan?->extra_bed_supplement ?? 0;
        $singleUseSupplement = $ratePlan?->single_use_supplement;
        $extraOccupancyCharge = $ratePlan?->extra_occupancy_charge ?? 0;
        
        return new PriceBreakdown(
            basePrice: $basePrice,
            adultSupplement: $adultSupplement,
            childSupplement: $childSupplement,
            infantSupplement: $infantSupplement,
            extraBedSupplement: $extraBedSupplement,
            singleUseSupplement: $singleUseSupplement,
            extraOccupancyCharge: $extraOccupancyCharge,
            currency: $roomType->hotel?->currency ?? 'EUR',
            pricingModel: $this->determinePricingModel($roomType, $ratePlan),
            nights: $nights,
            baseOccupancy: $baseOccupancy,
            adults: $occupancy->getAdults(),
            children: $occupancy->getChildren(),
            infants: $occupancy->getInfants(),
            extraBeds: $occupancy->getExtraBeds()
        );
    }

    /**
     * Calculate price for simple occupancy data.
     *
     * @param float $basePrice Base room price
     * @param int $nights Number of nights
     * @param int $adults Number of adults
     * @param int $children Number of children
     * @param int $baseOccupancy Base occupancy included
     * @param string $currency Currency code
     * @param string $pricingModel Pricing model constant
     * @return PriceBreakdown
     */
    public function calculateSimplePrice(
        float $basePrice,
        int $nights,
        int $adults,
        int $children = 0,
        int $baseOccupancy = 2,
        string $currency = 'EUR',
        string $pricingModel = PriceBreakdown::MODEL_UNIT_INCLUDED_OCCUPANCY
    ): PriceBreakdown {
        $guestsBeyondBase = max(0, $adults + $children - $baseOccupancy);
        
        // For simplicity, assume per-person charge for extra guests
        $adultSupplement = 25.00; // Default supplement
        $childSupplement = 15.00; // Default supplement
        
        return new PriceBreakdown(
            basePrice: $basePrice,
            adultSupplement: $adultSupplement,
            childSupplement: $childSupplement,
            infantSupplement: 0,
            extraBedSupplement: 0,
            singleUseSupplement: null,
            extraOccupancyCharge: 0,
            currency: $currency,
            pricingModel: $pricingModel,
            nights: $nights,
            baseOccupancy: $baseOccupancy,
            adults: $adults,
            children: $children,
            infants: 0,
            extraBeds: 0
        );
    }

    /**
     * Get the base price for a room type and stay type.
     */
    protected function getBasePrice(RoomType $roomType, StayType $stayType): float
    {
        // Base price from room type
        $basePrice = $roomType->base_price ?? 0;
        
        // Apply stay type multipliers if applicable
        if (isset($stayType->price_multiplier)) {
            $basePrice *= $stayType->price_multiplier;
        }
        
        return $basePrice;
    }

    /**
     * Get the applicable rate plan for the booking.
     */
    protected function getApplicableRatePlan(RoomType $roomType, StayType $stayType): ?RatePlan
    {
        // For now, return the default rate plan
        // In a full implementation, this would check dates, availability, etc.
        return $roomType->ratePlans()->first();
    }

    /**
     * Determine the pricing model based on room type and rate plan.
     */
    protected function determinePricingModel(RoomType $roomType, ?RatePlan $ratePlan): string
    {
        if ($ratePlan && isset($ratePlan->pricing_model)) {
            return $ratePlan->pricing_model;
        }
        
        // Default based on room type configuration
        return PriceBreakdown::MODEL_UNIT_INCLUDED_OCCUPANCY;
    }

    /**
     * Calculate single night price.
     */
    public function calculatePerNightPrice(PriceBreakdown $breakdown): PriceBreakdown
    {
        return new PriceBreakdown(
            basePrice: $breakdown->getPerNightBasePrice(),
            adultSupplement: $breakdown->getAdultSupplement() / max(1, $breakdown->getNights()),
            childSupplement: $breakdown->getChildSupplement() / max(1, $breakdown->getNights()),
            infantSupplement: $breakdown->getInfantSupplement() / max(1, $breakdown->getNights()),
            extraBedSupplement: $breakdown->getExtraBedSupplement() / max(1, $breakdown->getNights()),
            singleUseSupplement: $breakdown->getSingleUseSupplement() / max(1, $breakdown->getNights()),
            extraOccupancyCharge: $breakdown->getExtraOccupancyCharge() / max(1, $breakdown->getNights()),
            currency: $breakdown->getCurrency(),
            pricingModel: $breakdown->getPricingModel(),
            nights: 1,
            baseOccupancy: $breakdown->getBaseOccupancy(),
            adults: $breakdown->getAdults(),
            children: $breakdown->getChildren(),
            infants: $breakdown->getInfants(),
            extraBeds: $breakdown->getExtraBeds()
        );
    }

    /**
     * Validate that occupancy doesn't exceed room limits.
     */
    public function validateOccupancy(RoomType $roomType, Occupancy $occupancy): array
    {
        $errors = [];
        
        $maxOccupancy = $roomType->max_occupancy ?? PHP_INT_MAX;
        $maxExtraBeds = $roomType->extra_bed_slots ?? 0;
        
        $totalPayingGuests = $occupancy->getTotalPayingGuests();
        
        if ($totalPayingGuests > $maxOccupancy) {
            $errors[] = sprintf(
                'Maximum occupancy is %d guests. You have %d paying guests.',
                $maxOccupancy,
                $totalPayingGuests
            );
        }
        
        if ($occupancy->getExtraBeds() > $maxExtraBeds) {
            $errors[] = sprintf(
                'Maximum %d extra beds available. You requested %d.',
                $maxExtraBeds,
                $occupancy->getExtraBeds()
            );
        }
        
        return $errors;
    }
}
