<?php

namespace App\Livewire;

use App\Models\RoomType;
use App\Models\StayType;
use App\Services\AvailabilityService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Livewire\Component;

/**
 * DateOccupancySelector
 *
 * A Livewire component for selecting check-in date and guest occupancy.
 * Handles date validation, occupancy counters, and availability checking.
 *
 * @property int $totalGuests
 * @property int $nights
 * @property int $maxOccupancy
 * @property int $baseOccupancy
 * @property int $extraBedSlots
 * @property bool $exceedsOccupancy
 * @property string $checkoutDate
 */
class DateOccupancySelector extends Component
{
    /**
     * The stay type for the booking.
     */
    public ?StayType $stayType = null;

    /**
     * The room type for availability checking.
     */
    public ?RoomType $roomType = null;

    /**
     * Selected check-in date.
     */
    public string $checkinDate = '';

    /**
     * Number of adults.
     */
    public int $adults = 1;

    /**
     * Number of children.
     */
    public int $children = 0;

    /**
     * Number of infants.
     */
    public int $infants = 0;

    /**
     * Number of extra beds.
     */
    public int $extraBeds = 0;

    /**
     * Maximum date for booking (default: 1 year from today).
     */
    public Carbon $maxDate;

    /**
     * Maximum adults allowed.
     */
    public int $maxAdults = 10;

    /**
     * Maximum children allowed.
     */
    public int $maxChildren = 10;

    /**
     * Whether extra beds are supported.
     */
    public bool $supportsExtraBeds = false;

    /**
     * Validation error messages.
     */
    protected $validationAttributes = [
        'checkinDate' => 'check-in date',
        'adults' => 'number of adults',
        'children' => 'number of children',
        'extraBeds' => 'number of extra beds',
    ];

    /**
     * Validation rules.
     */
    protected function rules(): array
    {
        return [
            'checkinDate' => [
                'required',
                'date',
                'after_or_equal:today',
                'before_or_equal:maxDate',
            ],
            'adults' => [
                'required',
                'integer',
                'min:1',
                'max:' . $this->maxAdults,
            ],
            'children' => [
                'required',
                'integer',
                'min:0',
                'max:' . $this->maxChildren,
            ],
            'infants' => [
                'required',
                'integer',
                'min:0',
            ],
            'extraBeds' => [
                'required',
                'integer',
                'min:0',
                'max:' . ($this->roomType?->extra_bed_slots ?? 0),
            ],
        ];
    }

    /**
     * Custom validation messages.
     */
    protected function messages(): array
    {
        $maxOccupancy = $this->roomType?->max_occupancy ?? 0;
        $baseOccupancy = $this->roomType?->base_occupancy ?? 2;
        $extraBedSlots = $this->roomType?->extra_bed_slots ?? 0;

        return [
            'adults.min' => 'At least 1 adult is required.',
            'adults.max' => "Maximum :max adults allowed for this room.",
            'children.max' => "Maximum :max children allowed.",
            'extraBeds.max' => "Maximum {$extraBedSlots} extra beds available.",
        ];
    }

    /**
     * Mount the component with initial data.
     */
    public function mount(
        ?StayType $stayType = null,
        ?RoomType $roomType = null,
        ?string $initialCheckinDate = null,
        int $initialAdults = 1,
        int $initialChildren = 0,
        int $initialInfants = 0
    ): void {
        $this->stayType = $stayType;
        $this->roomType = $roomType;
        $this->maxDate = Carbon::today()->addYear();

        // Set initial values
        $this->checkinDate = $initialCheckinDate ?? Carbon::tomorrow()->format('Y-m-d');
        $this->adults = max(1, $initialAdults);
        $this->children = max(0, $initialChildren);
        $this->infants = max(0, $initialInfants);
        $this->extraBeds = 0;

        // Configure limits based on room type
        if ($this->roomType) {
            $this->maxAdults = $this->roomType->max_occupancy ?? 10;
            $this->maxChildren = $this->roomType->max_occupancy ?? 10;
            $this->supportsExtraBeds = ($this->roomType->extra_bed_slots ?? 0) > 0;
        }
    }

    /**
     * Updated checkin date handler.
     */
    public function updatedCheckinDate(string $value): void
    {
        $this->validateOnly('checkinDate');
        $this->emitDateOccupancySelected();
    }

    /**
     * Updated adults handler.
     */
    public function updatedAdults(int $value): void
    {
        $this->validateOnly('adults');
        $this->emitDateOccupancySelected();
    }

    /**
     * Updated children handler.
     */
    public function updatedChildren(int $value): void
    {
        $this->validateOnly('children');
        $this->emitDateOccupancySelected();
    }

    /**
     * Updated infants handler.
     */
    public function updatedInfants(int $value): void
    {
        $this->validateOnly('infants');
        $this->emitDateOccupancySelected();
    }

    /**
     * Updated extra beds handler.
     */
    public function updatedExtraBeds(int $value): void
    {
        $this->validateOnly('extraBeds');
        $this->emitDateOccupancySelected();
    }

    /**
     * Increment adults counter.
     */
    public function incrementAdults(): void
    {
        if ($this->adults < $this->maxAdults) {
            $this->adults++;
        }
        $this->validateOnly('adults');
        $this->emitDateOccupancySelected();
    }

    /**
     * Decrement adults counter.
     */
    public function decrementAdults(): void
    {
        if ($this->adults > 1) {
            $this->adults--;
        }
        $this->validateOnly('adults');
        $this->emitDateOccupancySelected();
    }

    /**
     * Increment children counter.
     */
    public function incrementChildren(): void
    {
        $totalGuests = $this->totalGuests;
        $maxOccupancy = $this->roomType?->max_occupancy ?? PHP_INT_MAX;

        if ($this->children < $this->maxChildren && $totalGuests < $maxOccupancy) {
            $this->children++;
        }
        $this->validateOnly('children');
        $this->emitDateOccupancySelected();
    }

    /**
     * Decrement children counter.
     */
    public function decrementChildren(): void
    {
        if ($this->children > 0) {
            $this->children--;
        }
        $this->validateOnly('children');
        $this->emitDateOccupancySelected();
    }

    /**
     * Increment infants counter.
     */
    public function incrementInfants(): void
    {
        $this->infants++;
        $this->validateOnly('infants');
        $this->emitDateOccupancySelected();
    }

    /**
     * Decrement infants counter.
     */
    public function decrementInfants(): void
    {
        if ($this->infants > 0) {
            $this->infants--;
        }
        $this->validateOnly('infants');
        $this->emitDateOccupancySelected();
    }

    /**
     * Increment extra beds counter.
     */
    public function incrementExtraBeds(): void
    {
        $maxExtraBeds = $this->roomType?->extra_bed_slots ?? 0;
        if ($this->extraBeds < $maxExtraBeds) {
            $this->extraBeds++;
        }
        $this->validateOnly('extraBeds');
        $this->emitDateOccupancySelected();
    }

    /**
     * Decrement extra beds counter.
     */
    public function decrementExtraBeds(): void
    {
        if ($this->extraBeds > 0) {
            $this->extraBeds--;
        }
        $this->validateOnly('extraBeds');
        $this->emitDateOccupancySelected();
    }

    /**
     * Set the check-in date to today.
     */
    public function setToday(): void
    {
        $this->checkinDate = Carbon::today()->format('Y-m-d');
        $this->validateOnly('checkinDate');
        $this->emitDateOccupancySelected();
    }

    /**
     * Set the check-in date to tomorrow.
     */
    public function setTomorrow(): void
    {
        $this->checkinDate = Carbon::tomorrow()->format('Y-m-d');
        $this->validateOnly('checkinDate');
        $this->emitDateOccupancySelected();
    }

    /**
     * Total guests (public accessor for computed property).
     */
    public int $totalGuests = 0;

    /**
     * Hydrate the component.
     */
    public function hydrate(): void
    {
        $this->totalGuests = $this->adults + $this->children;
    }

    /**
     * Get total guests (adults + children).
     */
    public function getTotalGuestsProperty(): int
    {
        return $this->adults + $this->children;
    }

    /**
     * Get number of nights from stay type.
     */
    public function getNightsProperty(): int
    {
        return $this->stayType?->nights ?? 1;
    }

    /**
     * Get the maximum occupancy from room type.
     */
    public function getMaxOccupancyProperty(): int
    {
        return $this->roomType?->max_occupancy ?? 10;
    }

    /**
     * Get the base occupancy from room type.
     */
    public function getBaseOccupancyProperty(): int
    {
        return $this->roomType?->base_occupancy ?? 2;
    }

    /**
     * Get extra bed slots from room type.
     */
    public function getExtraBedSlotsProperty(): int
    {
        return $this->roomType?->extra_bed_slots ?? 0;
    }

    /**
     * Check if total guests exceeds maximum occupancy.
     */
    public function getExceedsOccupancyProperty(): bool
    {
        return $this->totalGuests > $this->maxOccupancy;
    }

    /**
     * Check if date is available using AvailabilityService.
     */
    public function isDateAvailable(Carbon $date): bool
    {
        if (!$this->stayType || !$this->roomType) {
            return true;
        }

        $availabilityService = app(AvailabilityService::class);
        $result = $availabilityService->checkAvailability(
            $this->stayType,
            $this->roomType,
            $date,
            $this->nights
        );

        return $result->isAvailable();
    }

    /**
     * Get available dates for the date picker.
     */
    public function getAvailableDates(): array
    {
        $dates = [];
        $today = Carbon::today();
        $maxDate = $this->maxDate;

        for ($date = $today->copy(); $date->lte($maxDate); $date->addDay()) {
            if ($this->isDateAvailable($date)) {
                $dates[] = $date->format('Y-m-d');
            }

            // Safety limit to prevent infinite loops
            if ($dates >= 365) {
                break;
            }
        }

        return $dates;
    }

    /**
     * Validate the current selection is complete and valid.
     */
    public function validateSelection(): bool
    {
        $validated = $this->validate();

        // Additional check: total guests must not exceed max occupancy
        if ($this->exceedsOccupancy) {
            $this->addError('adults', "Total guests ({$this->totalGuests}) cannot exceed maximum occupancy ({$this->maxOccupancy}).");
            return false;
        }

        return true;
    }

    /**
     * Emit the dateOccupancySelected event with current data.
     */
    public function emitDateOccupancySelected(): void
    {
        $data = [
            'checkin_date' => $this->checkinDate,
            'nights' => $this->nights,
            'checkout_date' => $this->checkoutDate,
            'adults' => $this->adults,
            'children' => $this->children,
            'infants' => $this->infants,
            'extra_beds' => $this->extraBeds,
            'total_guests' => $this->totalGuests,
            'is_valid' => !$this->getError() && !$this->exceedsOccupancy,
        ];

        $this->dispatch('dateOccupancySelected', $data);
    }

    /**
     * Get checkout date based on checkin date and stay type nights.
     */
    public function getCheckoutDateProperty(): string
    {
        if (empty($this->checkinDate)) {
            return '';
        }

        return Carbon::parse($this->checkinDate)
            ->addDays($this->nights)
            ->format('Y-m-d');
    }

    /**
     * Render the component.
     */
    public function render()
    {
        return view('livewire.date-occupancy-selector');
    }
}
