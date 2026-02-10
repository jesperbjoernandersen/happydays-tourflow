<?php

namespace Tests\Feature\Api\Booking;

use App\Models\Booking;
use App\Models\BookingGuest;
use App\Models\Hotel;
use App\Models\RatePlan;
use App\Models\RateRule;
use App\Models\RoomType;
use App\Models\StayType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingApiTest extends TestCase
{
    use RefreshDatabase;

    protected Hotel $hotel;
    protected RatePlan $ratePlan;
    protected RoomType $roomType;
    protected StayType $stayType;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test data
        $this->hotel = Hotel::factory()->create([
            'name' => 'Test Hotel',
            'code' => 'TH001',
            'is_active' => true,
        ]);

        $this->roomType = RoomType::factory()->create([
            'hotel_id' => $this->hotel->id,
            'name' => 'Standard Room',
            'code' => 'STD',
            'base_occupancy' => 2,
            'max_occupancy' => 4,
            'extra_bed_slots' => 1,
            'is_active' => true,
        ]);

        $this->ratePlan = RatePlan::factory()->create([
            'hotel_id' => $this->hotel->id,
            'name' => 'Standard Rate',
            'code' => 'STD',
            'pricing_model' => 'unit_included_occupancy',
            'is_active' => true,
        ]);

        $this->stayType = StayType::factory()->create([
            'hotel_id' => $this->hotel->id,
            'name' => 'Weekend Package',
            'code' => 'WEEKEND',
            'nights' => 2,
            'included_board_type' => 'Half Board',
            'is_active' => true,
        ]);

        // Create rate rule for the stay type
        RateRule::factory()->create([
            'rate_plan_id' => $this->ratePlan->id,
            'stay_type_id' => $this->stayType->id,
            'room_type_id' => $this->roomType->id,
            'base_price' => 299.99,
            'price_per_adult' => 50.00,
            'price_per_child' => 25.00,
            'price_per_infant' => 0,
            'included_occupancy' => 2,
            'start_date' => now()->subMonth()->format('Y-m-d'),
            'end_date' => now()->addYear()->format('Y-m-d'),
        ]);
    }

    /** @test */
    public function it_can_create_a_booking(): void
    {
        $payload = [
            'stay_type_id' => $this->stayType->id,
            'check_in_date' => now()->addWeek()->format('Y-m-d'),
            'nights' => 2,
            'occupancy' => [
                'adults' => 2,
                'children' => [],
            ],
            'guest_info' => [
                'adults' => [
                    ['name' => 'John Doe', 'birthdate' => '1990-01-15'],
                    ['name' => 'Jane Doe', 'birthdate' => '1992-06-20'],
                ],
                'email' => 'john@example.com',
                'phone' => '+1234567890',
            ],
        ];

        $response = $this->postJson('/api/bookings', $payload);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'message',
                'booking' => [
                    'id',
                    'booking_reference',
                    'status',
                    'check_in_date',
                    'check_out_date',
                    'nights',
                    'currency',
                    'total_price',
                    'guest_count',
                    'stay_type' => [
                        'id',
                        'name',
                        'code',
                    ],
                    'room_type' => [
                        'id',
                        'name',
                        'code',
                    ],
                    'hotel' => [
                        'id',
                        'name',
                    ],
                    'guests',
                ],
                'price_breakdown' => [
                    'currency',
                    'total_price',
                    'per_night_average',
                    'breakdown',
                ],
            ])
            ->assertJson([
                'success' => true,
                'message' => 'Booking created successfully',
            ]);

        $this->assertNotNull($response->json('booking.booking_reference'));
        $this->assertEquals('pending', $response->json('booking.status'));
        $this->assertEquals(2, $response->json('booking.guest_count'));
        $this->assertGreaterThan(0, $response->json('price_breakdown.total_price'));
    }

    /** @test */
    public function it_can_create_a_booking_with_children(): void
    {
        $payload = [
            'stay_type_id' => $this->stayType->id,
            'check_in_date' => now()->addWeek()->format('Y-m-d'),
            'nights' => 2,
            'occupancy' => [
                'adults' => 2,
                'children' => [
                    ['name' => 'Child One', 'birthdate' => '2018-05-10'],
                    ['name' => 'Child Two', 'birthdate' => '2020-03-15'],
                ],
            ],
            'guest_info' => [
                'adults' => [
                    ['name' => 'John Doe', 'birthdate' => '1990-01-15'],
                    ['name' => 'Jane Doe', 'birthdate' => '1992-06-20'],
                ],
            ],
        ];

        $response = $this->postJson('/api/bookings', $payload);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
            ]);

        $this->assertEquals(4, $response->json('booking.guest_count'));
    }

    /** @test */
    public function it_validates_required_fields(): void
    {
        $response = $this->postJson('/api/bookings', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['stay_type_id', 'check_in_date', 'occupancy', 'guest_info']);
    }

    /** @test */
    public function it_validates_check_in_date_not_in_past(): void
    {
        $payload = [
            'stay_type_id' => $this->stayType->id,
            'check_in_date' => now()->subDay()->format('Y-m-d'),
            'nights' => 2,
            'occupancy' => [
                'adults' => 2,
                'children' => [],
            ],
            'guest_info' => [
                'adults' => [
                    ['name' => 'John Doe', 'birthdate' => '1990-01-15'],
                ],
            ],
        ];

        $response = $this->postJson('/api/bookings', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['check_in_date']);
    }

    /** @test */
    public function it_validates_minimum_stay_requirement(): void
    {
        // Stay type requires 2 nights
        $payload = [
            'stay_type_id' => $this->stayType->id,
            'check_in_date' => now()->addWeek()->format('Y-m-d'),
            'nights' => 1, // Less than required 2
            'occupancy' => [
                'adults' => 2,
                'children' => [],
            ],
            'guest_info' => [
                'adults' => [
                    ['name' => 'John Doe'],
                ],
            ],
        ];

        $response = $this->postJson('/api/bookings', $payload);

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
            ])
            ->assertJsonPath('message', 'Minimum stay requirement is 2 nights for this package');
    }

    /** @test */
    public function it_returns_404_for_nonexistent_stay_type(): void
    {
        $payload = [
            'stay_type_id' => 99999,
            'check_in_date' => now()->addWeek()->format('Y-m-d'),
            'nights' => 2,
            'occupancy' => [
                'adults' => 2,
                'children' => [],
            ],
            'guest_info' => [
                'adults' => [
                    ['name' => 'John Doe'],
                ],
            ],
        ];

        $response = $this->postJson('/api/bookings', $payload);

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Stay type not found',
            ]);
    }

    /** @test */
    public function it_can_get_a_booking(): void
    {
        $booking = Booking::factory()->create([
            'stay_type_id' => $this->stayType->id,
            'room_type_id' => $this->roomType->id,
            'hotel_id' => $this->hotel->id,
            'status' => 'pending',
        ]);

        BookingGuest::factory()->create([
            'booking_id' => $booking->id,
            'name' => 'John Doe',
            'guest_category' => 'adult',
        ]);

        $response = $this->getJson("/api/bookings/{$booking->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'booking' => [
                    'id',
                    'booking_reference',
                    'status',
                    'stay_type',
                    'room_type',
                    'hotel',
                    'guests',
                ],
            ])
            ->assertJson([
                'success' => true,
                'booking.id' => $booking->id,
                'booking.booking_reference' => $booking->booking_reference,
            ]);
    }

    /** @test */
    public function it_returns_404_for_nonexistent_booking(): void
    {
        $response = $this->getJson('/api/bookings/99999');

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Booking not found',
            ]);
    }

    /** @test */
    public function it_can_list_bookings_with_pagination(): void
    {
        Booking::factory()->count(5)->create([
            'stay_type_id' => $this->stayType->id,
            'room_type_id' => $this->roomType->id,
            'hotel_id' => $this->hotel->id,
        ]);

        $response = $this->getJson('/api/bookings');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'data' => [
                        [
                            'id',
                            'booking_reference',
                            'status',
                        ],
                    ],
                ],
                'meta' => [
                    'current_page',
                    'last_page',
                    'per_page',
                    'total',
                ],
            ])
            ->assertJson([
                'success' => true,
                'meta.total' => 5,
            ]);
    }

    /** @test */
    public function it_can_filter_bookings_by_status(): void
    {
        Booking::factory()->count(3)->create([
            'stay_type_id' => $this->stayType->id,
            'room_type_id' => $this->roomType->id,
            'hotel_id' => $this->hotel->id,
            'status' => 'pending',
        ]);

        Booking::factory()->count(2)->create([
            'stay_type_id' => $this->stayType->id,
            'room_type_id' => $this->roomType->id,
            'hotel_id' => $this->hotel->id,
            'status' => 'confirmed',
        ]);

        $response = $this->getJson('/api/bookings?status=confirmed');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'meta.total' => 2,
            ]);

        foreach ($response->json('data.data') as $booking) {
            $this->assertEquals('confirmed', $booking['status']);
        }
    }

    /** @test */
    public function it_can_cancel_a_booking(): void
    {
        $booking = Booking::factory()->create([
            'stay_type_id' => $this->stayType->id,
            'room_type_id' => $this->roomType->id,
            'hotel_id' => $this->hotel->id,
            'status' => 'pending',
            'total_price' => 500.00,
            'currency' => 'EUR',
        ]);

        $response = $this->putJson("/api/bookings/{$booking->id}/cancel", [
            'reason' => 'Customer requested cancellation',
            'notify_customer' => true,
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'booking',
                'cancellation' => [
                    'cancelled_at',
                    'reason',
                    'refund' => [
                        'amount',
                        'percentage',
                        'currency',
                    ],
                    'customer_notified',
                ],
            ])
            ->assertJson([
                'success' => true,
                'message' => 'Booking cancelled successfully',
            ]);

        $this->assertEquals('cancelled', $response->json('booking.status'));
        $this->assertEquals(100, $response->json('cancellation.refund.percentage'));
    }

    /** @test */
    public function it_returns_50_percent_refund_for_confirmed_booking(): void
    {
        $booking = Booking::factory()->create([
            'stay_type_id' => $this->stayType->id,
            'room_type_id' => $this->roomType->id,
            'hotel_id' => $this->hotel->id,
            'status' => 'confirmed',
            'total_price' => 500.00,
            'currency' => 'EUR',
        ]);

        $response = $this->putJson("/api/bookings/{$booking->id}/cancel");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'booking.status' => 'cancelled',
                'cancellation.refund.percentage' => 50,
                'cancellation.refund.amount' => 250.00,
            ]);
    }

    /** @test */
    public function it_cannot_cancel_already_cancelled_booking(): void
    {
        $booking = Booking::factory()->create([
            'stay_type_id' => $this->stayType->id,
            'room_type_id' => $this->roomType->id,
            'hotel_id' => $this->hotel->id,
            'status' => 'cancelled',
        ]);

        $response = $this->putJson("/api/bookings/{$booking->id}/cancel");

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => "Cannot cancel a booking with status: cancelled",
            ]);
    }

    /** @test */
    public function it_can_update_booking_status(): void
    {
        $booking = Booking::factory()->create([
            'stay_type_id' => $this->stayType->id,
            'room_type_id' => $this->roomType->id,
            'hotel_id' => $this->hotel->id,
            'status' => 'pending',
        ]);

        $response = $this->putJson("/api/bookings/{$booking->id}/status", [
            'status' => 'confirmed',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'booking',
                'status_change' => [
                    'from',
                    'to',
                    'updated_at',
                ],
            ])
            ->assertJson([
                'success' => true,
                'message' => 'Booking status updated successfully',
                'status_change.from' => 'pending',
                'status_change.to' => 'confirmed',
            ]);
    }

    /** @test */
    public function it_validates_status_transitions(): void
    {
        // Cannot go from pending to completed directly
        $booking = Booking::factory()->create([
            'stay_type_id' => $this->stayType->id,
            'room_type_id' => $this->roomType->id,
            'hotel_id' => $this->hotel->id,
            'status' => 'pending',
        ]);

        $response = $this->putJson("/api/bookings/{$booking->id}/status", [
            'status' => 'completed',
        ]);

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
            ])
            ->assertJsonPath('message', "Invalid status transition from 'pending' to 'completed'");
    }

    /** @test */
    public function it_validates_status_values(): void
    {
        $booking = Booking::factory()->create([
            'stay_type_id' => $this->stayType->id,
            'room_type_id' => $this->roomType->id,
            'hotel_id' => $this->hotel->id,
            'status' => 'pending',
        ]);

        $response = $this->putJson("/api/bookings/{$booking->id}/status", [
            'status' => 'invalid_status',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['status']);
    }

    /** @test */
    public function it_can_filter_bookings_by_date_range(): void
    {
        // Create bookings for different dates
        Booking::factory()->create([
            'stay_type_id' => $this->stayType->id,
            'room_type_id' => $this->roomType->id,
            'hotel_id' => $this->hotel->id,
            'check_in_date' => now()->addDays(5),
        ]);

        Booking::factory()->create([
            'stay_type_id' => $this->stayType->id,
            'room_type_id' => $this->roomType->id,
            'hotel_id' => $this->hotel->id,
            'check_in_date' => now()->addDays(15),
        ]);

        Booking::factory()->create([
            'stay_type_id' => $this->stayType->id,
            'room_type_id' => $this->roomType->id,
            'hotel_id' => $this->hotel->id,
            'check_in_date' => now()->addDays(25),
        ]);

        $response = $this->getJson('/api/bookings?check_in_from=' . now()->addDays(10)->format('Y-m-d') . '&check_in_to=' . now()->addDays(20)->format('Y-m-d'));

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'meta.total' => 1,
            ]);
    }

    /** @test */
    public function it_respects_per_page_limit(): void
    {
        Booking::factory()->count(20)->create([
            'stay_type_id' => $this->stayType->id,
            'room_type_id' => $this->roomType->id,
            'hotel_id' => $this->hotel->id,
        ]);

        $response = $this->getJson('/api/bookings?per_page=5');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'meta.per_page' => 5,
                'meta.total' => 20,
            ]);

        $this->assertCount(5, $response->json('data.data'));
    }
}
