<?php

namespace Tests\Unit\Livewire;

use App\Livewire\DateOccupancySelector;
use App\Models\RoomType;
use App\Models\StayType;
use Carbon\Carbon;
use Database\Factories\RoomTypeFactory;
use Database\Factories\StayTypeFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DateOccupancySelectorTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_renders_successfully(): void
    {
        Livewire::test(DateOccupancySelector::class)
            ->assertStatus(200);
    }

    /** @test */
    public function it_initializes_with_default_values(): void
    {
        $component = Livewire::test(DateOccupancySelector::class);

        $component->assertSet('adults', 1);
        $component->assertSet('children', 0);
        $component->assertSet('infants', 0);
        $component->assertSet('extraBeds', 0);
        $component->assertSet('totalGuests', 1);
    }

    /** @test */
    public function it_mounts_with_provided_values(): void
    {
        $stayType = StayTypeFactory::new()->withNights(7)->create();
        $roomType = RoomTypeFactory::new()->withMaxOccupancy(4)->create();

        Livewire::test(DateOccupancySelector::class, [
            'stayType' => $stayType,
            'roomType' => $roomType,
            'initialAdults' => 2,
            'initialChildren' => 1,
            'initialInfants' => 1,
        ])
            ->assertSet('adults', 2)
            ->assertSet('children', 1)
            ->assertSet('infants', 1)
            ->assertSet('stayType.id', $stayType->id)
            ->assertSet('roomType.id', $roomType->id);
    }

    /** @test */
    public function it_sets_max_date_to_one_year_ahead(): void
    {
        $component = Livewire::test(DateOccupancySelector::class);
        $expectedMaxDate = Carbon::today()->addYear();

        $component->assertTrue($component->get('maxDate')->isSameDay($expectedMaxDate));
    }

    /** @test */
    public function it_configures_room_type_limits(): void
    {
        $roomType = RoomType::factory()
            ->create([
                'max_occupancy' => 4,
                'extra_bed_slots' => 2,
            ]);

        $component = Livewire::test(DateOccupancySelector::class, [
            'roomType' => $roomType,
        ]);

        $component->assertSet('maxAdults', 4);
        $component->assertSet('maxChildren', 4);
        $component->assertSet('supportsExtraBeds', true);
    }

    // ==================== ADULTS COUNTER TESTS ====================

    /** @test */
    public function it_increments_adults(): void
    {
        $roomType = RoomType::factory()->create(['max_occupancy' => 4]);

        $component = Livewire::test(DateOccupancySelector::class, [
            'roomType' => $roomType,
        ]);

        $component->call('incrementAdults');
        $component->assertSet('adults', 2);
        $component->assertSet('totalGuests', 2);
    }

    /** @test */
    public function it_prevents_adults_from_going_below_minimum(): void
    {
        $component = Livewire::test(DateOccupancySelector::class);

        $component->call('decrementAdults');
        $component->assertSet('adults', 1); // Should remain at 1
        $component->assertSet('totalGuests', 1);
    }

    /** @test */
    public function it_prevents_adults_from_exceeding_max(): void
    {
        $roomType = RoomType::factory()->create(['max_occupancy' => 2]);

        $component = Livewire::test(DateOccupancySelector::class, [
            'roomType' => $roomType,
        ]);

        $component->call('incrementAdults');
        $component->assertSet('adults', 2);

        $component->call('incrementAdults');
        $component->assertSet('adults', 2); // Should remain at max (2)
    }

    // ==================== CHILDREN COUNTER TESTS ====================

    /** @test */
    public function it_increments_children(): void
    {
        $roomType = RoomTypeFactory::new()->withMaxOccupancy(4)->create();

        $component = Livewire::test(DateOccupancySelector::class, [
            'roomType' => $roomType,
        ]);

        $component->call('incrementChildren');
        $component->assertSet('children', 1);
        $component->assertSet('totalGuests', 2);
    }

    /** @test */
    public function it_prevents_children_from_going_below_zero(): void
    {
        $component = Livewire::test(DateOccupancySelector::class);

        $component->call('decrementChildren');
        $component->assertSet('children', 0); // Should remain at 0
    }

    /** @test */
    public function it_prevents_children_from_exceeding_max_occupancy(): void
    {
        $roomType = RoomType::factory()->create(['max_occupancy' => 2]);

        $component = Livewire::test(DateOccupancySelector::class, [
            'roomType' => $roomType,
            'initialAdults' => 2,
        ]);

        // At max occupancy, children cannot be incremented
        $component->call('incrementChildren');
        $component->assertSet('children', 0); // Should remain at 0
    }

    // ==================== INFANTS COUNTER TESTS ====================

    /** @test */
    public function it_increments_infants(): void
    {
        $component = Livewire::test(DateOccupancySelector::class);

        $component->call('incrementInfants');
        $component->assertSet('infants', 1);
    }

    /** @test */
    public function it_prevents_infants_from_going_below_zero(): void
    {
        $component = Livewire::test(DateOccupancySelector::class);

        $component->call('decrementInfants');
        $component->assertSet('infants', 0); // Should remain at 0
    }

    // ==================== EXTRA BEDS TESTS ====================

    /** @test */
    public function it_does_not_show_extra_beds_when_not_supported(): void
    {
        $roomType = RoomType::factory()->create(['extra_bed_slots' => 0]);

        $component = Livewire::test(DateOccupancySelector::class, [
            'roomType' => $roomType,
        ]);

        $component->assertSet('supportsExtraBeds', false);
    }

    /** @test */
    public function it_shows_extra_beds_when_supported(): void
    {
        $roomType = RoomType::factory()->create(['extra_bed_slots' => 2]);

        $component = Livewire::test(DateOccupancySelector::class, [
            'roomType' => $roomType,
        ]);

        $component->assertSet('supportsExtraBeds', true);
    }

    /** @test */
    public function it_increments_extra_beds(): void
    {
        $roomType = RoomType::factory()->create(['extra_bed_slots' => 2]);

        $component = Livewire::test(DateOccupancySelector::class, [
            'roomType' => $roomType,
        ]);

        $component->call('incrementExtraBeds');
        $component->assertSet('extraBeds', 1);

        $component->call('incrementExtraBeds');
        $component->assertSet('extraBeds', 2);
    }

    /** @test */
    public function it_prevents_extra_beds_from_exceeding_limit(): void
    {
        $roomType = RoomType::factory()->create(['extra_bed_slots' => 1]);

        $component = Livewire::test(DateOccupancySelector::class, [
            'roomType' => $roomType,
        ]);

        $component->call('incrementExtraBeds');
        $component->assertSet('extraBeds', 1);

        $component->call('incrementExtraBeds');
        $component->assertSet('extraBeds', 1); // Should remain at max (1)
    }

    // ==================== DATE SELECTION TESTS ====================

    /** @test */
    public function it_sets_checkin_date_to_tomorrow_by_default(): void
    {
        $component = Livewire::test(DateOccupancySelector::class);

        $component->assertSet('checkinDate', Carbon::tomorrow()->format('Y-m-d'));
    }

    /** @test */
    public function it_sets_checkin_date_to_today(): void
    {
        $component = Livewire::test(DateOccupancySelector::class);

        $component->call('setToday');
        $component->assertSet('checkinDate', Carbon::today()->format('Y-m-d'));
    }

    /** @test */
    public function it_sets_checkin_date_to_tomorrow(): void
    {
        $component = Livewire::test(DateOccupancySelector::class);

        $component->call('setTomorrow');
        $component->assertSet('checkinDate', Carbon::tomorrow()->format('Y-m-d'));
    }

    /** @test */
    public function it_updates_checkin_date(): void
    {
        $component = Livewire::test(DateOccupancySelector::class);

        $component->set('checkinDate', Carbon::now()->addDays(5)->format('Y-m-d'));
        $component->assertSet('checkinDate', Carbon::now()->addDays(5)->format('Y-m-d'));
    }

    // ==================== COMPUTED PROPERTIES TESTS ====================

    /** @test */
    public function it_calculates_total_guests_correctly(): void
    {
        $component = Livewire::test(DateOccupancySelector::class, [
            'initialAdults' => 2,
            'initialChildren' => 1,
        ]);

        $component->assertSet('totalGuests', 3);
    }

    /** @test */
    public function it_returns_nights_from_stay_type(): void
    {
        $stayType = StayTypeFactory::new()->withNights(7)->create();

        $component = Livewire::test(DateOccupancySelector::class, [
            'stayType' => $stayType,
        ]);

        $component->assertSet('nights', 7);
    }

    /** @test */
    public function it_defaults_to_one_night_without_stay_type(): void
    {
        $component = Livewire::test(DateOccupancySelector::class);

        $component->assertSet('nights', 1);
    }

    /** @test */
    public function it_calculates_checkout_date(): void
    {
        $stayType = StayTypeFactory::new()->withNights(5)->create();

        $component = Livewire::test(DateOccupancySelector::class, [
            'stayType' => $stayType,
        ]);

        $component->set('checkinDate', '2026-02-15');
        $component->assertSet('checkoutDate', '2026-02-20');
    }

    /** @test */
    public function it_detects_exceeds_occupancy(): void
    {
        $roomType = RoomType::factory()->create(['max_occupancy' => 2]);

        $component = Livewire::test(DateOccupancySelector::class, [
            'roomType' => $roomType,
            'initialAdults' => 2,
            'initialChildren' => 1,
        ]);

        $component->assertSet('exceedsOccupancy', true);
    }

    /** @test */
    public function it_does_not_exceed_occupancy_when_within_limit(): void
    {
        $roomType = RoomType::factory()->create(['max_occupancy' => 4]);

        $component = Livewire::test(DateOccupancySelector::class, [
            'roomType' => $roomType,
            'initialAdults' => 2,
            'initialChildren' => 1,
        ]);

        $component->assertSet('exceedsOccupancy', false);
    }

    // ==================== VALIDATION TESTS ====================

    /** @test */
    public function it_validates_checkin_date_is_required(): void
    {
        $component = Livewire::test(DateOccupancySelector::class);
        $component->set('checkinDate', '');

        $component->call('validateSelection');

        $component->assertHasErrors(['checkinDate' => 'required']);
    }

    /** @test */
    public function it_validates_checkin_date_is_not_in_past(): void
    {
        $component = Livewire::test(DateOccupancySelector::class);
        $component->set('checkinDate', Carbon::yesterday()->format('Y-m-d'));

        $component->call('validateSelection');

        $component->assertHasErrors(['checkinDate' => 'after_or_equal']);
    }

    /** @test */
    public function it_validates_at_least_one_adult(): void
    {
        $component = Livewire::test(DateOccupancySelector::class);
        $component->set('adults', 0);

        $component->call('validateSelection');

        $component->assertHasErrors(['adults' => 'min']);
    }

    /** @test */
    public function it_validates_total_guests_not_exceeding_max_occupancy(): void
    {
        $roomType = RoomType::factory()->create(['max_occupancy' => 2]);

        $component = Livewire::test(DateOccupancySelector::class, [
            'roomType' => $roomType,
            'initialAdults' => 2,
            'initialChildren' => 1,
        ]);

        $component->call('validateSelection');

        $component->assertHasErrors(['adults']);
    }

    // ==================== EVENT EMISSION TESTS ====================

    /** @test */
    public function it_emits_date_occupancy_selected_event(): void
    {
        $stayType = StayType::factory()->create(['nights' => 5]);
        $roomType = RoomType::factory()->create(['max_occupancy' => 4]);

        $component = Livewire::test(DateOccupancySelector::class, [
            'stayType' => $stayType,
            'roomType' => $roomType,
            'initialAdults' => 2,
            'initialChildren' => 1,
            'initialInfants' => 1,
        ]);

        $component->dispatch('dateOccupancySelected');

        $component->assertEmitted('dateOccupancySelected', [
            'checkin_date' => $component->get('checkinDate'),
            'nights' => 5,
            'checkout_date' => $component->get('checkoutDate'),
            'adults' => 2,
            'children' => 1,
            'infants' => 1,
            'extra_beds' => 0,
            'total_guests' => 3,
            'is_valid' => true,
        ]);
    }

    /** @test */
    public function it_emits_event_after_updating_values(): void
    {
        $component = Livewire::test(DateOccupancySelector::class);

        $component->set('adults', 3);
        $component->set('children', 2);
        $component->set('checkinDate', '2026-03-01');

        $component->dispatch('dateOccupancySelected');

        $component->assertEmitted('dateOccupancySelected', [
            'checkin_date' => '2026-03-01',
            'nights' => 1,
            'checkout_date' => '2026-03-02',
            'adults' => 3,
            'children' => 2,
            'infants' => 0,
            'extra_beds' => 0,
            'total_guests' => 5,
            'is_valid' => true,
        ]);
    }

    /** @test */
    public function it_sets_is_valid_to_false_when_exceeds_occupancy(): void
    {
        $roomType = RoomType::factory()->create(['max_occupancy' => 2]);

        $component = Livewire::test(DateOccupancySelector::class, [
            'roomType' => $roomType,
            'initialAdults' => 2,
            'initialChildren' => 1,
        ]);

        $component->dispatch('dateOccupancySelected');

        $component->assertEmitted('dateOccupancySelected', [
            'total_guests' => 3,
            'is_valid' => false,
        ]);
    }
}
