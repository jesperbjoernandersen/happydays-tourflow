<?php

namespace Tests\Unit\Actions;

use App\Actions\CancelBooking;
use App\Models\Allotment;
use App\Models\Booking;
use App\Models\RoomType;
use Carbon\Carbon;
use App\Models\StayType;
use Database\Factories\AllotmentFactory;
use Database\Factories\BookingFactory;
use Database\Factories\RoomTypeFactory;
use Database\Factories\StayTypeFactory;
use Database\Factories\HotelFactory;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;
use RuntimeException;
use InvalidArgumentException;

/**
 * Tests for CancelBooking action.
 */
class CancelBookingTest extends TestCase
{
    use DatabaseMigrations;

    private CancelBooking $action;
    private RoomType $roomType;
    private Booking $booking;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->action = new CancelBooking();

        // Create a test hotel
        $hotel = HotelFactory::new()->create();

        // Create a test room type
        $this->roomType = RoomTypeFactory::new()->for($hotel)->create([
            'name' => 'Standard Room',
            'code' => 'STD',
        ]);

        // Create a test stay type
        $stayType = StayTypeFactory::new()->for($hotel)->create([
            'name' => 'Standard Package',
            'nights' => 3,
        ]);
    }

    /** @test */
    public function it_cancels_a_pending_booking(): void
    {
        // Arrange
        $checkInDate = Carbon::today()->addDays(10);
        $checkOutDate = $checkInDate->copy()->addDays(3);
        
        $booking = BookingFactory::new()
            ->forRoomType($this->roomType)
            ->forStayType($stayType ?? StayType::factory()->for($this->roomType->hotel)->create())
            ->withDates($checkInDate, $checkOutDate)
            ->withTotalPrice(1000.00)
            ->pending()
            ->withAllocatedAllotments()
            ->create();

        // Act
        $result = $this->action->execute(
            bookingId: $booking->id,
            reason: 'Customer request',
            notifyCustomer: true,
            cancelledByUserId: 1
        );

        // Assert
        $this->assertEquals('cancelled', $result['status']);
        $this->assertEquals($booking->id, $result['booking_id']);
        $this->assertEquals(1000.00, $result['total_price']);
        $this->assertEquals('Customer request', $result['cancellation_reason']);
        $this->assertNotNull($result['cancelled_at']);
        $this->assertEquals(1, $result['cancelled_by']);
        $this->assertTrue($result['customer_notified']);

        // Verify booking status was updated
        $booking->refresh();
        $this->assertEquals('cancelled', $booking->status);
        $this->assertStringContainsString('CANCELLED', $booking->notes);
    }

    /** @test */
    public function it_cancels_a_confirmed_booking(): void
    {
        // Arrange
        $checkInDate = Carbon::today()->addDays(14);
        $checkOutDate = $checkInDate->copy()->addDays(5);
        
        $booking = BookingFactory::new()
            ->forRoomType($this->roomType)
            ->forStayType(StayType::factory()->for($this->roomType->hotel)->create())
            ->withDates($checkInDate, $checkOutDate)
            ->withTotalPrice(1500.00)
            ->confirmed()
            ->withAllocatedAllotments()
            ->create();

        // Act
        $result = $this->action->execute(
            bookingId: $booking->id,
            reason: 'Change of plans',
            notifyCustomer: false
        );

        // Assert
        $this->assertEquals('cancelled', $result['status']);
        $this->assertFalse($result['customer_notified']);

        $booking->refresh();
        $this->assertEquals('cancelled', $booking->status);
    }

    /** @test */
    public function it_restores_allotments_for_all_dates(): void
    {
        // Arrange
        $startDate = Carbon::today()->addDays(7);
        $nights = 3;
        $checkOutDate = $startDate->copy()->addDays($nights);

        // Create allotments with allocations
        for ($i = 0; $i < $nights; $i++) {
            AllotmentFactory::new()->create([
                'room_type_id' => $this->roomType->id,
                'date' => $startDate->copy()->addDays($i),
                'quantity' => 10,
                'allocated' => 1, // This booking's allocation
                'stop_sell' => false,
            ]);
        }

        $booking = BookingFactory::new()
            ->forRoomType($this->roomType)
            ->forStayType(StayType::factory()->for($this->roomType->hotel)->create())
            ->withDates($startDate, $checkOutDate)
            ->withTotalPrice(800.00)
            ->pending()
            ->create();

        // Act
        $result = $this->action->execute(
            bookingId: $booking->id,
            reason: 'Customer request'
        );

        // Assert
        $this->assertEquals($nights, $result['allotments_restored']['total_restored']);
        $this->assertCount($nights, $result['allotments_restored']['dates']);

        // Verify each allotment was restored
        for ($i = 0; $i < $nights; $i++) {
            $allotment = Allotment::where('room_type_id', $this->roomType->id)
                ->whereDate('date', $startDate->copy()->addDays($i)->format('Y-m-d'))
                ->first();

            $this->assertEquals(0, $allotment->allocated); // Should be restored to 0
            $this->assertEquals(10, $allotment->quantity);
            $this->assertEquals(10, $allotment->quantity - $allotment->allocated);
        }
    }

    /** @test */
    public function it_calculates_full_refund_for_early_cancellation(): void
    {
        // Arrange - More than 14 days before check-in = 100% refund
        $checkInDate = Carbon::today()->addDays(20);
        $checkOutDate = $checkInDate->copy()->addDays(2);
        
        // Create allotments for all dates
        AllotmentFactory::createForDateRange(
            $this->roomType,
            $checkInDate,
            2,
            ['quantity' => 10, 'allocated' => 0, 'stop_sell' => false]
        );

        $booking = BookingFactory::new()
            ->forRoomType($this->roomType)
            ->forStayType(StayType::factory()->for($this->roomType->hotel)->create())
            ->withDates($checkInDate, $checkOutDate)
            ->withTotalPrice(1000.00)
            ->pending()
            ->create();

        // Act
        $result = $this->action->execute(
            bookingId: $booking->id,
            reason: 'Change of plans'
        );

        // Assert - 100% refund for 20 days notice
        $this->assertEquals(1000.00, $result['refund_amount']);
        $this->assertEquals(1.0, $result['refund_amount'] / $result['total_price']);
    }

    /** @test */
    public function it_calculates_partial_refund_for_short_notice(): void
    {
        // Arrange - Same day cancellation = 0% refund
        $checkInDate = Carbon::today()->addDays(1);
        $checkOutDate = $checkInDate->copy()->addDays(1);
        
        // Create allotments for all dates
        AllotmentFactory::createForDateRange(
            $this->roomType,
            $checkInDate,
            1,
            ['quantity' => 10, 'allocated' => 0, 'stop_sell' => false]
        );

        $booking = BookingFactory::new()
            ->forRoomType($this->roomType)
            ->forStayType(StayType::factory()->for($this->roomType->hotel)->create())
            ->withDates($checkInDate, $checkOutDate)
            ->withTotalPrice(1000.00)
            ->pending()
            ->create();

        // Act
        $result = $this->action->execute(
            bookingId: $booking->id,
            reason: 'Emergency'
        );

        // Assert - 50% refund for 1 day notice
        $this->assertEquals(500.00, $result['refund_amount']);
    }

    /** @test */
    public function it_throws_exception_when_booking_not_found(): void
    {
        // Act & Assert
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Booking with ID 999 not found');

        $this->action->execute(
            bookingId: 999,
            reason: 'Test'
        );
    }

    /** @test */
    public function it_throws_exception_when_booking_is_already_cancelled(): void
    {
        // Arrange
        $checkInDate = Carbon::today()->addDays(10);
        $booking = BookingFactory::new()
            ->forRoomType($this->roomType)
            ->forStayType(StayType::factory()->for($this->roomType->hotel)->create())
            ->withDates($checkInDate, $checkInDate->copy()->addDays(3))
            ->cancelled()
            ->create();

        // Act & Assert
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("Cannot cancel booking with status 'cancelled'");

        $this->action->execute(
            bookingId: $booking->id,
            reason: 'Test'
        );
    }

    /** @test */
    public function it_throws_exception_when_booking_is_checked_in(): void
    {
        // Arrange
        $checkInDate = Carbon::today()->subDays(1);
        $booking = BookingFactory::new()
            ->forRoomType($this->roomType)
            ->forStayType(StayType::factory()->for($this->roomType->hotel)->create())
            ->withDates($checkInDate, $checkInDate->copy()->addDays(3))
            ->checkedIn()
            ->create();

        // Act & Assert
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("Cannot cancel booking with status 'checked_in'");

        $this->action->execute(
            bookingId: $booking->id,
            reason: 'Test'
        );
    }

    /** @test */
    public function it_throws_exception_when_checkin_date_has_passed(): void
    {
        // Arrange - Check-in was yesterday, but status is still confirmed
        $checkInDate = Carbon::yesterday();
        $booking = BookingFactory::new()
            ->forRoomType($this->roomType)
            ->forStayType(StayType::factory()->for($this->roomType->hotel)->create())
            ->withDates($checkInDate, $checkInDate->copy()->addDays(3))
            ->confirmed()
            ->create();

        // Act & Assert
        try {
            $this->action->execute(
                bookingId: $booking->id,
                reason: 'Test'
            );
            $this->fail('Expected RuntimeException was not thrown');
        } catch (RuntimeException $e) {
            // The message should contain information about the past check-in date
            $this->assertTrue(
                str_contains($e->getMessage(), 'check-in date') && 
                str_contains($e->getMessage(), 'passed'),
                'Exception message should mention check-in date and that it has passed'
            );
        }
    }

    /** @test */
    public function it_throws_exception_for_invalid_booking_id(): void
    {
        // Act & Assert
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Booking ID must be a positive integer');

        $this->action->execute(
            bookingId: 0,
            reason: 'Test'
        );
    }

    /** @test */
    public function it_throws_exception_for_empty_reason(): void
    {
        // Arrange
        $checkInDate = Carbon::today()->addDays(10);
        $booking = BookingFactory::new()
            ->forRoomType($this->roomType)
            ->forStayType(StayType::factory()->for($this->roomType->hotel)->create())
            ->withDates($checkInDate, $checkInDate->copy()->addDays(2))
            ->pending()
            ->create();

        // Act & Assert
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Cancellation reason is required');

        $this->action->execute(
            bookingId: $booking->id,
            reason: ''
        );
    }

    /** @test */
    public function it_handles_multiple_consecutive_cancellations(): void
    {
        // Arrange - Create multiple bookings for the same dates
        $startDate = Carbon::today()->addDays(10);
        $checkOutDate = $startDate->copy()->addDays(3);

        // Create allotment with allocations for multiple bookings
        AllotmentFactory::new()->create([
            'room_type_id' => $this->roomType->id,
            'date' => $startDate,
            'quantity' => 10,
            'allocated' => 3, // 3 bookings allocated
            'stop_sell' => false,
        ]);
        // Create allotments for remaining dates
        AllotmentFactory::createForDateRange(
            $this->roomType,
            $startDate->copy()->addDay(),
            2,
            ['quantity' => 10, 'allocated' => 0, 'stop_sell' => false]
        );

        $booking = BookingFactory::new()
            ->forRoomType($this->roomType)
            ->forStayType(StayType::factory()->for($this->roomType->hotel)->create())
            ->withDates($startDate, $checkOutDate)
            ->withTotalPrice(500.00)
            ->pending()
            ->create();

        // Act - Cancel one booking
        $result = $this->action->execute(
            bookingId: $booking->id,
            reason: 'Customer request'
        );

        // Assert - Allocation should be reduced from 3 to 2
        $allotment = Allotment::where('room_type_id', $this->roomType->id)
            ->whereDate('date', $startDate->format('Y-m-d'))
            ->first();

        $this->assertEquals(2, $allotment->allocated);
        $this->assertEquals($result['status'], 'cancelled');
    }

    /** @test */
    public function it_rolls_back_changes_on_failure(): void
    {
        // Arrange - Create bookings where second date has no allotment
        $startDate = Carbon::today()->addDays(5);
        $nights = 2;

        // Only create allotment for first date (second will cause failure)
        $allotment = AllotmentFactory::new()->create([
            'room_type_id' => $this->roomType->id,
            'date' => $startDate,
            'quantity' => 10,
            'allocated' => 0,
            'stop_sell' => false,
        ]);
        // No allotment for second date

        $booking = BookingFactory::new()
            ->forRoomType($this->roomType)
            ->forStayType(StayType::factory()->for($this->roomType->hotel)->create())
            ->withDates($startDate, $startDate->copy()->addDays($nights))
            ->withTotalPrice(600.00)
            ->pending()
            ->create();

        // Store the original allocated value
        $originalAllocated = $allotment->allocated;

        // Act & Assert - Should fail and rollback
        try {
            $this->action->execute(
                bookingId: $booking->id,
                reason: 'Test'
            );
            $this->fail('Expected RuntimeException was not thrown');
        } catch (RuntimeException $e) {
            // Refresh the allotment from database
            $allotment->refresh();

            // Verify first allotment was NOT modified (transaction rolled back)
            $this->assertEquals($originalAllocated, $allotment->allocated);

            // Verify booking status is unchanged
            $booking->refresh();
            $this->assertEquals('pending', $booking->status);

            // Verify the exception message mentions the missing allotment
            $this->assertStringContainsString('No allotment found', $e->getMessage());
        }
    }

    /** @test */
    public function it_calculates_refund_for_14_days_notice(): void
    {
        // Arrange - Exactly 14 days = 100% refund
        $checkInDate = Carbon::today()->addDays(14);
        $checkOutDate = $checkInDate->copy()->addDays(2);
        
        // Create allotments for all dates
        AllotmentFactory::createForDateRange(
            $this->roomType,
            $checkInDate,
            2,
            ['quantity' => 10, 'allocated' => 0, 'stop_sell' => false]
        );

        $booking = BookingFactory::new()
            ->forRoomType($this->roomType)
            ->forStayType(StayType::factory()->for($this->roomType->hotel)->create())
            ->withDates($checkInDate, $checkOutDate)
            ->withTotalPrice(1000.00)
            ->pending()
            ->create();

        // Act
        $result = $this->action->execute(
            bookingId: $booking->id,
            reason: 'Test'
        );

        // Assert
        $this->assertEquals(1000.00, $result['refund_amount']);
    }

    /** @test */
    public function it_calculates_refund_for_7_days_notice(): void
    {
        // Arrange - 7 days = 90% refund
        $checkInDate = Carbon::today()->addDays(7);
        $checkOutDate = $checkInDate->copy()->addDays(2);
        
        // Create allotments for all dates
        AllotmentFactory::createForDateRange(
            $this->roomType,
            $checkInDate,
            2,
            ['quantity' => 10, 'allocated' => 0, 'stop_sell' => false]
        );

        $booking = BookingFactory::new()
            ->forRoomType($this->roomType)
            ->forStayType(StayType::factory()->for($this->roomType->hotel)->create())
            ->withDates($checkInDate, $checkOutDate)
            ->withTotalPrice(1000.00)
            ->pending()
            ->create();

        // Act
        $result = $this->action->execute(
            bookingId: $booking->id,
            reason: 'Test'
        );

        // Assert
        $this->assertEquals(900.00, $result['refund_amount']);
    }
}
