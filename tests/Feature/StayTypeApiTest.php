<?php

namespace Tests\Feature;

use App\Models\Hotel;
use App\Models\HotelAgePolicy;
use App\Models\RatePlan;
use App\Models\RateRule;
use App\Models\StayType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StayTypeApiTest extends TestCase
{
    use RefreshDatabase;

    protected Hotel $hotel;
    protected RatePlan $ratePlan;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test data
        $this->hotel = Hotel::factory()->create([
            'name' => 'Test Hotel',
            'code' => 'TH001',
            'is_active' => true,
        ]);

        HotelAgePolicy::factory()->create([
            'hotel_id' => $this->hotel->id,
            'name' => 'Standard Policy',
            'infant_max_age' => 2,
            'child_max_age' => 12,
            'adult_min_age' => 18,
        ]);

        $this->ratePlan = RatePlan::factory()->create([
            'hotel_id' => $this->hotel->id,
            'name' => 'Standard Rate',
            'code' => 'STD',
            'is_active' => true,
        ]);
    }

    /** @test */
    public function it_can_list_all_active_stay_types(): void
    {
        // Create active and inactive stay types
        $activeStayType = StayType::factory()->create([
            'hotel_id' => $this->hotel->id,
            'name' => 'Active Stay',
            'code' => 'ACTIVE1',
            'is_active' => true,
        ]);

        StayType::factory()->create([
            'hotel_id' => $this->hotel->id,
            'name' => 'Inactive Stay',
            'code' => 'INACTIVE1',
            'is_active' => false,
        ]);

        $response = $this->getJson('/api/stay-types');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'hotel_id',
                        'name',
                        'description',
                        'code',
                        'nights',
                        'included_board_type',
                        'is_active',
                    ],
                ],
                'meta' => ['total'],
            ])
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.name', 'Active Stay');
    }

    /** @test */
    public function it_can_filter_stay_types_by_hotel_id(): void
    {
        $hotel2 = Hotel::factory()->create([
            'name' => 'Second Hotel',
            'code' => 'SH001',
        ]);

        StayType::factory()->create([
            'hotel_id' => $this->hotel->id,
            'name' => 'Hotel 1 Stay',
            'code' => 'H1S1',
            'is_active' => true,
        ]);

        StayType::factory()->create([
            'hotel_id' => $hotel2->id,
            'name' => 'Hotel 2 Stay',
            'code' => 'H2S1',
            'is_active' => true,
        ]);

        $response = $this->getJson('/api/stay-types?hotel_id=' . $this->hotel->id);

        $response->assertStatus(200)
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.name', 'Hotel 1 Stay');
    }

    /** @test */
    public function it_can_get_a_single_stay_type_with_details(): void
    {
        $stayType = StayType::factory()->create([
            'hotel_id' => $this->hotel->id,
            'name' => 'Weekend Getaway',
            'code' => 'WEEKEND',
            'nights' => 2,
            'included_board_type' => 'Half Board',
            'is_active' => true,
        ]);

        RateRule::factory()->create([
            'stay_type_id' => $stayType->id,
            'rate_plan_id' => $this->ratePlan->id,
            'base_price' => 299.99,
            'price_per_adult' => 50.00,
            'price_per_child' => 25.00,
        ]);

        $response = $this->getJson('/api/stay-types/' . $stayType->id);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'hotel_id',
                    'name',
                    'description',
                    'code',
                    'nights',
                    'included_board_type',
                    'is_active',
                    'hotel',
                    'age_policy',
                    'pricing_hints',
                ],
            ])
            ->assertJsonPath('data.name', 'Weekend Getaway')
            ->assertJsonPath('data.nights', 2)
            ->assertJsonPath('data.hotel.name', 'Test Hotel')
            ->assertJsonPath('data.age_policy.0.name', 'Standard Policy')
            ->assertJsonPath('data.pricing_hints.0.base_price', '299.99');
    }

    /** @test */
    public function it_returns_404_for_non_existent_stay_type(): void
    {
        $response = $this->getJson('/api/stay-types/9999');

        $response->assertStatus(404)
            ->assertJsonPath('message', 'Stay type not found');
    }

    /** @test */
    public function it_can_create_a_new_stay_type(): void
    {
        $stayTypeData = [
            'hotel_id' => $this->hotel->id,
            'name' => 'New Stay Type',
            'description' => 'A test stay type',
            'code' => 'NEWSTAY',
            'nights' => 7,
            'included_board_type' => 'Full Board',
            'is_active' => true,
        ];

        $response = $this->postJson('/api/stay-types', $stayTypeData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'id',
                    'hotel_id',
                    'name',
                    'description',
                    'code',
                    'nights',
                    'included_board_type',
                    'is_active',
                    'created_at',
                    'updated_at',
                ],
            ])
            ->assertJsonPath('data.name', 'New Stay Type')
            ->assertJsonPath('data.nights', 7);

        $this->assertDatabaseHas('stay_types', [
            'name' => 'New Stay Type',
            'code' => 'NEWSTAY',
            'nights' => 7,
        ]);
    }

    /** @test */
    public function it_validates_required_fields_on_create(): void
    {
        $response = $this->postJson('/api/stay-types', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['hotel_id', 'name', 'code', 'nights']);
    }

    /** @test */
    public function it_validates_unique_code_on_create(): void
    {
        StayType::factory()->create([
            'hotel_id' => $this->hotel->id,
            'code' => 'UNIQUE1',
        ]);

        $response = $this->postJson('/api/stay-types', [
            'hotel_id' => $this->hotel->id,
            'name' => 'Another Stay',
            'code' => 'UNIQUE1', // Duplicate code
            'nights' => 3,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['code']);
    }

    /** @test */
    public function it_can_update_a_stay_type(): void
    {
        $stayType = StayType::factory()->create([
            'hotel_id' => $this->hotel->id,
            'name' => 'Original Name',
            'code' => 'ORIG1',
            'nights' => 3,
        ]);

        $updateData = [
            'name' => 'Updated Name',
            'nights' => 5,
        ];

        $response = $this->putJson('/api/stay-types/' . $stayType->id, $updateData);

        $response->assertStatus(200)
            ->assertJsonPath('data.name', 'Updated Name')
            ->assertJsonPath('data.nights', 5);

        $this->assertDatabaseHas('stay_types', [
            'id' => $stayType->id,
            'name' => 'Updated Name',
            'nights' => 5,
        ]);
    }

    /** @test */
    public function it_validates_unique_code_on_update(): void
    {
        StayType::factory()->create([
            'hotel_id' => $this->hotel->id,
            'code' => 'EXIST1',
        ]);

        $stayType = StayType::factory()->create([
            'hotel_id' => $this->hotel->id,
            'code' => 'UPDATE1',
        ]);

        $response = $this->putJson('/api/stay-types/' . $stayType->id, [
            'code' => 'EXIST1', // Duplicate code
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['code']);
    }

    /** @test */
    public function it_can_soft_delete_a_stay_type(): void
    {
        $stayType = StayType::factory()->create([
            'hotel_id' => $this->hotel->id,
            'name' => 'To Be Deleted',
            'code' => 'DELETE1',
        ]);

        $response = $this->deleteJson('/api/stay-types/' . $stayType->id);

        $response->assertStatus(200)
            ->assertJsonPath('message', 'Stay type deleted successfully');

        $this->assertSoftDeleted('stay_types', [
            'id' => $stayType->id,
        ]);
    }

    /** @test */
    public function it_includes_pricing_hints_in_list(): void
    {
        $stayType = StayType::factory()->create([
            'hotel_id' => $this->hotel->id,
            'name' => 'Pricing Test',
            'code' => 'PRICING',
            'is_active' => true,
        ]);

        RateRule::factory()->create([
            'stay_type_id' => $stayType->id,
            'rate_plan_id' => $this->ratePlan->id,
            'base_price' => 199.99,
            'price_per_adult' => 40.00,
            'included_occupancy' => 2,
        ]);

        $response = $this->getJson('/api/stay-types');

        $response->assertStatus(200)
            ->assertJsonPath('data.0.pricing_hints.0.base_price', '199.99');
    }
}
