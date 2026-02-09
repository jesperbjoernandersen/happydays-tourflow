<?php

namespace App\Enums;

/**
 * Pricing Model Enum
 *
 * Defines the two pricing models used in the booking system.
 */
enum PricingModel: string
{
    /**
     * OCCUPANCY_BASED model:
     * - base_price: per room/night
     * - price_per_adult: additional per adult
     * - price_per_child: additional per child
     * - price_per_infant: additional per infant (usually 0)
     * - price_per_extra_bed: additional per extra bed
     */
    case OCCUPANCY_BASED = 'occupancy_based';

    /**
     * UNIT_INCLUDED_OCCUPANCY model:
     * - base_price: fixed price for up to X guests (included_occupancy)
     * - price_per_extra_person: additional per person above included_occupancy
     */
    case UNIT_INCLUDED_OCCUPANCY = 'unit_included_occupancy';

    /**
     * Check if this is an occupancy-based pricing model.
     */
    public function isOccupancyBased(): bool
    {
        return $this === self::OCCUPANCY_BASED;
    }

    /**
     * Check if this is a unit included occupancy pricing model.
     */
    public function isUnitIncludedOccupancy(): bool
    {
        return $this === self::UNIT_INCLUDED_OCCUPANCY;
    }
}
