<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class BookingGuest extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id',
        'name',
        'birthdate',
        'guest_category',
    ];

    protected $casts = [
        'birthdate' => 'date',
        'guest_category' => 'string',
    ];

    /**
     * Get the booking that owns this guest.
     */
    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * Calculate the guest's age at check-in date.
     *
     * @param \Carbon\Carbon $checkInDate
     * @return int|null
     */
    public function calculateAgeAtCheckIn(Carbon $checkInDate): ?int
    {
        if (!$this->birthdate) {
            return null;
        }

        return $this->birthdate->diffInYears($checkInDate);
    }

    /**
     * Calculate the guest's age at check-in and return it.
     * Convenience method that uses the booking's check_in_date.
     *
     * @return int|null
     */
    public function getAgeAtCheckIn(): ?int
    {
        if (!$this->booking || !$this->booking->check_in_date) {
            return null;
        }

        return $this->calculateAgeAtCheckIn($this->booking->check_in_date);
    }

    /**
     * Determine the guest category based on age and policy.
     *
     * @param \Carbon\Carbon $checkInDate
     * @param \App\Models\HotelAgePolicy $policy
     * @return string
     */
    public function determineCategoryAtCheckIn(Carbon $checkInDate, HotelAgePolicy $policy): string
    {
        $age = $this->calculateAgeAtCheckIn($checkInDate);

        if ($age === null) {
            return 'adult'; // Default if no birthdate
        }

        return $policy->getGuestCategoryForAge($age);
    }

    /**
     * Get the guest's full name.
     */
    public function getFullNameAttribute(): string
    {
        return $this->name;
    }

    /**
     * Get the guest's age in years (current age).
     */
    public function getCurrentAgeAttribute(): ?int
    {
        if (!$this->birthdate) {
            return null;
        }

        return $this->birthdate->age;
    }

    /**
     * Check if this is an adult.
     */
    public function isAdult(): bool
    {
        return $this->guest_category === 'adult';
    }

    /**
     * Check if this is a child.
     */
    public function isChild(): bool
    {
        return $this->guest_category === 'child';
    }

    /**
     * Check if this is an infant.
     */
    public function isInfant(): bool
    {
        return $this->guest_category === 'infant';
    }

    /**
     * Scope a query to only include adults.
     */
    public function scopeAdults($query)
    {
        return $query->where('guest_category', 'adult');
    }

    /**
     * Scope a query to only include children.
     */
    public function scopeChildren($query)
    {
        return $query->where('guest_category', 'child');
    }

    /**
     * Scope a query to only include infants.
     */
    public function scopeInfants($query)
    {
        return $query->where('guest_category', 'infant');
    }
}
