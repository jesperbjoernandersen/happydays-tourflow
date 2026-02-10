<?php

namespace Tests\Feature\Livewire;

use App\Livewire\StayTypeSelector;
use App\Models\Hotel;
use App\Models\StayType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class StayTypeSelectorTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_displays_active_stay_types()
    {
        $hotel = Hotel::factory()->create();
        $activeStayType = StayType::factory()->create([
            'hotel_id' => $hotel->id,
            'is_active' => true,
            'name' => 'Active Package',
        ]);
        $inactiveStayType = StayType::factory()->create([
            'hotel_id' => $hotel->id,
            'is_active' => false,
            'name' => 'Inactive Package',
        ]);

        Livewire::test(StayTypeSelector::class)
            ->assertSee('Active Package')
            ->assertDontSee('Inactive Package');
    }

    #[Test]
    public function it_can_filter_by_hotel()
    {
        $hotel1 = Hotel::factory()->create();
        $hotel2 = Hotel::factory()->create();
        $stayType1 = StayType::factory()->create([
            'hotel_id' => $hotel1->id,
            'is_active' => true,
            'name' => 'Hotel 1 Package',
        ]);
        $stayType2 = StayType::factory()->create([
            'hotel_id' => $hotel2->id,
            'is_active' => true,
            'name' => 'Hotel 2 Package',
        ]);

        Livewire::test(StayTypeSelector::class, ['hotelId' => $hotel1->id])
            ->assertSee('Hotel 1 Package')
            ->assertDontSee('Hotel 2 Package');
    }

    #[Test]
    public function it_selects_stay_type_and_emits_event()
    {
        $hotel = Hotel::factory()->create();
        $stayType = StayType::factory()->create([
            'hotel_id' => $hotel->id,
            'is_active' => true,
        ]);

        Livewire::test(StayTypeSelector::class)
            ->call('selectStayType', $stayType->id)
            ->assertSet('selectedStayTypeId', $stayType->id)
            ->assertDispatched('stayTypeSelected', stayTypeId: $stayType->id);
    }

    #[Test]
    public function it_shows_validation_error_when_nothing_selected()
    {
        $hotel = Hotel::factory()->create();
        StayType::factory()->create([
            'hotel_id' => $hotel->id,
            'is_active' => true,
        ]);

        Livewire::test(StayTypeSelector::class)
            ->call('validateSelection')
            ->assertSet('showValidation', true);
    }

    #[Test]
    public function it_validates_selection_successfully()
    {
        $hotel = Hotel::factory()->create();
        $stayType = StayType::factory()->create([
            'hotel_id' => $hotel->id,
            'is_active' => true,
        ]);

        Livewire::test(StayTypeSelector::class)
            ->set('selectedStayTypeId', $stayType->id)
            ->call('validateSelection')
            ->assertSet('showValidation', false);
    }

    #[Test]
    public function it_displays_stay_type_details()
    {
        $hotel = Hotel::factory()->create();
        $stayType = StayType::factory()->create([
            'hotel_id' => $hotel->id,
            'name' => 'All-Inclusive Week',
            'description' => '7 nights of all-inclusive fun',
            'nights' => 7,
            'included_board_type' => 'AI',
            'is_active' => true,
        ]);

        Livewire::test(StayTypeSelector::class)
            ->assertSee('All-Inclusive Week')
            ->assertSee('7 nights of all-inclusive fun')
            ->assertSee('7')
            ->assertSee('All-Inclusive');
    }

    #[Test]
    public function it_handles_empty_stay_types()
    {
        Livewire::test(StayTypeSelector::class)
            ->assertSee('No stay types available');
    }
}
