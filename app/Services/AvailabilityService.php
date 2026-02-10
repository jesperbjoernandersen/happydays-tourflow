<?php

namespace App\Services;

use App\Models\StayType;
use App\Models\RoomType;
use App\Models\RatePlan;
use App\Models\RateRule;
use App\Models\Allotment;
use App\Models\Booking;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * AvailabilityService
 *
 * Handles availability checks for rooms and stay types.
 * Checks rate rules, allotments, bookings, and restrictions.
 */
class AvailabilityService
{
    /**
     * Check availability for a stay type.
     *
     * @param StayType $stayType
     * @param RoomType $roomType
     * @param RatePlan $ratePlan
     * @param Carbon $checkInDate
     * @param int $nights
     * @param int $adults
     * @param int $children
     * @param int $infants
     * @param int $extraBeds
     * @return array
     */
    public function checkAvailability(
        StayType $stayType,
        RoomType $roomType,
        RatePlan $ratePlan,
        Carbon $checkInDate,
        int $nights,
        int $adults,
        int $children,
        int $infants,
        int $extraBeds = 0
    ): array {
        $totalGuests = $adults + $children + $infants;

        // Check room capacity
        $maxOccupancy = $roomType->max_occupancy ?? 4;
        if ($totalGuests > $maxOccupancy) {
            return [
                'is_available' => false,
                'error' => true,
                'message' => "Room capacity exceeded. Maximum occupancy is {$maxOccupancy} guests",
                'total_price' => 0,
                'currency' => 'EUR',
                'restrictions' => [
                    'type' => 'occupancy_exceeded',
                    'message' => "Maximum occupancy is {$maxOccupancy} guests",
                    'max_occupancy' => $maxOccupancy,
                    'requested_guests' => $totalGuests,
                ],
            ];
        }

        // Check for rate rules for each night
        $availableDates = [];
        $hasRateForAllNights = true;
        $prices = [];
        $rateRule = null;

        for ($i = 0; $i < $nights; $i++) {
            $date = $checkInDate->copy()->addDays($i)->startOfDay();
            $currentRateRule = $this->getApplicableRateRule($ratePlan, $stayType, $roomType, $date);

            if (!$currentRateRule) {
                $hasRateForAllNights = false;
                break;
            }

            $rateRule = $currentRateRule;

            // Check allotment
            $allotment = $this->getAllotment($roomType, $date);
            if ($allotment && $allotment->stop_sell) {
                $hasRateForAllNights = false;
                break;
            }

            // Check if date is in the past
            if ($date->isPast()) {
                $hasRateForAllNights = false;
                break;
            }

            // Check minimum stay
            $minStay = $allotment?->min_stay ?? $stayType->nights;
            if ($nights < $minStay) {
                return [
                    'is_available' => false,
                    'error' => true,
                    'message' => "Minimum stay requirement is {$minStay} nights",
                    'total_price' => 0,
                    'currency' => 'EUR',
                    'restrictions' => [
                        'type' => 'minimum_stay',
                        'message' => "Minimum stay requirement is {$minStay} nights",
                        'minimum_nights' => $minStay,
                        'requested_nights' => $nights,
                    ],
                ];
            }

            // Check maximum stay
            $maxStay = $allotment?->max_stay ?? 30;
            if ($nights > $maxStay) {
                return [
                    'is_available' => false,
                    'error' => true,
                    'message' => "Maximum stay is {$maxStay} nights",
                    'total_price' => 0,
                    'currency' => 'EUR',
                    'restrictions' => [
                        'type' => 'maximum_stay',
                        'message' => "Maximum stay is {$maxStay} nights",
                        'maximum_nights' => $maxStay,
                        'requested_nights' => $nights,
                    ],
                ];
            }

            // Check availability (allotment quantity - allocations)
            if ($allotment) {
                $availableQuantity = $allotment->quantity - $allotment->allocated;
                if ($availableQuantity <= 0) {
                    $hasRateForAllNights = false;
                    break;
                }
            }

            $availableDates[] = $date->format('Y-m-d');
            $prices[] = $this->calculateNightlyPrice($ratePlan, $roomType, $stayType, $date, $adults, $children, $infants);
        }

        if (!$hasRateForAllNights || empty($availableDates)) {
            return [
                'is_available' => false,
                'error' => true,
                'message' => 'Not available for the selected dates',
                'total_price' => 0,
                'currency' => 'EUR',
                'available_dates' => [],
                'restrictions' => [
                    'type' => 'no_rate',
                    'message' => 'No rate available for the selected dates',
                ],
            ];
        }

        // Calculate total price
        $totalPrice = array_sum($prices);

        return [
            'is_available' => true,
            'error' => false,
            'message' => 'Available',
            'total_price' => $totalPrice,
            'currency' => 'EUR',
            'available_dates' => $availableDates,
            'prices' => $prices,
            'rate_rule' => $rateRule ? [
                'id' => $rateRule->id,
                'rate_plan_id' => $rateRule->rate_plan_id,
                'rate_plan_name' => $ratePlan->name,
                'pricing_model' => $ratePlan->pricing_model,
                'included_occupancy' => $rateRule->included_occupancy,
            ] : null,
            'restrictions' => null,
        ];
    }

    /**
     * Get calendar availability for a date range.
     *
     * @param StayType $stayType
     * @param RoomType $roomType
     * @param RatePlan $ratePlan
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @param int $adults
     * @param int $children
     * @param int $infants
     * @return array
     */
    public function getCalendarAvailability(
        StayType $stayType,
        RoomType $roomType,
        RatePlan $ratePlan,
        Carbon $startDate,
        Carbon $endDate,
        int $adults = 2,
        int $children = 0,
        int $infants = 0
    ): array {
        $days = [];
        $currentDate = $startDate->copy()->startOfDay();
        $totalGuests = $adults + $children + $infants;

        while ($currentDate->lte($endDate)) {
            $dayOfWeek = $currentDate->dayOfWeek;
            $isWeekend = $dayOfWeek === 0 || $dayOfWeek === 6;

            $rateRule = $this->getApplicableRateRule($ratePlan, $stayType, $roomType, $currentDate);
            $allotment = $this->getAllotment($roomType, $currentDate);

            $hasRate = $rateRule !== null;
            $isAvailable = true;
            $price = null;
            $basePrice = null;
            $restriction = null;

            if (!$hasRate) {
                $isAvailable = false;
                $restriction = [
                    'type' => 'no_rate',
                    'message' => 'No rate configured for this date',
                ];
            } elseif ($allotment && $allotment->stop_sell) {
                $isAvailable = false;
                $restriction = [
                    'type' => 'stop_sell',
                    'message' => 'Stop sell is active for this date',
                ];
            } elseif ($currentDate->isPast()) {
                $isAvailable = false;
                $restriction = [
                    'type' => 'past_date',
                    'message' => 'Date has passed',
                ];
            } elseif ($allotment) {
                $availableQuantity = $allotment->quantity - $allotment->allocated;
                if ($availableQuantity <= 0) {
                    $isAvailable = false;
                    $restriction = [
                        'type' => 'sold_out',
                        'message' => 'Sold out',
                    ];
                }
            }

            if ($hasRate && $isAvailable && !$currentDate->isPast()) {
                $priceResult = $this->calculateNightlyPrice(
                    $ratePlan,
                    $roomType,
                    $stayType,
                    $currentDate,
                    $adults,
                    $children,
                    $infants
                );
                $price = $priceResult['total_price'];
                $basePrice = (float) $rateRule->base_price;
            }

            $days[] = [
                'date' => $currentDate->format('Y-m-d'),
                'day_of_week' => $dayOfWeek,
                'day_name' => $currentDate->format('l'),
                'day' => $currentDate->day,
                'is_weekend' => $isWeekend,
                'is_available' => $isAvailable,
                'is_blocked' => !$hasRate || !$isAvailable,
                'has_rate' => $hasRate,
                'price' => $price,
                'base_price' => $basePrice,
                'currency' => 'EUR',
                'rate_rule_id' => $rateRule?->id,
                'allotment' => $allotment ? [
                    'quantity' => $allotment->quantity,
                    'allocated' => $allotment->allocated,
                    'available' => $allotment->quantity - $allotment->allocated,
                    'stop_sell' => $allotment->stop_sell,
                ] : null,
                'minimum_stay' => $allotment?->min_stay ?? $stayType->nights,
                'maximum_stay' => $allotment?->max_stay ?? 30,
                'cta' => $allotment?->cta ?? false,
                'ctd' => $allotment?->ctd ?? false,
                'restriction' => $restriction,
            ];

            $currentDate->addDay();
        }

        return [
            'days' => $days,
            'total_days' => count($days),
            'available_days' => collect($days)->where('is_available', true)->count(),
        ];
    }

    /**
     * Get the applicable rate rule for the given criteria.
     */
    private function getApplicableRateRule(
        RatePlan $ratePlan,
        ?StayType $stayType,
        RoomType $roomType,
        Carbon $checkInDate
    ): ?RateRule {
        $query = $ratePlan->rateRules()
            ->where('start_date', '<=', $checkInDate)
            ->where('end_date', '>=', $checkInDate);

        // Try to find rule with specific stay_type and room_type match
        $rule = null;

        if ($stayType) {
            $rule = $query->where('stay_type_id', $stayType->id)
                ->where('room_type_id', $roomType->id)
                ->first();
        }

        if (!$rule) {
            // Fall back to rule with only room_type
            $rule = $query->where('room_type_id', $roomType->id)
                ->whereNull('stay_type_id')
                ->first();
        }

        if (!$rule) {
            // Fall back to rule with only stay_type
            $rule = $query->where('stay_type_id', $stayType?->id)
                ->whereNull('room_type_id')
                ->first();
        }

        if (!$rule) {
            // Fall back to global rule
            $rule = $query->whereNull('stay_type_id')
                ->whereNull('room_type_id')
                ->first();
        }

        return $rule;
    }

    /**
     * Get allotment for a room type and date.
     */
    private function getAllotment(RoomType $roomType, Carbon $date): ?Allotment
    {
        return Allotment::where('room_type_id', $roomType->id)
            ->where('date', $date)
            ->first();
    }

    /**
     * Calculate price for a single night.
     */
    private function calculateNightlyPrice(
        RatePlan $ratePlan,
        RoomType $roomType,
        ?StayType $stayType,
        Carbon $date,
        int $adults,
        int $children,
        int $infants
    ): array {
        $rateRule = $this->getApplicableRateRule($ratePlan, $stayType, $roomType, $date);

        if (!$rateRule) {
            return ['total_price' => 0, 'breakdown' => null];
        }

        $totalGuests = $adults + $children + $infants;
        $pricingModel = $ratePlan->pricing_model;

        // Calculate base price
        $basePrice = (float) $rateRule->base_price;

        if ($pricingModel === 'unit_included_occupancy') {
            $includedOccupancy = $rateRule->included_occupancy ?? 2;

            if ($totalGuests > $includedOccupancy) {
                $extraGuests = $totalGuests - $includedOccupancy;
                $pricePerExtra = (float) $rateRule->price_per_extra_person;
                $basePrice += $pricePerExtra * $extraGuests;
            }
        } else {
            // occupancy_based
            $adultCharges = (float) $rateRule->price_per_adult * $adults;
            $childCharges = (float) $rateRule->price_per_child * $children;
            $infantCharges = (float) $rateRule->price_per_infant * $infants;
            $basePrice = $adultCharges + $childCharges + $infantCharges;
        }

        // Single use supplement
        $singleUseSupplement = 0;
        $roomBaseOccupancy = $roomType->base_occupancy ?? 2;
        if ($adults === 1 && $totalGuests === 1 && $roomBaseOccupancy >= 2) {
            $singleUseSupplement = (float) ($rateRule->single_use_supplement ?? $roomType->single_use_supplement ?? 0);
        }

        $totalPrice = $basePrice + $singleUseSupplement;
        $totalPrice = max(0, round($totalPrice, 2));

        return [
            'total_price' => $totalPrice,
            'breakdown' => [
                'base_price' => round($basePrice, 2),
                'single_use_supplement' => round($singleUseSupplement, 2),
                'pricing_model' => $pricingModel,
            ],
        ];
    }

    /**
     * Check if a specific date is available.
     */
    public function isDateAvailable(
        StayType $stayType,
        RoomType $roomType,
        RatePlan $ratePlan,
        Carbon $date
    ): bool {
        $rateRule = $this->getApplicableRateRule($ratePlan, $stayType, $roomType, $date);

        if (!$rateRule) {
            return false;
        }

        if ($date->isPast()) {
            return false;
        }

        $allotment = $this->getAllotment($roomType, $date);
        if ($allotment && $allotment->stop_sell) {
            return false;
        }

        if ($allotment) {
            $availableQuantity = $allotment->quantity - $allotment->allocated;
            if ($availableQuantity <= 0) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get available dates within a range.
     */
    public function getAvailableDates(
        StayType $stayType,
        RoomType $roomType,
        RatePlan $ratePlan,
        Carbon $startDate,
        Carbon $endDate
    ): Collection {
        $dates = [];
        $currentDate = $startDate->copy()->startOfDay();
        $end = $endDate->copy()->endOfDay();

        while ($currentDate->lte($end)) {
            if ($this->isDateAvailable($stayType, $roomType, $ratePlan, $currentDate)) {
                $dates[] = $currentDate->format('Y-m-d');
            }
            $currentDate->addDay();
        }

        return collect($dates);
    }
}
