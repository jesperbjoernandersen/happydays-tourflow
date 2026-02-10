<?php

namespace Tests\Unit\Actions;

use App\Actions\ReduceAllotment;
use App\Models\Allotment;
use App\Models\RoomType;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Database\Factories\AllotmentFactory;
use Database\Factories\RoomTypeFactory;
use Tests\TestCase;
use RuntimeException;
use InvalidArgumentException;

/**
 * Tests for ReduceAllotment action.
 */
class ReduceAllotmentTest extends TestCase
{
    use DatabaseMigrations;

    private ReduceAllotment $action;
    private RoomType $roomType;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->action = new ReduceAllotment();

        // Create a test room type
        $this->roomType = RoomTypeFactory::new()->create([
            'name' => 'Standard Room',
            'code' => 'STD',
        ]);
    }

    /** @test */
    public function it_reduces_allotment_for_single_night(): void
    {
        // Arrange
        $tomorrow = Carbon::tomorrow();
        $allotment = AllotmentFactory::new()->create([
            'room_type_id' => $this->roomType->id,
            'date' => $tomorrow,
            'quantity' => 10,
            'allocated' => 5,
            'stop_sell' => false,
        ]);

        // Act
        $result = $this->action->execute(
            roomTypeId: $this->roomType->id,
            checkinDate: $tomorrow->format('Y-m-d'),
            nights: 1
        );

        // Assert
        $this->assertEquals(1, $result['total_reduced']);
        $this->assertCount(1, $result['dates']);

        // Verify allotment was updated
        $allotment->refresh();
        $this->assertEquals(6, $allotment->allocated);

        // Verify result structure
        $this->assertEquals($tomorrow->format('Y-m-d'), $result['dates'][0]['date']);
        $this->assertEquals(6, $result['dates'][0]['allocated']);
        $this->assertEquals(10, $result['dates'][0]['quantity']);
        $this->assertEquals(4, $result['dates'][0]['remaining']);
    }

    /** @test */
    public function it_reduces_allotment_for_multiple_nights(): void
    {
        // Arrange
        $startDate = Carbon::tomorrow();
        $nights = 3;

        // Create allotments for each night
        for ($i = 0; $i < $nights; $i++) {
            AllotmentFactory::new()->create([
                'room_type_id' => $this->roomType->id,
                'date' => $startDate->copy()->addDays($i),
                'quantity' => 5,
                'allocated' => 2,
                'stop_sell' => false,
            ]);
        }

        // Act
        $result = $this->action->execute(
            roomTypeId: $this->roomType->id,
            checkinDate: $startDate->format('Y-m-d'),
            nights: $nights
        );

        // Assert
        $this->assertEquals(3, $result['total_reduced']);
        $this->assertCount(3, $result['dates']);

        // Verify each allotment was updated
        for ($i = 0; $i < $nights; $i++) {
            $allotment = Allotment::where('room_type_id', $this->roomType->id)
                ->whereDate('date', $startDate->copy()->addDays($i)->format('Y-m-d'))
                ->first();

            $this->assertEquals(3, $allotment->allocated);
        }
    }

    /** @test */
    public function it_throws_exception_when_allotment_not_found(): void
    {
        // Arrange
        $date = Carbon::tomorrow();

        // Act & Assert
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("No allotment found for room type {$this->roomType->id} on date {$date->format('Y-m-d')}");

        $this->action->execute(
            roomTypeId: $this->roomType->id,
            checkinDate: $date->format('Y-m-d'),
            nights: 1
        );
    }

    /** @test */
    public function it_throws_exception_when_stop_sell_is_active(): void
    {
        // Arrange
        $tomorrow = Carbon::tomorrow();
        AllotmentFactory::new()->create([
            'room_type_id' => $this->roomType->id,
            'date' => $tomorrow,
            'quantity' => 10,
            'allocated' => 5,
            'stop_sell' => true,
        ]);

        // Act & Assert
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("Cannot reduce allotment: stop sell is active");

        $this->action->execute(
            roomTypeId: $this->roomType->id,
            checkinDate: $tomorrow->format('Y-m-d'),
            nights: 1
        );
    }

    /** @test */
    public function it_throws_exception_when_no_rooms_available(): void
    {
        // Arrange
        $tomorrow = Carbon::tomorrow();
        AllotmentFactory::new()->create([
            'room_type_id' => $this->roomType->id,
            'date' => $tomorrow,
            'quantity' => 5,
            'allocated' => 5, // Fully allocated
            'stop_sell' => false,
        ]);

        // Act & Assert
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("Cannot reduce allotment: no rooms available");

        $this->action->execute(
            roomTypeId: $this->roomType->id,
            checkinDate: $tomorrow->format('Y-m-d'),
            nights: 1
        );
    }

    /** @test */
    public function it_throws_exception_when_invalid_room_type_id(): void
    {
        // Arrange
        $date = Carbon::tomorrow();

        // Act & Assert - InvalidArgumentException for non-positive ID
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Room type ID must be a positive integer');

        $this->action->execute(
            roomTypeId: 0,
            checkinDate: $date->format('Y-m-d'),
            nights: 1
        );
    }

    /** @test */
    public function it_throws_exception_when_invalid_nights(): void
    {
        // Arrange
        $date = Carbon::tomorrow();

        // Act & Assert
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Nights must be a positive integer');

        $this->action->execute(
            roomTypeId: $this->roomType->id,
            checkinDate: $date->format('Y-m-d'),
            nights: 0
        );
    }

    /** @test */
    public function it_throws_exception_when_past_checkin_date(): void
    {
        // Arrange
        $pastDate = Carbon::yesterday();

        // Act & Assert
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Check-in date cannot be in the past');

        $this->action->execute(
            roomTypeId: $this->roomType->id,
            checkinDate: $pastDate->format('Y-m-d'),
            nights: 1
        );
    }

    /** @test */
    public function it_rolls_back_changes_on_failure(): void
    {
        // Arrange
        $startDate = Carbon::tomorrow();

        // Create two allotments - one will fail
        AllotmentFactory::new()->create([
            'room_type_id' => $this->roomType->id,
            'date' => $startDate,
            'quantity' => 10,
            'allocated' => 5,
            'stop_sell' => false,
        ]);

        AllotmentFactory::new()->create([
            'room_type_id' => $this->roomType->id,
            'date' => $startDate->copy()->addDay(),
            'quantity' => 5,
            'allocated' => 5, // Fully allocated - will cause failure
            'stop_sell' => false,
        ]);

        // Act & Assert - Should fail on second date
        $this->expectException(RuntimeException::class);

        try {
            $this->action->execute(
                roomTypeId: $this->roomType->id,
                checkinDate: $startDate->format('Y-m-d'),
                nights: 2
            );
        } catch (RuntimeException $e) {
            // Verify first allotment was rolled back
            $firstAllotment = Allotment::where('room_type_id', $this->roomType->id)
                ->where('date', $startDate->format('Y-m-d'))
                ->first();

            $this->assertEquals(5, $firstAllotment->allocated); // Should be unchanged
            throw $e;
        }
    }

    /** @test */
    public function it_allows_today_as_checkin_date(): void
    {
        // Arrange
        $today = Carbon::today();
        $allotment = AllotmentFactory::new()->create([
            'room_type_id' => $this->roomType->id,
            'date' => $today,
            'quantity' => 10,
            'allocated' => 5,
            'stop_sell' => false,
        ]);

        // Act
        $result = $this->action->execute(
            roomTypeId: $this->roomType->id,
            checkinDate: $today->format('Y-m-d'),
            nights: 1
        );

        // Assert
        $this->assertEquals(1, $result['total_reduced']);
        $allotment->refresh();
        $this->assertEquals(6, $allotment->allocated);
    }

    /** @test */
    public function it_returns_correct_remaining_values(): void
    {
        // Arrange
        $tomorrow = Carbon::tomorrow();
        $allotment = AllotmentFactory::new()->create([
            'room_type_id' => $this->roomType->id,
            'date' => $tomorrow,
            'quantity' => 10,
            'allocated' => 3,
            'stop_sell' => false,
        ]);

        // Act
        $result = $this->action->execute(
            roomTypeId: $this->roomType->id,
            checkinDate: $tomorrow->format('Y-m-d'),
            nights: 1
        );

        // Assert
        $this->assertEquals(6, $result['dates'][0]['remaining']); // 10 - 4 = 6
    }
}
