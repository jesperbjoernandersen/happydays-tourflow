<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PricingBreakdownResource;
use App\Http\Resources\PricingCalendarResource;
use App\Http\Resources\DailyPriceResource;
use App\Http\Requests\Api\CalculatePriceRequest;
use App\Http\Requests\Api\GetPriceBreakdownRequest;
use App\Http\Requests\Api\GetPricingCalendarRequest;
use App\Models\StayType;
use App\Models\RoomType;
use App\Models\RatePlan;
use App\Models\RateRule;
use App\Services\PricingService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class PricingController extends Controller
{
    private PricingService $pricingService;

    public function __construct(PricingService $pricingService)
    {
        $this->pricingService = $pricingService;
    }

    /**
     * Calculate total price for a stay.
     *
     * POST /api/pricing/calculate
     *
     * @param CalculatePriceRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function calculate(CalculatePriceRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $stayTypeId = $validated['stay_type_id'];
        $checkInDate = Carbon::parse($validated['check_in_date']);
        $nights = $validated['nights'] ?? 1;
        $adults = $validated['occupancy']['adults'];
        $children = $validated['occupancy']['children'] ?? 0;
        $infants = $validated['occupancy']['infants'] ?? 0;
        $roomTypeId = $validated['room_type_id'] ?? null;
        $ratePlanId = $validated['rate_plan_id'] ?? null;
        $extraBeds = $validated['extra_beds'] ?? 0;

        try {
            // Load stay type with relationships
            $stayType = StayType::with(['hotel', 'rateRules.ratePlan'])
                ->where('id', $stayTypeId)
                ->firstOrFail();

            // Build guest array for pricing service
            $guests = [];
            for ($i = 0; $i < $adults; $i++) {
                $guests[] = ['guest_category' => 'adult'];
            }
            for ($i = 0; $i < $children; $i++) {
                $guests[] = ['guest_category' => 'child'];
            }
            for ($i = 0; $i < $infants; $i++) {
                $guests[] = ['guest_category' => 'infant'];
            }

            // Determine room type and rate plan
            $roomType = null;
            $ratePlan = null;

            if ($roomTypeId) {
                $roomType = RoomType::findOrFail($roomTypeId);
            } else {
                // Get first available room type for the hotel
                $roomType = $stayType->hotel->roomTypes()->first();
            }

            if (!$roomType) {
                return response()->json([
                    'success' => false,
                    'message' => 'No room types available for this stay type',
                ], 400);
            }

            if ($ratePlanId) {
                $ratePlan = RatePlan::findOrFail($ratePlanId);
            } else {
                // Get first available rate plan
                $ratePlan = $stayType->hotel->ratePlans()->first();
            }

            if (!$ratePlan) {
                return response()->json([
                    'success' => false,
                    'message' => 'No rate plans available for this stay type',
                ], 400);
            }

            // Calculate price
            $result = $this->pricingService->calculatePrice(
                $ratePlan,
                $roomType,
                $stayType,
                $checkInDate,
                $nights,
                $guests,
                $extraBeds
            );

            if (isset($result['error'])) {
                return response()->json([
                    'success' => false,
                    'message' => $result['error'],
                    'stay_type_id' => $stayTypeId,
                    'check_in_date' => $checkInDate->format('Y-m-d'),
                    'nights' => $nights,
                ], 400);
            }

            // Get the applicable rate rule for response
            $rateRule = $this->getApplicableRateRule($ratePlan, $stayType, $roomType, $checkInDate);

            return response()->json([
                'success' => true,
                'stay_type_id' => $stayTypeId,
                'stay_type_name' => $stayType->name,
                'check_in_date' => $checkInDate->format('Y-m-d'),
                'nights' => $nights,
                'currency' => $result['currency'],
                'total_price' => $result['total_price'],
                'per_night_average' => round($result['total_price'] / $nights, 2),
                'breakdown' => $result['breakdown'],
                'rate_rule' => $rateRule ? [
                    'id' => $rateRule->id,
                    'rate_plan_id' => $rateRule->rate_plan_id,
                    'rate_plan_name' => $ratePlan->name,
                    'pricing_model' => $ratePlan->pricing_model,
                    'start_date' => $rateRule->start_date->format('Y-m-d'),
                    'end_date' => $rateRule->end_date->format('Y-m-d'),
                ] : null,
                'stay_type' => [
                    'id' => $stayType->id,
                    'name' => $stayType->name,
                    'code' => $stayType->code,
                    'nights' => $stayType->nights,
                    'included_board_type' => $stayType->included_board_type,
                ],
            ]);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Stay type not found',
                'stay_type_id' => $stayTypeId,
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while calculating the price',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get detailed price breakdown.
     *
     * GET /api/pricing/breakdown/{stayType}/{checkInDate}/{nights}
     *
     * @param Request $request
     * @param $stayType
     * @param $checkInDate
     * @param $nights
     * @return \Illuminate\Http\JsonResponse
     */
    public function breakdown(
        Request $request,
        $stayType,
        $checkInDate,
        $nights
    ): JsonResponse {
        try {
            $stayTypeId = $stayType;
            $checkInDate = Carbon::parse($checkInDate);
            $nights = (int) $nights;

            // Load stay type with relationships
            $stayType = StayType::with(['hotel', 'rateRules.ratePlan'])
                ->where('id', $stayTypeId)
                ->firstOrFail();

            // Use request occupancy or defaults
            $adults = $request->input('occupancy.adults', 2);
            $children = $request->input('occupancy.children', 0);
            $infants = $request->input('occupancy.infants', 0);
            $roomTypeId = $request->input('room_type_id');
            $ratePlanId = $request->input('rate_plan_id');
            $extraBeds = $request->input('extra_beds', 0);

            // Build guest array
            $guests = [];
            for ($i = 0; $i < $adults; $i++) {
                $guests[] = ['guest_category' => 'adult'];
            }
            for ($i = 0; $i < $children; $i++) {
                $guests[] = ['guest_category' => 'child'];
            }
            for ($i = 0; $i < $infants; $i++) {
                $guests[] = ['guest_category' => 'infant'];
            }

            // Determine room type and rate plan
            $roomType = null;
            $ratePlan = null;

            if ($roomTypeId) {
                $roomType = RoomType::findOrFail($roomTypeId);
            } else {
                $roomType = $stayType->hotel->roomTypes()->first();
            }

            if (!$roomType) {
                return response()->json([
                    'success' => false,
                    'message' => 'No room types available for this stay type',
                ], 400);
            }

            if ($ratePlanId) {
                $ratePlan = RatePlan::findOrFail($ratePlanId);
            } else {
                $ratePlan = $stayType->hotel->ratePlans()->first();
            }

            if (!$ratePlan) {
                return response()->json([
                    'success' => false,
                    'message' => 'No rate plans available for this stay type',
                ], 400);
            }

            // Calculate price
            $result = $this->pricingService->calculatePrice(
                $ratePlan,
                $roomType,
                $stayType,
                $checkInDate,
                $nights,
                $guests,
                $extraBeds
            );

            if (isset($result['error'])) {
                return response()->json([
                    'success' => false,
                    'message' => $result['error'],
                    'stay_type_id' => $stayTypeId,
                    'check_in_date' => $checkInDate->format('Y-m-d'),
                    'nights' => $nights,
                ], 400);
            }

            // Get daily breakdown for multi-night stays
            $dailyBreakdown = [];
            if ($nights > 1) {
                for ($i = 0; $i < $nights; $i++) {
                    $date = $checkInDate->copy()->addDays($i);
                    $nightResult = $this->pricingService->calculatePrice(
                        $ratePlan,
                        $roomType,
                        $stayType,
                        $date,
                        1,
                        $guests,
                        $extraBeds
                    );

                    if (!isset($nightResult['error'])) {
                        $dailyBreakdown[] = [
                            'date' => $date->format('Y-m-d'),
                            'day_name' => $date->format('l'),
                            'price' => $nightResult['total_price'],
                            'breakdown' => $nightResult['breakdown'],
                        ];
                    }
                }
            }

            $rateRule = $this->getApplicableRateRule($ratePlan, $stayType, $roomType, $checkInDate);

            return response()->json([
                'success' => true,
                'stay_type_id' => $stayTypeId,
                'stay_type_name' => $stayType->name,
                'check_in_date' => $checkInDate->format('Y-m-d'),
                'nights' => $nights,
                'currency' => $result['currency'],
                'total_price' => $result['total_price'],
                'per_night_average' => round($result['total_price'] / $nights, 2),
                'breakdown' => $result['breakdown'],
                'daily_breakdown' => $dailyBreakdown,
                'rate_rule' => $rateRule ? [
                    'id' => $rateRule->id,
                    'rate_plan_id' => $rateRule->rate_plan_id,
                    'rate_plan_name' => $ratePlan->name,
                    'pricing_model' => $ratePlan->pricing_model,
                    'start_date' => $rateRule->start_date->format('Y-m-d'),
                    'end_date' => $rateRule->end_date->format('Y-m-d'),
                ] : null,
                'stay_type' => [
                    'id' => $stayType->id,
                    'name' => $stayType->name,
                    'code' => $stayType->code,
                    'nights' => $stayType->nights,
                    'included_board_type' => $stayType->included_board_type,
                ],
            ]);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Stay type not found',
                'stay_type_id' => $stayTypeId,
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while calculating the price breakdown',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get pricing calendar for a month.
     *
     * GET /api/pricing/availability/{stayType}/{year}/{month}
     *
     * @param Request $request
     * @param $stayType
     * @param $year
     * @param $month
     * @return \Illuminate\Http\JsonResponse
     */
    public function availability(
        Request $request,
        $stayType,
        $year,
        $month
    ): JsonResponse {
        try {
            $stayTypeId = $stayType;
            $year = (int) $year;
            $month = (int) $month;
            $startDate = Carbon::create($year, $month, 1)->startOfMonth();
            $endDate = $startDate->copy()->endOfMonth();
            $daysInMonth = $endDate->day;

            // Load stay type with relationships
            $stayType = StayType::with(['hotel', 'rateRules.ratePlan'])
                ->where('id', $stayTypeId)
                ->firstOrFail();

            $roomTypeId = $request->input('room_type_id');
            $ratePlanId = $request->input('rate_plan_id');

            // Get room type and rate plan
            $roomType = null;
            $ratePlan = null;

            if ($roomTypeId) {
                $roomType = RoomType::findOrFail($roomTypeId);
            } else {
                $roomType = $stayType->hotel->roomTypes()->first();
            }

            if (!$roomType) {
                return response()->json([
                    'success' => false,
                    'message' => 'No room types available for this stay type',
                ], 400);
            }

            if ($ratePlanId) {
                $ratePlan = RatePlan::findOrFail($ratePlanId);
            } else {
                $ratePlan = $stayType->hotel->ratePlans()->first();
            }

            if (!$ratePlan) {
                return response()->json([
                    'success' => false,
                    'message' => 'No rate plans available for this stay type',
                ], 400);
            }

            // Calculate prices for each day
            $days = [];
            $prices = [];
            $availableDays = 0;

            $guests = [
                ['guest_category' => 'adult'],
                ['guest_category' => 'adult'],
            ]; // Default 2 adults

            for ($day = 1; $day <= $daysInMonth; $day++) {
                $date = Carbon::create($year, $month, $day)->startOfDay();

                $hasRate = false;
                $price = null;
                $basePrice = null;
                $rateRuleId = null;

                // Check for applicable rate rule
                $rateRule = $this->getApplicableRateRule($ratePlan, $stayType, $roomType, $date);

                if ($rateRule) {
                    $hasRate = true;
                    $rateRuleId = $rateRule->id;
                    $basePrice = (float) $rateRule->base_price;

                    // Calculate price for this day
                    $result = $this->pricingService->calculatePrice(
                        $ratePlan,
                        $roomType,
                        $stayType,
                        $date,
                        1,
                        $guests,
                        0
                    );

                    if (!isset($result['error'])) {
                        $price = $result['total_price'];
                        $prices[] = $price;

                        if ($price > 0 || $hasRate) {
                            $availableDays++;
                        }
                    }
                }

                $dayOfWeek = $date->dayOfWeek;
                $isWeekend = $dayOfWeek === 0 || $dayOfWeek === 6;

                $days[] = [
                    'date' => $date->format('Y-m-d'),
                    'day_of_week' => $dayOfWeek,
                    'day_name' => $date->format('l'),
                    'day' => $day,
                    'is_weekend' => $isWeekend,
                    'is_available' => $hasRate && ($price !== null),
                    'is_blocked' => !$hasRate,
                    'has_rate' => $hasRate,
                    'price' => $price,
                    'base_price' => $basePrice,
                    'currency' => 'EUR',
                    'rate_rule_id' => $rateRuleId,
                    'occupancy_pricing' => $rateRule ? [
                        'included_occupancy' => $rateRule->included_occupancy,
                        'price_per_adult' => $rateRule->price_per_adult,
                        'price_per_child' => $rateRule->price_per_child,
                        'price_per_infant' => $rateRule->price_per_infant,
                    ] : null,
                    'minimum_stay' => $stayType->nights,
                    'restrictions' => null,
                ];
            }

            // Calculate summary
            $minPrice = !empty($prices) ? min($prices) : null;
            $maxPrice = !empty($prices) ? max($prices) : null;
            $avgPrice = !empty($prices) ? round(array_sum($prices) / count($prices), 2) : null;

            $summary = [
                'total_days' => $daysInMonth,
                'available_days' => $availableDays,
                'min_price' => $minPrice,
                'max_price' => $maxPrice,
                'avg_price' => $avgPrice,
                'currency' => 'EUR',
            ];

            return response()->json([
                'success' => true,
                'stay_type_id' => $stayTypeId,
                'stay_type_name' => $stayType->name,
                'year' => $year,
                'month' => $month,
                'month_name' => $startDate->format('F'),
                'currency' => 'EUR',
                'summary' => $summary,
                'days' => $days,
                'stay_type' => [
                    'id' => $stayType->id,
                    'name' => $stayType->name,
                    'code' => $stayType->code,
                    'nights' => $stayType->nights,
                    'included_board_type' => $stayType->included_board_type,
                ],
                'room_type' => $roomType ? [
                    'id' => $roomType->id,
                    'name' => $roomType->name,
                    'code' => $roomType->code,
                ] : null,
                'rate_plan' => [
                    'id' => $ratePlan->id,
                    'name' => $ratePlan->name,
                    'code' => $ratePlan->code,
                    'pricing_model' => $ratePlan->pricing_model,
                ],
            ]);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Stay type not found',
                'stay_type_id' => $stayTypeId,
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while generating the pricing calendar',
                'error' => $e->getMessage(),
            ], 500);
        }
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
}
