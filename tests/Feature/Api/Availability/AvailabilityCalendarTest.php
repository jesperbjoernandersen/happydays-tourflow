<?php

namespace Tests\Feature\Api\Availability;

use App\Models\Hotel;
use App\Models\StayType;
use App\Models\RoomType;
use App\Models\RatePlan;
use App\Models\RateRule;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AvailabilityCalendarTest extends TestCase
{
    use RefreshDatabase;

    private Hotel $hotel;
    private StayType $stayType;
    private RoomType $roomType;
    private RatePlan $ratePlan;

    protected function setUp(): void
    {
        parent::setUp();

        $this->hotel = Hotel::factory()->create([
            'name' => 'Test Hotel',
            'code' => 'TEST-HOTEL',
        ]);

        $this->stayType = StayType::factory()->create([
            'hotel_id' => $this->hotel->id,
            'name' => 'Test Package',
            'code' => 'TEST-PKG',
            'nights' => 1,
            'is_active' => true,
        ]);

        $this->roomType = RoomType::factory()->create([
            'hotel_id' => $this->hotel->id,
            'name' => 'Standard Room',
            'code' => 'STD',
            'base_occupancy' => 2,
            'max_occupancy' => 4,
            'is_active' => true,
        ]);

        $this->ratePlan = RatePlan::factory()->create([
            'hotel_id' => $this->hotel->id,
            'name' => 'Best Available Rate',
            'code' => 'BAR',
            'pricing_model' => 'unit_included_occupancy',
            'is_active' => true,
        ]);

        // Create rate rules for the next 3 months
        RateRule::factory()->create([
            'rate_plan_id' => $this->ratePlan->id,
            'stay_type_id' => $this->stayType->id,
            'room_type_id' => $this->roomType->id,
            'start_date' => today()->subMonth(),
            'end_date' => today()->addMonths(3),
            'base_price' => 100.00,
            'included_occupancy' => 2,
        ]);
    }

    /** @test */
    public function it_returns_calendar_for_valid_month()
    {
        $year = today()->year;
        $month = today()->month + 1;

        $response = $this->getJson("/api/availability/{$this->stayType->id}/calendar/{$year}/{$month}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'stay_type_id',
                'stay_type_name',
                'year',
                'month',
                'month_name',
                'currency',
                'summary' => [
                    'total_days',
                    'available_days',
                    'unavailable_days',
                    'min_price',
                    'max_price',
                    'avg_price',
                    'currency',
                ],
                'available_dates',
                'days',
                'stay_type',
                'occupancy',
                'room_type',
                'rate_plan',
            ])
            ->assertJson([
                'success' => true,
                'stay_type_id' => $this->stayType->id,
                'year' => $year,
                'month' => $month,
            ]);
    }

    /** @test */
    public function it_returns_correct_number_of_days_in_month()
    {
        $year = today()->year;
        $month = 2; // February

        // Check leap year consideration
        $daysInFebruary = date('L', strtotime("$year-01-01")) ? 29 : 28;

        $response = $this->getJson("/api/availability/{$this->stayType->id}/calendar/{$year}/{$month}");

        $response->assertStatus(200);

        $data = $response->json();
        $this->assertCount($daysInFebruary, $data['days']);
    }

    /** @test */
    public function it_includes_weekend_information()
    {
        $year = today()->year;
        $month = today()->month + 1;

        $response = $this->getJson("/api/availability/{$this->stayType->id}/calendar/{year}/{$month}");

        $response->assertStatus(200);

        $data = $response->json();

        // Check that some days are marked as weekend
        $weekendDays = array_filter($data['days'], fn($day) => $day['is_weekend']);
        $weekdayDays = array_filter($data['days'], fn($day) => !$day['is_weekend']);

        $this->assertGreaterThan(0, count($weekendDays));
        $this->assertGreaterThan(0, count($weekdayDays));
    }

    /** @test */
    public function it_returns_price_for_each_day()
    {
        $year = today()->year;
        $month = today()->month + 1;

        $response = $this->getJson("/api/availability/{$this->stayType->id}/calendar/{$year}/{$month}");

        $response->assertStatus(200);

        $data = $response->json();

        // All days should have a price (or null if no rate)
        $daysWithPrice = array_filter($data['days'], fn($day) => $day['price'] !== null);

        $this->assertGreaterThan(0, count($daysWithPrice));
    }

    /** @test */
    public function it_validates_year_parameter()
    {
        $response = $this->getJson("/api/availability/{$this->stayType->id}/calendar/2019/1");

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => 'Year must be between 2020 and 2100',
            ]);
    }

    /** @test */
    public function it_validates_month_parameter()
    {
        $response = $this->getJson("/api/availability/{$this->stayType->id}/calendar/" . today()->year . "/0");

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => 'Month must be between 1 and 12',
            ]);
    }

    /** @test */
    public function it_returns_not_available_when_no_rate_for_date()
    {
        // Delete all rate rules
        RateRule::query()->delete();

        $year = today()->year;
        $month = today()->month + 1;

        $response = $this->getJson("/api/availability/{$this->stayType->id}/calendar/{$year}/{$month}");

        $response->assertStatus(200);

        $data = $response->json();

        // All days should be unavailable
        $availableDays = array_filter($data['days'], fn($day) => $day['is_available']);
        $this->assertCount(0, $availableDays);
    }

    /** @test */
    public function it_can_filter_by_occupancy()
    {
        $year = today()->year;
        $month = today()->month + 1;

        $response = $this->getJson("/api/availability/{$this->stayType->id}/calendar/{$year}/{$month}?occupancy[adults]=2&occupancy[children]=1");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'occupancy',
            ])
            ->assertJson([
                'occupancy' => [
                    'adults' => 2,
                    'children' => 1,
                    'infants' => 0,
                    'total_guests' => 3,
                ],
            ]);
    }

    /** @test */
    public function it_returns_summary_statistics()
    {
        $year = today()->year;
        $month = today()->month + 1;

        $response = $this->getJson("/api/availability/{$this->stayType->id}/calendar/{$year}/{$month}");

        $response->assertStatus(200);

        $data = $response->json();
        $summary = $data['summary'];

        $this->assertArrayHasKey('total_days', $summary);
        $this->assertArrayHasKey('available_days', $summary);
        $this->assertArrayHasKey('min_price', $summary);
        $this->assertArrayHasKey('max_price', $summary);
        $this->assertArrayHasKey('avg_price', $summary);

        // Available days should be less than or equal to total days
        $this->assertLessThanOrEqual($summary['total_days'], $summary['available_days']);
    }

    /** @test */
    public function it_returns_list_of_available_dates()
    {
        $year = today()->year;
        $month = today()->month + 1;

        $response = $this->getJson("/api/availability/{$this->stayType->id}/calendar/{$year}/{$month}");

        $response->assertStatus(200);

        $data = $response->json();

        // available_dates should be an array of date strings
        $this->assertIsArray($data['available_dates']);

        // Each item should be a valid date string
        foreach ($data['available_dates'] as $date) {
            $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2}$/', $date);
        }
    }
}
