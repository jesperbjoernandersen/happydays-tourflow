<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\BookingResource;
use App\Http\Resources\BookingCollection;
use App\Http\Requests\Api\CreateBookingRequest;
use App\Http\Requests\Api\UpdateBookingStatusRequest;
use App\Http\Requests\Api\CancelBookingRequest;
use App\Models\Booking;
use App\Models\StayType;
use App\Models\RoomType;
use App\Models\RatePlan;
use App\Models\RateRule;
use App\Models\BookingGuest;
use App\Services\PricingService;
use App\Services\BookingValidationService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

class BookingController extends Controller
{
    private PricingService $pricingService;
    private BookingValidationService $bookingValidationService;

    public function __construct(
        PricingService $pricingService,
        BookingValidationService $bookingValidationService
    ) {
        $this->pricingService = $pricingService;
        $this->bookingValidationService = $bookingValidationService;
    }

    /**
     * Create a new booking.
     *
     * POST /api/bookings
     *
     * @param CreateBookingRequest $request
     * @return JsonResponse
     */
    public function store(CreateBookingRequest $request): JsonResponse
    {
        $validated = $request->validated();

        try {
            $stayTypeId = $validated['stay_type_id'];
            $checkInDate = Carbon::parse($validated['check_in_date']);
            $nights = $validated['nights'] ?? 1;
            $occupancy = $validated['occupancy'];
            $adults = $occupancy['adults'] ?? 2;
            $children = $occupancy['children'] ?? [];
            $guestInfo = $validated['guest_info'];
            $roomTypeId = $validated['room_type_id'] ?? null;
            $ratePlanId = $validated['rate_plan_id'] ?? null;
            $extraBeds = $validated['extra_beds'] ?? 0;
            $notes = $validated['notes'] ?? null;

            // Load stay type with relationships
            $stayType = StayType::with(['hotel', 'rateRules.ratePlan'])
                ->where('id', $stayTypeId)
                ->where('is_active', true)
                ->firstOrFail();

            // Validate minimum stay
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

            // Build guest array for validation and pricing
            $guests = [];
            
            // Add adults
            for ($i = 0; $i < $adults; $i++) {
                $adultName = 'Adult '.($i + 1);
                $adultBirthdate = null;
                if (isset($guestInfo['adults'][$i])) {
                    $adultName = $guestInfo['adults'][$i]['name'] ?? $adultName;
                    $adultBirthdate = $guestInfo['adults'][$i]['birthdate'] ?? null;
                }
                $guests[] = [
                    'guest_category' => 'adult',
                    'name' => $adultName,
                    'birthdate' => $adultBirthdate,
                ];
            }

            // Add children with birthdates
            foreach ($children as $index => $child) {
                $childName = 'Child '.($index + 1);
                if (isset($child['name'])) {
                    $childName = $child['name'];
                }
                $guests[] = [
                    'guest_category' => 'child',
                    'name' => $childName,
                    'birthdate' => $child['birthdate'],
                ];
            }

            // Validate booking
            $validationResult = $this->bookingValidationService->validate([
                'stay_type' => $stayType,
                'room_type' => $roomType,
                'check_in_date' => $checkInDate,
                'nights' => $nights,
                'guests' => $guests,
                'extra_beds' => $extraBeds,
            ]);

            if (!$validationResult->isValid()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Booking validation failed',
                    'errors' => $validationResult->getErrors(),
                ], 422);
            }

            // Calculate price
            $guestsForPricing = array_map(function ($guest) {
                return ['guest_category' => $guest['guest_category']];
            }, $guests);

            $pricingResult = $this->pricingService->calculatePrice(
                $ratePlan,
                $roomType,
                $stayType,
                $checkInDate,
                $nights,
                $guestsForPricing,
                $extraBeds
            );

            if (isset($pricingResult['error'])) {
                return response()->json([
                    'success' => false,
                    'message' => $pricingResult['error'],
                ], 400);
            }

            // Generate booking reference
            $bookingReference = $this->generateBookingReference();

            // Get applicable rate rule for snapshot
            $rateRule = $this->getApplicableRateRule($ratePlan, $stayType, $roomType, $checkInDate);

            // Get age policy snapshot
            $agePolicy = $stayType->hotel->agePolicies->first();
            $agePolicySnapshot = $agePolicy ? $agePolicy->toArray() : null;

            // Create booking in transaction
            $booking = DB::transaction(function () use (
                $bookingReference,
                $stayType,
                $roomType,
                $checkInDate,
                $nights,
                $pricingResult,
                $guests,
                $notes,
                $rateRule,
                $agePolicySnapshot
            ) {
                $booking = Booking::create([
                    'booking_reference' => $bookingReference,
                    'stay_type_id' => $stayType->id,
                    'room_type_id' => $roomType->id,
                    'hotel_id' => $stayType->hotel->id,
                    'check_in_date' => $checkInDate,
                    'check_out_date' => $checkInDate->copy()->addDays($nights),
                    'total_price' => $pricingResult['total_price'],
                    'currency' => $pricingResult['currency'] ?? 'EUR',
                    'status' => 'pending',
                    'hotel_age_policy_snapshot' => $agePolicySnapshot,
                    'rate_rule_snapshot' => $rateRule ? [
                        'id' => $rateRule->id,
                        'rate_plan_id' => $rateRule->rate_plan_id,
                        'pricing_model' => $rateRule->ratePlan->pricing_model,
                        'base_price' => $rateRule->base_price,
                        'price_per_adult' => $rateRule->price_per_adult,
                        'price_per_child' => $rateRule->price_per_child,
                        'price_per_infant' => $rateRule->price_per_infant,
                        'included_occupancy' => $rateRule->included_occupancy,
                    ] : null,
                    'price_breakdown_json' => $pricingResult['breakdown'] ?? [],
                    'guest_count' => count($guests),
                    'notes' => $notes,
                ]);

                // Create guest records
                foreach ($guests as $guestData) {
                    BookingGuest::create([
                        'booking_id' => $booking->id,
                        'name' => $guestData['name'],
                        'birthdate' => $guestData['birthdate'],
                        'guest_category' => $guestData['guest_category'],
                    ]);
                }

                return $booking->load(['stayType', 'roomType', 'hotel', 'guests']);
            });

            return response()->json([
                'success' => true,
                'message' => 'Booking created successfully',
                'booking' => new BookingResource($booking),
                'price_breakdown' => [
                    'currency' => $pricingResult['currency'] ?? 'EUR',
                    'total_price' => $pricingResult['total_price'],
                    'per_night_average' => round($pricingResult['total_price'] / $nights, 2),
                    'breakdown' => $pricingResult['breakdown'] ?? [],
                ],
            ], 201);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Stay type not found',
                'stay_type_id' => $stayTypeId ?? null,
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while creating the booking',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get a specific booking.
     *
     * GET /api/bookings/{id}
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        try {
            $booking = Booking::with(['stayType', 'roomType', 'hotel', 'guests'])
                ->where('id', $id)
                ->firstOrFail();

            return response()->json([
                'success' => true,
                'booking' => new BookingResource($booking),
            ]);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Booking not found',
                'booking_id' => $id,
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while retrieving the booking',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * List all bookings with pagination and filters.
     *
     * GET /api/bookings
     *
     * Query params:
     * - page: Page number (default: 1)
     * - per_page: Items per page (default: 15)
     * - status: Filter by status (pending, confirmed, cancelled, checked_in, completed)
     * - check_in_from: Filter by check-in date from (YYYY-MM-DD)
     * - check_in_to: Filter by check-in date to (YYYY-MM-DD)
     * - customer_email: Filter by customer email
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $page = (int) $request->query('page', 1);
            $perPage = (int) $request->query('per_page', 15);
            $status = $request->query('status');
            $checkInFrom = $request->query('check_in_from');
            $checkInTo = $request->query('check_in_to');
            $customerEmail = $request->query('customer_email');

            // Validate per_page
            $perPage = min(max($perPage, 1), 100);

            $query = Booking::with(['stayType', 'roomType', 'hotel', 'guests']);

            // Apply filters
            if ($status) {
                $query->where('status', $status);
            }

            if ($checkInFrom) {
                $query->whereDate('check_in_date', '>=', $checkInFrom);
            }

            if ($checkInTo) {
                $query->whereDate('check_in_date', '<=', $checkInTo);
            }

            if ($customerEmail) {
                $query->whereHas('guests', function ($q) use ($customerEmail) {
                    $q->where('email', 'like', '%'.$customerEmail.'%');
                });
            }

            // Order by created_at descending
            $query->orderBy('created_at', 'desc');

            $bookings = $query->paginate($perPage, ['*'], 'page', $page);

            return response()->json([
                'success' => true,
                'data' => new BookingCollection($bookings),
                'meta' => [
                    'current_page' => $bookings->currentPage(),
                    'last_page' => $bookings->lastPage(),
                    'per_page' => $bookings->perPage(),
                    'total' => $bookings->total(),
                ],
                'filters' => [
                    'status' => $status,
                    'check_in_from' => $checkInFrom,
                    'check_in_to' => $checkInTo,
                    'customer_email' => $customerEmail,
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while retrieving bookings',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Cancel a booking.
     *
     * PUT /api/bookings/{id}/cancel
     *
     * @param CancelBookingRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function cancel(CancelBookingRequest $request, int $id): JsonResponse
    {
        $validated = $request->validated();
        $reason = $validated['reason'] ?? null;
        $notifyCustomer = $validated['notify_customer'] ?? true;

        try {
            $booking = Booking::with(['stayType', 'roomType', 'hotel', 'guests'])
                ->where('id', $id)
                ->firstOrFail();

            // Check if booking can be cancelled
            if (in_array($booking->status, ['cancelled', 'completed'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot cancel a booking with status: '.$booking->status,
                ], 400);
            }

            // Calculate refund amount (simple 100% refund for pending, 50% for confirmed)
            $refundAmount = 0;
            $refundPercentage = 100;

            if ($booking->status === 'confirmed') {
                $refundPercentage = 50;
                $refundAmount = $booking->total_price * ($refundPercentage / 100);
            } elseif ($booking->status === 'pending') {
                $refundAmount = $booking->total_price;
            }

            // Update booking status
            $cancellationReason = $reason ?? 'Not provided';
            $booking->update([
                'status' => 'cancelled',
                'notes' => $booking->notes."\nCancellation reason: ".$cancellationReason,
            ]);

            // Reload booking with relationships
            $booking->load(['stayType', 'roomType', 'hotel', 'guests']);

            // TODO: Send notification email if $notifyCustomer is true

            return response()->json([
                'success' => true,
                'message' => 'Booking cancelled successfully',
                'booking' => new BookingResource($booking),
                'cancellation' => [
                    'cancelled_at' => now()->toIso8601String(),
                    'reason' => $reason,
                    'refund' => [
                        'amount' => round($refundAmount, 2),
                        'percentage' => $refundPercentage,
                        'currency' => $booking->currency,
                    ],
                    'customer_notified' => $notifyCustomer,
                ],
            ]);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Booking not found',
                'booking_id' => $id,
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while cancelling the booking',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update booking status (admin only).
     *
     * PUT /api/bookings/{id}/status
     *
     * @param UpdateBookingStatusRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function updateStatus(UpdateBookingStatusRequest $request, int $id): JsonResponse
    {
        $validated = $request->validated();
        $newStatus = $validated['status'];

        try {
            $booking = Booking::with(['stayType', 'roomType', 'hotel', 'guests'])
                ->where('id', $id)
                ->firstOrFail();

            // Validate status transition
            $validTransitions = [
                'pending' => ['confirmed', 'cancelled'],
                'confirmed' => ['checked_in', 'cancelled'],
                'checked_in' => ['completed'],
                'cancelled' => [],
                'completed' => [],
            ];

            $currentTransitions = $validTransitions[$booking->status] ?? [];
            if (!in_array($newStatus, $currentTransitions)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid status transition from \''.$booking->status.'\' to \''.$newStatus.'\'',
                    'current_status' => $booking->status,
                    'requested_status' => $newStatus,
                    'valid_transitions' => $currentTransitions,
                ], 400);
            }

            $booking->update(['status' => $newStatus]);
            $booking->load(['stayType', 'roomType', 'hotel', 'guests']);

            return response()->json([
                'success' => true,
                'message' => 'Booking status updated successfully',
                'booking' => new BookingResource($booking),
                'status_change' => [
                    'from' => $booking->getOriginal('status'),
                    'to' => $newStatus,
                    'updated_at' => now()->toIso8601String(),
                ],
            ]);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Booking not found',
                'booking_id' => $id,
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while updating the booking status',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Generate a unique booking reference.
     *
     * @return string
     */
    private function generateBookingReference(): string
    {
        do {
            $reference = 'BK'.strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 8));
        } while (Booking::where('booking_reference', $reference)->exists());

        return $reference;
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

        $rule = null;

        if ($stayType) {
            $rule = $query->where('stay_type_id', $stayType->id)
                ->where('room_type_id', $roomType->id)
                ->first();
        }

        if (!$rule) {
            $rule = $query->where('room_type_id', $roomType->id)
                ->whereNull('stay_type_id')
                ->first();
        }

        if (!$rule) {
            $stayTypeId = $stayType ? $stayType->id : null;
            $rule = $query->where('stay_type_id', $stayTypeId)
                ->whereNull('room_type_id')
                ->first();
        }

        if (!$rule) {
            $rule = $query->whereNull('stay_type_id')
                ->whereNull('room_type_id')
                ->first();
        }

        return $rule;
    }
}
