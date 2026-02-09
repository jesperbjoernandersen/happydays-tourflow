<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StayType extends Model
{
    use HasFactory;

    protected $fillable = [
        'hotel_id',
        'hotel_age_policy_id',
        'name',
        'description',
        'code',
        'nights',
        'included_board_type',
        'is_active',
    ];

    protected $casts = [
        'nights' => 'integer',
        'is_active' => 'boolean',
    ];

    /**
     * Get the hotel that owns this stay type.
     */
    public function hotel()
    {
        return $this->belongsTo(Hotel::class);
    }

    /**
     * Get the age policy associated with this stay type.
     */
    public function agePolicy()
    {
        return $this->belongsTo(HotelAgePolicy::class, 'hotel_age_policy_id');
    }

    /**
     * Get all rate rules for this stay type.
     */
    public function rateRules()
    {
        return $this->hasMany(RateRule::class);
    }

    /**
     * Get all bookings for this stay type.
     */
    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    /**
     * Get the board type name (accessor for included_board_type).
     */
    public function getBoardTypeAttribute(): ?string
    {
        return $this->attributes['included_board_type'] ?? null;
    }

    /**
     * Get the board type name in a human-readable format.
     */
    public function getBoardTypeNameAttribute(): string
    {
        $boardTypes = [
            'AI' => 'All Inclusive',
            'FB' => 'Full Board',
            'HB' => 'Half Board',
            'BB' => 'Bed & Breakfast',
            'RO' => 'Room Only',
        ];

        return $boardTypes[$this->board_type] ?? $this->board_type ?? 'Unknown';
    }

    /**
     * Scope a query to only include active stay types.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to filter by number of nights.
     */
    public function scopeWithNights($query, int $nights)
    {
        return $query->where('nights', $nights);
    }

    /**
     * Scope a query to filter by board type.
     */
    public function scopeWithBoardType($query, string $boardType)
    {
        return $query->where('included_board_type', $boardType);
    }
}
