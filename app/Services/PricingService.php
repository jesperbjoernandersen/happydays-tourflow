<?php

namespace App\Services;

use App\Models\RateRule;
use App\Models\RoomType;
use App\Models\StayType;
use App\Enums\PricingModel;
use App\Domain\ValueObjects\Money;
use App\Domain\ValueObjects\Occupancy;
use App\Domain\ValueObjects\PriceBreakdown;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * PricingService
 *
 * Calculates pricing for travel package bookings.
 * Supports both OCCUPANCY_BASED and UNIT_INCLUDED_OCCUPANCY pricing models.
 */
class PricingService
{
    /**
     * Calculate the price for a booking.
     *
     * @param StayType $stayType The stay type (package duration)
     * @param RoomType $roomType The room type
     * @param Occupancy $occupancy The guest occupancy
     * @param Carbon $checkinDate The check-in date
     * @return PriceBreakdown The detailed price breakdown
     */
    public function calculatePrice(
        StayType $stayType,
        RoomType $roomType,
        Occupancy $occupancy,
        Carbon $checkinDate
    ): PriceBreakdown {
        // Find the applicable rate rule for the given parameters
        $rateRule = $this->findApplicableRateRule($stayType, $roomType, $checkinDate);

        if (!$rateRule) {
            // If no rate rule found, return zero price breakdown
            return PriceBreakdown::zero();
        }

        // Get the pricing model from the rate plan
        $pricingModel = $this->getPricingModel($rateRule);

        // Calculate the price based on the pricing model
        return $this->calculatePriceWithModel($rateRule, $occupancy, $stayType->nights, $pricingModel, $roomType);
    }

    /**
     * Find the applicable rate rule for the given parameters.
     *
     * @param StayType $stayType The stay type
     * @param RoomType $roomType The room type
     * @param Carbon $checkinDate The check-in date
     * @return RateRule|null The applicable rate rule or null if not found
     */
    public function findApplicableRateRule(
        StayType $stayType,
        RoomType $roomType,
        Carbon $checkinDate
    ): ?RateRule {
        $rateRule = RateRule::where('stay_type_id', $stayType->id)
        ->where('room_type_id', $roomType->id)
        ->where('start_date', '<=', $checkinDate)
        ->where('end_date', '>=', $checkinDate)
        ->whereHas('ratePlan', function ($query) {
            $query->where('is_active', true);
        })
        ->first();

        // Load the ratePlan relationship
        if ($rateRule) {
            $rateRule->load('ratePlan');
        }

        return $rateRule;
    }

    /**
     * Calculate the price using a specific pricing model.
     *
     * @param RateRule $rateRule The rate rule
     * @param Occupancy $occupancy The guest occupancy
     * @param int $nights Number of nights
     * @param PricingModel $pricingModel The pricing model
     * @param RoomType $roomType The room type
     * @return PriceBreakdown The calculated price breakdown
     */
    private function calculatePriceWithModel(
        RateRule $rateRule,
        Occupancy $occupancy,
        int $nights,
        PricingModel $pricingModel,
        RoomType $roomType
    ): PriceBreakdown {
        $currency = 'EUR'; // Default currency, could be made configurable

        if ($pricingModel->isOccupancyBased()) {
            return $this->calculateOccupancyBasedPrice($rateRule, $occupancy, $nights, $currency, $roomType);
        }

        return $this->calculateUnitIncludedOccupancyPrice($rateRule, $occupancy, $nights, $currency, $roomType);
    }

    /**
     * Calculate price using OCCUPANCY_BASED model.
     *
     * Formula:
     * - base_price: per room/night
     * - price_per_adult: additional per adult
     * - price_per_child: additional per child
     * - price_per_infant: additional per infant (usually 0)
     * - price_per_extra_bed: additional per extra bed
     * - single_use_supplement: if 1 person books the room
     *
     * @param RateRule $rateRule The rate rule
     * @param Occupancy $occupancy The guest occupancy
     * @param int $nights Number of nights
     * @param string $currency Currency code
     * @param RoomType $roomType The room type
     * @return PriceBreakdown
     */
    private function calculateOccupancyBasedPrice(
        RateRule $rateRule,
        Occupancy $occupancy,
        int $nights,
        string $currency,
        RoomType $roomType
    ): PriceBreakdown {
        // Calculate base price for the stay duration
        $basePrice = new Money($rateRule->base_price * $nights, $currency);

        // Calculate adult supplement
        $adultSupplement = new Money($rateRule->price_per_adult * $occupancy->getAdults() * $nights, $currency);

        // Calculate child supplement
        $childSupplement = new Money($rateRule->price_per_child * $occupancy->getChildren() * $nights, $currency);

        // Calculate infant supplement (usually 0)
        $infantSupplement = new Money($rateRule->price_per_infant * $occupancy->getInfants() * $nights, $currency);

        // Calculate extra bed supplement
        $extraBedSupplement = new Money($rateRule->price_per_extra_bed * $occupancy->getExtraBeds() * $nights, $currency);

        // Calculate single use supplement (if only 1 person and supplement applies)
        $singleUseSupplement = Money::zero($currency);
        $totalGuests = $occupancy->total();

        if ($totalGuests === 1 && $rateRule->single_use_supplement > 0) {
            $singleUseSupplement = new Money($rateRule->single_use_supplement * $nights, $currency);
        }

        // No extra occupancy charge for this model
        $extraOccupancyCharge = Money::zero($currency);

        // Calculate total
        $totalPrice = $basePrice
            ->add($adultSupplement)
            ->add($childSupplement)
            ->add($infantSupplement)
            ->add($extraBedSupplement)
            ->add($singleUseSupplement)
            ->add($extraOccupancyCharge);

        return new PriceBreakdown(
            $basePrice,
            $adultSupplement,
            $childSupplement,
            $infantSupplement,
            $extraBedSupplement,
            $singleUseSupplement,
            $extraOccupancyCharge,
            $totalPrice,
            $currency
        );
    }

    /**
     * Calculate price using UNIT_INCLUDED_OCCUPANCY model.
     *
     * Formula:
     * - base_price: fixed price for up to X guests (included_occupancy)
     * - price_per_extra_person: additional per person above included_occupancy
     *
     * @param RateRule $rateRule The rate rule
     * @param Occupancy $occupancy The guest occupancy
     * @param int $nights Number of nights
     * @param string $currency Currency code
     * @param RoomType $roomType The room type
     * @return PriceBreakdown
     */
    private function calculateUnitIncludedOccupancyPrice(
        RateRule $rateRule,
        Occupancy $occupancy,
        int $nights,
        string $currency,
        RoomType $roomType
    ): PriceBreakdown {
        // Base price for the stay duration (includes up to included_occupancy guests)
        $basePrice = new Money($rateRule->base_price * $nights, $currency);

        // No supplements for this model
        $adultSupplement = Money::zero($currency);
        $childSupplement = Money::zero($currency);
        $infantSupplement = Money::zero($currency);
        $extraBedSupplement = Money::zero($currency);
        $singleUseSupplement = Money::zero($currency);

        // Calculate extra occupancy charge (guests beyond included_occupancy)
        $totalGuests = $occupancy->total();
        $includedOccupancy = $rateRule->included_occupancy ?? 2; // Default to 2 if not set

        $extraOccupancyCharge = Money::zero($currency);
        if ($totalGuests > $includedOccupancy) {
            $extraGuests = $totalGuests - $includedOccupancy;
            $extraOccupancyCharge = new Money($rateRule->price_per_extra_person * $extraGuests * $nights, $currency);
        }

        // Calculate total
        $totalPrice = $basePrice
            ->add($adultSupplement)
            ->add($childSupplement)
            ->add($infantSupplement)
            ->add($extraBedSupplement)
            ->add($singleUseSupplement)
            ->add($extraOccupancyCharge);

        return new PriceBreakdown(
            $basePrice,
            $adultSupplement,
            $childSupplement,
            $infantSupplement,
            $extraBedSupplement,
            $singleUseSupplement,
            $extraOccupancyCharge,
            $totalPrice,
            $currency
        );
    }

    /**
     * Get the pricing model from the rate rule's rate plan.
     *
     * @param RateRule $rateRule
     * @return PricingModel
     */
    private function getPricingModel(RateRule $rateRule): PricingModel
    {
        $pricingModel = $rateRule->ratePlan->pricing_model ?? 'occupancy_based';

        return PricingModel::from($pricingModel);
    }
}
