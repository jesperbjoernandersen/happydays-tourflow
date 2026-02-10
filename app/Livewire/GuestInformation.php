<?php

namespace App\Livewire;

use App\Models\HotelAgePolicy;
use App\Services\AgeClassificationService;
use Carbon\Carbon;
use InvalidArgumentException;
use Livewire\Component;

/**
 * GuestInformation
 *
 * A Livewire component for collecting guest information including birthdates.
 * Dynamically generates guest fields based on occupancy and classifies guests
 * by age category using AgeClassificationService.
 */
class GuestInformation extends Component
{
    /**
     * The hotel age policy for age classification.
     */
    public ?HotelAgePolicy $hotelAgePolicy = null;

    /**
     * Selected check-in date (used for age calculation).
     */
    public string $checkinDate = '';

    /**
     * Number of adults from occupancy.
     */
    public int $adults = 1;

    /**
     * Number of children from occupancy.
     */
    public int $children = 0;

    /**
     * Number of infants from occupancy.
     */
    public int $infants = 0;

    /**
     * Guest information array.
     * Each guest: ['name' => '', 'birthdate' => '', 'age' => null, 'category' => null]
     */
    public array $guests = [];

    /**
     * Maximum date for birthdate (today).
     */
    public Carbon $maxBirthdate;

    /**
     * Minimum date for birthdate (150 years ago).
     */
    public Carbon $minBirthdate;

    /**
     * Validation error messages.
     */
    protected $validationAttributes = [
        'guests.*.name' => 'guest name',
        'guests.*.birthdate' => 'guest birthdate',
    ];

    /**
     * Validation rules.
     */
    public function rules(): array
    {
        return [
            'checkinDate' => ['required', 'date'],
            'adults' => ['required', 'integer', 'min:1'],
            'children' => ['required', 'integer', 'min:0'],
            'infants' => ['required', 'integer', 'min:0'],
            'guests' => ['required', 'array', 'min:1'],
            'guests.*.name' => ['required', 'string', 'max:255'],
            'guests.*.birthdate' => ['required', 'date', 'before_or_equal:today', 'after_or_equal:minBirthdate'],
        ];
    }

    /**
     * Custom validation messages.
     */
    protected function messages(): array
    {
        return [
            'guests.*.name.required' => 'Each guest must have a name.',
            'guests.*.birthdate.required' => 'Each guest must have a birthdate.',
            'guests.*.birthdate.before_or_equal' => 'Birthdate cannot be in the future.',
            'guests.*.birthdate.after_or_equal' => 'Birthdate is too far in the past (max 150 years ago).',
            'adults.min' => 'At least 1 adult is required.',
        ];
    }

    /**
     * Mount the component with initial data.
     */
    public function mount(
        ?HotelAgePolicy $hotelAgePolicy = null,
        string $checkinDate = '',
        int $adults = 1,
        int $children = 0,
        int $infants = 0
    ): void {
        $this->hotelAgePolicy = $hotelAgePolicy;
        $this->checkinDate = $checkinDate;
        $this->adults = max(1, $adults);
        $this->children = max(0, $children);
        $this->infants = max(0, $infants);

        // Set birthdate constraints
        $this->maxBirthdate = Carbon::today();
        $this->minBirthdate = Carbon::today()->subYears(150);

        // Initialize guest fields based on occupancy
        $this->initializeGuests();
    }

    /**
     * Initialize guest fields based on occupancy.
     */
    protected function initializeGuests(): void
    {
        $totalGuests = $this->totalGuests;
        
        // Ensure we have enough guest fields
        while (count($this->guests) < $totalGuests) {
            $this->guests[] = [
                'name' => '',
                'birthdate' => '',
                'age' => null,
                'category' => null,
            ];
        }

        // Remove extra fields if occupancy decreased
        while (count($this->guests) > $totalGuests) {
            array_pop($this->guests);
        }

        // Reset indices to maintain array structure
        $this->guests = array_values($this->guests);
    }

    /**
     * Updated occupancy handler - reinitialize guests.
     */
    public function updatedAdults(): void
    {
        $this->initializeGuests();
    }

    public function updatedChildren(): void
    {
        $this->initializeGuests();
    }

    public function updatedInfants(): void
    {
        $this->initializeGuests();
    }

    /**
     * Updated guest birthdate handler - calculate age and category.
     */
    public function updatedGuestsBirthdate(string $value, int $index): void
    {
        $this->calculateGuestAgeAndCategory($index);
    }

    /**
     * Calculate age and category for a specific guest.
     */
    protected function calculateGuestAgeAndCategory(int $index): void
    {
        if (!isset($this->guests[$index])) {
            return;
        }

        $birthdate = $this->guests[$index]['birthdate'];
        
        if (empty($birthdate) || empty($this->checkinDate)) {
            $this->guests[$index]['age'] = null;
            $this->guests[$index]['category'] = null;
            return;
        }

        try {
            $birthdateCarbon = Carbon::parse($birthdate);
            $checkinDateCarbon = Carbon::parse($this->checkinDate);

            // Validate birthdate is not in the future relative to check-in
            if ($birthdateCarbon->gt($checkinDateCarbon)) {
                return; // Will be caught by validation
            }

            $age = $this->calculateAge($birthdateCarbon, $checkinDateCarbon);
            $category = $this->classifyGuestAge($age);

            $this->guests[$index]['age'] = $age;
            $this->guests[$index]['category'] = $category;
        } catch (\Exception $e) {
            // Invalid date format, will be caught by validation
            $this->guests[$index]['age'] = null;
            $this->guests[$index]['category'] = null;
        }
    }

    /**
     * Calculate age at check-in date.
     */
    protected function calculateAge(Carbon $birthdate, Carbon $checkinDate): int
    {
        $age = $checkinDate->year - $birthdate->year;

        // Adjust if birthday hasn't occurred yet this year
        if ($checkinDate->month < $birthdate->month || 
            ($checkinDate->month === $birthdate->month && $checkinDate->day < $birthdate->day)) {
            $age--;
        }

        return max(0, $age);
    }

    /**
     * Classify guest age using the service or fallback.
     */
    protected function classifyGuestAge(int $age): string
    {
        if ($this->hotelAgePolicy) {
            $service = app(AgeClassificationService::class);
            $category = $service->classify($age, $this->checkinDate, $this->hotelAgePolicy);
            return (string) $category;
        }

        // Fallback classification without policy
        if ($age < 2) {
            return 'INFANT';
        } elseif ($age < 18) {
            return 'CHILD';
        }
        return 'ADULT';
    }

    /**
     * Get total guests (adults + children + infants).
     */
    public function getTotalGuestsProperty(): int
    {
        return $this->adults + $this->children + $this->infants;
    }

    /**
     * Validate all guest data.
     */
    public function validateGuests(): bool
    {
        // First validate basic rules
        $validated = $this->validate();

        // Check that at least one adult exists
        $adultCount = 0;
        foreach ($this->guests as $guest) {
            if (isset($guest['category']) && $guest['category'] === 'ADULT') {
                $adultCount++;
            }
        }

        // If no age classifications yet, assume all adults for validation purposes
        // unless there are explicit child/infant indicators
        if ($adultCount === 0 && !empty($this->checkinDate)) {
            // Try to classify all guests that have birthdates
            $hasValidClassifications = true;
            foreach ($this->guests as $guest) {
                if (empty($guest['birthdate'])) {
                    $hasValidClassifications = false;
                    break;
                }
            }

            if ($hasValidClassifications) {
                $this->addError('adults', 'At least 1 adult is required.');
                return false;
            }
        }

        // Recalculate ages and categories for all guests before emitting
        $this->recalculateAllGuests();

        return true;
    }

    /**
     * Recalculate age and category for all guests.
     */
    protected function recalculateAllGuests(): void
    {
        foreach ($this->guests as $index => &$guest) {
            if (!empty($guest['birthdate']) && !empty($this->checkinDate)) {
                $this->calculateGuestAgeAndCategory($index);
            }
        }
        unset($guest);
    }

    /**
     * Emit guestsCollected event with validated guest data.
     */
    public function submitGuests(): void
    {
        if (!$this->validateGuests()) {
            $this->dispatch('validationFailed', ['errors' => $this->getErrorMessages()]);
            return;
        }

        $guestsData = [];

        foreach ($this->guests as $guest) {
            if (!empty($guest['name']) && !empty($guest['birthdate'])) {
                $guestData = [
                    'name' => $guest['name'],
                    'birthdate' => $guest['birthdate'],
                    'age' => $guest['age'] ?? $this->calculateAgeFromBirthdate($guest['birthdate']),
                    'category' => $guest['category'] ?? $this->classifyGuestByBirthdate($guest['birthdate']),
                ];
                $guestsData[] = $guestData;
            }
        }

        $this->dispatch('guestsCollected', $guestsData);
    }

    /**
     * Calculate age from birthdate string.
     */
    protected function calculateAgeFromBirthdate(string $birthdate): int
    {
        if (empty($this->checkinDate)) {
            return 0;
        }

        try {
            $birthdateCarbon = Carbon::parse($birthdate);
            $checkinDateCarbon = Carbon::parse($this->checkinDate);
            return $this->calculateAge($birthdateCarbon, $checkinDateCarbon);
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Classify guest by birthdate string.
     */
    protected function classifyGuestByBirthdate(string $birthdate): string
    {
        $age = $this->calculateAgeFromBirthdate($birthdate);
        return $this->classifyGuestAge($age);
    }

    /**
     * Get all validation error messages.
     */
    protected function getErrorMessages(): array
    {
        $errors = [];

        foreach ($this->getMessages() as $field => $messages) {
            foreach ($messages as $message) {
                $errors[] = $message;
            }
        }

        return $errors;
    }

    /**
     * Render the component.
     */
    public function render()
    {
        return view('livewire.guest-information');
    }
}
