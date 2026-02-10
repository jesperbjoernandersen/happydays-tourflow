<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AvailabilityResource;
use App\Http\Resources\AvailabilityCalendarResource;
use App\Http\Resources\BulkAvailabilityResource;
use App\Http\Requests\Api\CheckAvailabilityRequest;
use App\Http\Requests\Api\GetAvailabilityCalendarRequest;
use App\Http\Requests\Api\CheckBulkAvailabilityRequest;
use App\Models\StayType;
use App\Models\RoomType;
use App\Models\RatePlan;
use App\Models\RateRule;
use App\Models\Allotment;
use App\Models\Booking;
use App\Services\AvailabilityService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class AvailabilityController extends Controller
{
    private AvailabilityService $availabilityService;

    public function __construct(AvailabilityService $availabilityService)
    {
        $this->availabilityService = $availabilityService;
    }

    /**
     * Check availability for a stay type.
     *
     * GET /api/availability/{stay_type_id}
     *
     * Query params:
     * - check_in_date: YYYY-MM-DD (required)
     * - nights: number of nights (default: 1)
     * - occupancy: array with adults, children, infants (default: 2 adults)
     *
     * @param Request $request
     * @param int $stayTypeId
     * @return JsonResponse
     */
    public function index(Request $request, int $stayTypeId): JsonResponse
    {
        try {
            // Validate required parameters
            $checkInDate = $request->query('check_in_date');
            if (!$checkInDate) {
                return response()->json([
                    'success' => false,
                    'message' => 'check_in_date query parameter is required',
                ], 400);
            }

            // Parse and validate date
            $checkInDate = Carbon::parse($checkInDate);
            if ($checkInDate->isPast()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Check-in date cannot be in the past',
                ], 400);
            }

            $nights = (int) $request->query('nights', 1);
            if ($nights < 1 || $nights > 365) {
                return response()->json([
                    'success' => false,
                    'message' => 'Nights must be between 1 and 365',
                ], 400);
            }

            // Parse occupancy
            $occupancy = $request->query('occupancy', []);
            $adults = $occupancy['adults'] ?? 2;
            $children = $occupancy['children'] ?? 0;
            $infants = $occupancy['infants'] ?? 0;

            if ($adults < 1) {
                return response()->json([
                    'success' => false,
                    'message' => 'At least one adult is required',
                ], 400);
            }

            $roomTypeId = $request->query('room_type_id');
            $ratePlanId = $request->query('rate_plan_id');
            $extraBeds = (int) $request->query('extra_beds', 0);

            // Load stay type with relationships
            $stayType = StayType::with(['hotel', 'rateRules.ratePlan'])
                ->where('id', $stayTypeId)
                ->where('is_active', true)
                ->firstOrFail();

            // Validate stay type nights requirement
            if ($nights < $stayType->nights) {
                return response()->json([
                    'success' => false,
                    'message' => "Minimum stay requirement is {$stayType->nights} nights for this package",
                    'minimum_nights' => $stayType->nights,
                ], 400);
            }

            // Determine room type and rate plan
            $roomType = null;
            $ratePlan = null;

            if ($roomTypeId) {
                $roomType = RoomType::findOrFail($roomTypeId);
            } else {
                $roomType = $stayType->hotel->roomTypes()->where('is_active', true)->first();
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
                $ratePlan = $stayType->hotel->ratePlans()->where('is_active', true)->first();
            }

            if (!$ratePlan) {
                return response()->json([
                    'success' => false,
                    'message' => 'No rate plans available for this stay type',
                ], 400);
            }

            // Check availability
            $availability = $this->availabilityService->checkAvailability(
                $stayType,
                $roomType,
                $ratePlan,
                $checkInDate,
                $nights,
                $adults,
                $children,
                $infants,
                $extraBeds
            );

            if ($availability['error']) {
                return response()->json([
                    'success' => false,
                    'message' => $availability['message'],
                    'stay_type_id' => $stayTypeId,
                    'check_in_date' => $checkInDate->format('Y-m-d'),
                    'nights' => $nights,
                ], 400);
            }

            $totalPrice = $availability['total_price'] ?? 0;
            $availableDates = $availability['available_dates'] ?? [];

            // Check if minimum stay requirement is met
            $minimumStayMet = $nights >= $stayType->nights;
            $maximumStayMet = true; // Could add max stay validation if needed

            return response()->json([
                'success' => true,
                'is_available' => $availability['is_available'],
                'stay_type_id' => $stayTypeId,
                'stay_type_name' => $stayType->name,
                'check_in_date' => $checkInDate->format('Y-m-d'),
                'check_out_date' => $checkInDate->copy()->addDays($nights)->format('Y-m-d'),
                'nights' => $nights,
                'currency' => $availability['currency'] ?? 'EUR',
                'total_price' => $totalPrice,
                'per_night_average' => $nights > 0 ? round($totalPrice / $nights, 2) : 0,
                'available_dates' => $availableDates,
                'minimum_stay_met' => $minimumStayMet,
                'maximum_stay_met' => $maximumStayMet,
                'occupancy' => [
                    'adults' => $adults,
                    'children' => $children,
                    'infants' => $infants,
                    'total_guests' => $adults + $children + $infants,
                ],
                'extra_beds' => $extraBeds,
                'rate_rule' => $availability['rate_rule'] ? [
                    'id' => $availability['rate_rule']['id'],
                    'rate_plan_id' => $availability['rate_rule']['rate_plan_id'],
                    'rate_plan_name' => $availability['rate_rule']['rate_plan_name'],
                    'pricing_model' => $availability['rate_rule']['pricing_model'],
                    'included_occupancy' => $availability['rate_rule']['included_occupancy'],
                ] : null,
                'restrictions' => $availability['restrictions'] ?? null,
                'stay_type' => [
                    'id' => $stayType->id,
                    'name' => $stayType->name,
                    'code' => $stayType->code,
                    'nights' => $stayType->nights,
                    'included_board_type' => $stayType->included_board_type,
                    'minimum_nights' => $stayType->nights,
                ],
                'room_type' => $roomType ? [
                    'id' => $roomType->id,
                    'name' => $roomType->name,
                    'code' => $roomType->code,
                    'base_occupancy' => $roomType->base_occupancy,
                    'max_occupancy' => $roomType->max_occupancy,
                ] : null,
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
                'message' => 'An error occurred while checking availability',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get monthly availability calendar.
     *
     * GET /api/availability/{stay_type_id}/calendar/{year}/{month}
     *
     * @param Request $request
     * @param int $stayTypeId
     * @param int $year
     * @param int $month
     * @return JsonResponse
     */
    public function calendar(Request $request, int $stayTypeId, int $year, int $month): JsonResponse
    {
        try {
            // Validate year and month
            if ($year < 2020 || $year > 2100) {
                return response()->json([
                    'success' => false,
                    'message' => 'Year must be between 2020 and 2100',
                ], 400);
            }

            if ($month < 1 || $month > 12) {
                return response()->json([
                    'success' => false,
                    'message' => 'Month must be between 1 and 12',
                ], 400);
            }

            $startDate = Carbon::create($year, $month, 1)->startOfMonth();
            $endDate = $startDate->copy()->endOfMonth();
            $daysInMonth = $endDate->day;

            // Parse occupancy
            $occupancy = $request->query('occupancy', []);
            $adults = $occupancy['adults'] ?? 2;
            $children = $occupancy['children'] ?? 0;
            $infants = $occupancy['infants'] ?? 0;

            $roomTypeId = $request->query('room_type_id');
            $ratePlanId = $request->query('rate_plan_id');

            // Load stay type with relationships
            $stayType = StayType::with(['hotel', 'rateRules.ratePlan'])
                ->where('id', $stayTypeId)
                ->where('is_active', true)
                ->firstOrFail();

            // Get room type and rate plan
            $roomType = null;
            $ratePlan = null;

            if ($roomTypeId) {
                $roomType = RoomType::findOrFail($roomTypeId);
            } else {
                $roomType = $stayType->hotel->roomTypes()->where('is_active', true)->first();
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
                $ratePlan = $stayType->hotel->ratePlans()->where('is_active', true)->first();
            }

            if (!$ratePlan) {
                return response()->json([
                    'success' => false,
                    'message' => 'No rate plans available for this stay type',
                ], 400);
            }

            // Generate calendar data
            $calendar = $this->availabilityService->getCalendarAvailability(
                $stayType,
                $roomType,
                $ratePlan,
                $startDate,
                $endDate,
                $adults,
                $children,
                $infants
            );

            $totalDays = $daysInMonth;
            $availableDays = collect($calendar['days'])->where('is_available', true)->count();
            $availableDates = collect($calendar['days'])->where('is_available', true)->pluck('date')->toArray();

            $minPrice = collect($calendar['days'])->whereNotNull('price')->min('price');
            $maxPrice = collect($calendar['days'])->whereNotNull('price')->max('price');
            $prices = collect($calendar['days'])->whereNotNull('price')->pluck('price')->toArray();
            $avgPrice = !empty($prices) ? round(array_sum($prices) / count($prices), 2) : null;

            return response()->json([
                'success' => true,
                'stay_type_id' => $stayTypeId,
                'stay_type_name' => $stayType->name,
                'year' => $year,
                'month' => $month,
                'month_name' => $startDate->format('F'),
                'currency' => 'EUR',
                'summary' => [
                    'total_days' => $totalDays,
                    'available_days' => $availableDays,
                    'unavailable_days' => $totalDays - $availableDays,
                    'min_price' => $minPrice,
                    'max_price' => $maxPrice,
                    'avg_price' => $avgPrice,
                    'currency' => 'EUR',
                ],
                'available_dates' => $availableDates,
                'days' => $calendar['days'],
                'stay_type' => [
                    'id' => $stayType->id,
                    'name' => $stayType->name,
                    'code' => $stayType->code,
                    'nights' => $stayType->nights,
                    'included_board_type' => $stayType->included_board_type,
                    'minimum_nights' => $stayType->nights,
                ],
                'occupancy' => [
                    'adults' => $adults,
                    'children' => $children,
                    'infants' => $infants,
                    'total_guests' => $adults + $children + $infants,
                ],
                'room_type' => $roomType ? [
                    'id' => $roomType->id,
                    'name' => $roomType->name,
                    'code' => $roomType->code,
                    'base_occupancy' => $roomType->base_occupancy,
                    'max_occupancy' => $roomType->max_occupancy,
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
                'message' => 'An error occurred while generating the availability calendar',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Bulk availability check.
     *
     * POST /api/availability/check
     *
     * @param CheckBulkAvailabilityRequest $request
     * @return JsonResponse
     */
    public function bulkCheck(CheckBulkAvailabilityRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $results = [];

        foreach ($validated['requests'] as $requestItem) {
            $stayTypeId = $requestItem['stay_type_id'];
            $checkInDate = Carbon::parse($requestItem['check_in_date']);
            $nights = $requestItem['nights'] ?? 1;

            $occupancy = $requestItem['occupancy'] ?? ['adults' => 2];
            $adults = $occupancy['adults'] ?? 2;
            $children = $occupancy['children'] ?? 0;
            $infants = $occupancy['infants'] ?? 0;

            $roomTypeId = $requestItem['room_type_id'] ?? null;
            $ratePlanId = $requestItem['rate_plan_id'] ?? null;

            try {
                // Load stay type with relationships
                $stayType = StayType::with(['hotel', 'rateRules.ratePlan'])
                    ->where('id', $stayTypeId)
                    ->where('is_active', true)
                    ->firstOrFail();

                // Get room type
                $roomType = null;
                if ($roomTypeId) {
                    $roomType = RoomType::findOrFail($roomTypeId);
                } else {
                    $roomType = $stayType->hotel->roomTypes()->where('is_active', true)->first();
                }

                // Get rate plan
                $ratePlan = null;
                if ($ratePlanId) {
                    $ratePlan = RatePlan::findOrFail($ratePlanId);
                } else {
                    $ratePlan = $stayType->hotel->ratePlans()->where('is_active', true)->first();
                }

                if (!$roomType || !$ratePlan) {
                    $results[] = [
                        'stay_type_id' => $stayTypeId,
                        'check_in_date' => $checkInDate->format('Y-m-d'),
                        'nights' => $nights,
                        'success' => false,
                        'is_available' => false,
                        'message' => !$roomType ? 'Room type not found' : 'Rate plan not found',
                    ];
                    continue;
                }

                // Check availability
                $availability = $this->availabilityService->checkAvailability(
                    $stayType,
                    $roomType,
                    $ratePlan,
                    $checkInDate,
                    $nights,
                    $adults,
                    $children,
                    $infants,
                    0
                );

                $results[] = [
                    'stay_type_id' => $stayTypeId,
                    'stay_type_name' => $stayType->name,
                    'check_in_date' => $checkInDate->format('Y-m-d'),
                    'check_out_date' => $checkInDate->copy()->addDays($nights)->format('Y-m-d'),
                    'nights' => $nights,
                    'success' => !$availability['error'],
                    'is_available' => $availability['is_available'],
                    'total_price' => $availability['total_price'] ?? null,
                    'currency' => $availability['currency'] ?? 'EUR',
                    'message' => $availability['error'] ?? ($availability['is_available'] ? 'Available' : 'Not available'),
                    'restrictions' => $availability['restrictions'] ?? null,
                ];

            } catch (ModelNotFoundException $e) {
                $results[] = [
                    'stay_type_id' => $stayTypeId,
                    'check_in_date' => $checkInDate->format('Y-m-d'),
                    'nights' => $nights,
                    'success' => false,
                    'is_available' => false,
                    'message' => 'Stay type not found',
                ];
            } catch (\Exception $e) {
                $results[] = [
                    'stay_type_id' => $stayTypeId,
                    'check_in_date' => $checkInDate->format('Y-m-d'),
                    'nights' => $nights,
                    'success' => false,
                    'is_available' => false,
                    'message' => 'An error occurred: ' . $e->getMessage(),
                ];
            }
        }

        // Count results
        $totalRequests = count($results);
        $successfulRequests = collect($results)->where('success', true)->count();
        $availableCount = collect($results)->where('is_available', true)->count();

        return response()->json([
            'success' => true,
            'total_requests' => $totalRequests,
            'successful_requests' => $successfulRequests,
            'failed_requests' => $totalRequests - $successfulRequests,
            'available_count' => $availableCount,
            'unavailable_count' => $totalRequests - $availableCount,
            'results' => $results,
        ]);
    }
}
