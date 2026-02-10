<?php

namespace Tests\Unit\Livewire;

use App\Livewire\GuestInformation;
use App\Models\HotelAgePolicy;
use Carbon\Carbon;
use Database\Factories\HotelAgePolicyFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class GuestInformationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_renders_successfully(): void
    {
        Livewire::test(GuestInformation::class)
            ->assertStatus(200);
    }

    /** @test */
    public function it_initializes_with_default_values(): void
    {
        $component = Livewire::test(GuestInformation::class);

        $component->assertSet('adults', 1);
        $component->assertSet('children', 0);
        $component->assertSet('infants', 0);
        $component->assertSet('totalGuests', 1);
        $component->assertCount('guests', 1);
    }

    /** @test */
    public function it_mounts_with_provided_values(): void
    {
        $hotelAgePolicy = HotelAgePolicy::create([
            'hotel_id' => 1,
            'name' => 'Standard Policy',
            'infant_max_age' => 2,
            'child_max_age' => 12,
            'adult_min_age' => 18,
        ]);

        Livewire::test(GuestInformation::class, [
            'hotelAgePolicy' => $hotelAgePolicy,
            'checkinDate' => '2026-03-15',
            'adults' => 2,
            'children' => 1,
            'infants' => 1,
        ])
            ->assertSet('adults', 2)
            ->assertSet('children', 1)
            ->assertSet('infants', 1)
            ->assertSet('checkinDate', '2026-03-15')
            ->assertSet('hotelAgePolicy.id', $hotelAgePolicy->id);
    }

    /** @test */
    public function it_initializes_correct_number_of_guests(): void
    {
        $component = Livewire::test(GuestInformation::class, [
            'adults' => 2,
            'children' => 1,
            'infants' => 1,
        ]);

        $component->assertSet('totalGuests', 4);
        $component->assertCount('guests', 4);
    }

    /** @test */
    public function it_sets_birthdate_constraints_correctly(): void
    {
        $component = Livewire::test(GuestInformation::class);

        $component->assertTrue($component->get('maxBirthdate')->isSameDay(Carbon::today()));
        $component->assertTrue($component->get('minBirthdate')->isSameDay(Carbon::today()->subYears(150)));
    }

    /** @test */
    public function it_calculates_age_correctly(): void
    {
        $component = Livewire::test(GuestInformation::class, [
            'checkinDate' => '2026-03-15',
        ]);

        // Set birthdate for someone who will be 30 at check-in
        $component->set('guests.0.birthdate', '1995-06-01');
        $component->call('updatedGuestsBirthdate', '1995-06-1', 0);

        $component->assertSet('guests.0.age', 30);
    }

    /** @test */
    public function it_classifies_adult_guest_correctly(): void
    {
        $hotelAgePolicy = HotelAgePolicy::create([
            'hotel_id' => 1,
            'name' => 'Standard Policy',
            'infant_max_age' => 2,
            'child_max_age' => 12,
            'adult_min_age' => 18,
        ]);

        $component = Livewire::test(GuestInformation::class, [
            'hotelAgePolicy' => $hotelAgePolicy,
            'checkinDate' => '2026-03-15',
        ]);

        // Set birthdate for an adult (30 years old)
        $component->set('guests.0.birthdate', '1995-06-01');
        $component->call('updatedGuestsBirthdate', '1995-06-01', 0);

        $component->assertSet('guests.0.category', 'ADULT');
    }

    /** @test */
    public function it_classifies_child_guest_correctly(): void
    {
        $hotelAgePolicy = HotelAgePolicy::create([
            'hotel_id' => 1,
            'name' => 'Standard Policy',
            'infant_max_age' => 2,
            'child_max_age' => 12,
            'adult_min_age' => 18,
        ]);

        $component = Livewire::test(GuestInformation::class, [
            'hotelAgePolicy' => $hotelAgePolicy,
            'checkinDate' => '2026-03-15',
        ]);

        // Set birthdate for a child (10 years old)
        $component->set('guests.0.birthdate', '2015-06-01');
        $component->call('updatedGuestsBirthdate', '2015-06-01', 0);

        $component->assertSet('guests.0.category', 'CHILD');
    }

    /** @test */
    public function it_classifies_infant_guest_correctly(): void
    {
        $hotelAgePolicy = HotelAgePolicy::create([
            'hotel_id' => 1,
            'name' => 'Standard Policy',
            'infant_max_age' => 2,
            'child_max_age' => 12,
            'adult_min_age' => 18,
        ]);

        $component = Livewire::test(GuestInformation::class, [
            'hotelAgePolicy' => $hotelAgePolicy,
            'checkinDate' => '2026-03-15',
        ]);

        // Set birthdate for an infant (1 year old)
        $component->set('guests.0.birthdate', '2025-06-01');
        $component->call('updatedGuestsBirthdate', '2025-06-01', 0);

        $component->assertSet('guests.0.category', 'INFANT');
    }

    /** @test */
    public function it_updates_guest_count_when_adults_change(): void
    {
        $component = Livewire::test(GuestInformation::class, [
            'adults' => 2,
        ]);

        $component->assertCount('guests', 2);

        $component->set('adults', 3);
        $component->assertCount('guests', 3);
    }

    /** @test */
    public function it_updates_guest_count_when_children_change(): void
    {
        $component = Livewire::test(GuestInformation::class, [
            'adults' => 1,
            'children' => 2,
        ]);

        $component->assertCount('guests', 3);

        $component->set('children', 3);
        $component->assertCount('guests', 4);
    }

    /** @test */
    public function it_validates_guest_name_is_required(): void
    {
        $component = Livewire::test(GuestInformation::class);

        $component->set('guests.0.name', '');
        $component->call('submitGuests');

        $component->assertHasErrors(['guests.0.name' => 'required']);
    }

    /** @test */
    public function it_validates_birthdate_is_required(): void
    {
        $component = Livewire::test(GuestInformation::class);

        $component->set('guests.0.birthdate', '');
        $component->call('submitGuests');

        $component->assertHasErrors(['guests.0.birthdate' => 'required']);
    }

    /** @test */
    public function it_validates_birthdate_is_not_in_future(): void
    {
        $component = Livewire::test(GuestInformation::class);

        $component->set('guests.0.birthdate', Carbon::tomorrow()->format('Y-m-d'));
        $component->call('submitGuests');

        $component->assertHasErrors(['guests.0.birthdate' => 'before_or_equal']);
    }

    /** @test */
    public function it_validates_birthdate_is_not_too_far_in_past(): void
    {
        $component = Livewire::test(GuestInformation::class);

        $component->set('guests.0.birthdate', Carbon::today()->subYears(151)->format('Y-m-d'));
        $component->call('submitGuests');

        $component->assertHasErrors(['guests.0.birthdate' => 'after_or_equal']);
    }

    // ==================== EVENT EMISSION TESTS ====================

    /** @test */
    public function it_emits_guests_collected_event_with_valid_data(): void
    {
        $hotelAgePolicy = HotelAgePolicy::create([
            'hotel_id' => 1,
            'name' => 'Standard Policy',
            'infant_max_age' => 2,
            'child_max_age' => 12,
            'adult_min_age' => 18,
        ]);

        $component = Livewire::test(GuestInformation::class, [
            'hotelAgePolicy' => $hotelAgePolicy,
            'checkinDate' => '2026-03-15',
            'adults' => 2,
            'children' => 1,
        ]);

        // Fill in guest data
        $component->set('guests.0.name', 'John Doe');
        $component->set('guests.0.birthdate', '1990-05-15');
        $component->set('guests.1.name', 'Jane Smith');
        $component->set('guests.1.birthdate', '1992-08-20');
        $component->set('guests.2.name', 'Tommy Jones');
        $component->set('guests.2.birthdate', '2015-03-10');

        $component->call('submitGuests');

        $component->assertEmitted('guestsCollected', [
            [
                'name' => 'John Doe',
                'birthdate' => '1990-05-15',
                'age' => 35,
                'category' => 'ADULT',
            ],
            [
                'name' => 'Jane Smith',
                'birthdate' => '1992-08-20',
                'age' => 33,
                'category' => 'ADULT',
            ],
            [
                'name' => 'Tommy Jones',
                'birthdate' => '2015-03-10',
                'age' => 11,
                'category' => 'CHILD',
            ],
        ]);
    }

    /** @test */
    public function it_emits_validation_failed_event_when_validation_fails(): void
    {
        $component = Livewire::test(GuestInformation::class);

        // Don't fill in any data
        $component->call('submitGuests');

        $component->assertEmitted('validationFailed');
    }

    /** @test */
    public function it_preserves_guest_data_when_occupancy_changes(): void
    {
        $component = Livewire::test(GuestInformation::class, [
            'adults' => 2,
        ]);

        // Fill in first guest
        $component->set('guests.0.name', 'John Doe');
        $component->set('guests.0.birthdate', '1990-05-15');

        // Add another adult
        $component->set('adults', 3);

        // Verify first guest data is preserved
        $component->assertSet('guests.0.name', 'John Doe');
        $component->assertSet('guests.0.birthdate', '1990-05-15');
    }

    /** @test */
    public function it_handles_empty_hotel_age_policy_gracefully(): void
    {
        $component = Livewire::test(GuestInformation::class, [
            'checkinDate' => '2026-03-15',
        ]);

        // No hotel age policy, should still work with fallback
        $component->set('guests.0.birthdate', '1990-05-15');
        $component->call('updatedGuestsBirthdate', '1990-05-15', 0);

        // Should classify as ADULT based on fallback logic
        $component->assertSet('guests.0.category', 'ADULT');
    }

    /** @test */
    public function it_calculates_age_at_checkin_date_not_today(): void
    {
        $component = Livewire::test(GuestInformation::class, [
            'checkinDate' => '2026-03-15',
        ]);

        // Someone born June 1, 2000
        // On March 15, 2026, they would be 25
        // On June 1, 2026, they would turn 26
        $component->set('guests.0.birthdate', '2000-06-01');
        $component->call('updatedGuestsBirthdate', '2000-06-01', 0);

        $component->assertSet('guests.0.age', 25);
        $component->assertSet('guests.0.category', 'ADULT');
    }

    /** @test */
    public function it_handles_infant_birthday_not_yet_reached(): void
    {
        $component = Livewire::test(GuestInformation::class, [
            'checkinDate' => '2026-03-15',
        ]);

        // Infant born August 1, 2025 - not yet 1 year old on March 15, 2026
        $component->set('guests.0.birthdate', '2025-08-01');
        $component->call('updatedGuestsBirthdate', '2025-08-01', 0);

        $component->assertSet('guests.0.age', 0);
    }

    /** @test */
    public function it_emits_correct_data_structure(): void
    {
        $component = Livewire::test(GuestInformation::class, [
            'checkinDate' => '2026-03-15',
            'adults' => 1,
        ]);

        $component->set('guests.0.name', 'John Doe');
        $component->set('guests.0.birthdate', '1990-05-15');

        $component->call('submitGuests');

        $component->assertEmitted('guestsCollected', function ($data) {
            // Verify structure
            if (count($data) !== 1) {
                return false;
            }

            $guest = $data[0];
            return isset($guest['name'], $guest['birthdate'], $guest['age'], $guest['category'])
                && $guest['name'] === 'John Doe'
                && $guest['birthdate'] === '1990-05-15'
                && $guest['age'] === 35
                && $guest['category'] === 'ADULT';
        });
    }
}
