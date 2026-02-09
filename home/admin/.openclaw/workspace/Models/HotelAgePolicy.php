<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HotelAgePolicy extends Model
{
    use HasFactory;

    protected $fillable = [
        'hotel_id',
        'name',
        'infant_max_age',
        'child_max_age',
        'adult_min_age',
    ];

    protected $casts = [
        'infant_max_age' => 'integer|null',
        'child_max_age' => 'integer|null',
        'adult_min_age' => 'integer|null',
    ];

    /**
     * Get the hotel that owns this age policy.
     */
    public function hotel()
    {
        return $this->belongsTo(Hotel::class);
    }

    /**
     * Get all stay types that use this age policy.
     */
    public function stayTypes()
    {
        return $this->hasMany(StayType::class);
    }

    /**
     * Determine if a guest category is valid based on age.
     *
     * @param int $age
     * @return string
     */
    public function getGuestCategoryForAge(int $age): string
    {
        if ($age <= $this->infant_max_age) {
            return 'infant';
        } elseif ($age <= $this->child_max_age) {
            return 'child';
        } else {
            return 'adult';
        }
    }

    /**
     * Get the maximum age for infants.
     */
    public function getMaxInfantAge(): int
    {
        return $this->infant_max_age;
    }

    /**
     * Get the maximum age for children.
     */
    public function getMaxChildAge(): int
    {
        return $this->child_max_age;
    }

    /**
     * Get the minimum age for adults.
     */
    public function getMinAdultAge(): int
    {
        return $this->adult_min_age;
    }
}
