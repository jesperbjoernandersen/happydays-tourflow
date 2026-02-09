<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RateRule extends Model
{
    use HasFactory;

    protected $fillable = [
        'rate_plan_id',
        'stay_type_id',
        'room_type_id',
        'start_date',
        'end_date',
        'base_price',
        'price_per_adult',
        'price_per_child',
        'price_per_infant',
        'price_per_extra_bed',
        'single_use_supplement',
        'included_occupancy',
        'price_per_extra_person',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'base_price' => 'decimal:2',
        'price_per_adult' => 'decimal:2',
        'price_per_child' => 'decimal:2',
        'price_per_infant' => 'decimal:2',
        'price_per_extra_bed' => 'decimal:2',
        'single_use_supplement' => 'decimal:2',
        'included_occupancy' => 'integer',
        'price_per_extra_person' => 'decimal:2',
    ];

    /**
     * Get the rate plan that owns this rule.
     */
    public function ratePlan()
    {
        return $this->belongsTo(RatePlan::class);
    }

    /**
     * Get the stay type associated with this rule (nullable).
     */
    public function stayType()
    {
        return $this->belongsTo(StayType::class);
    }

    /**
     * Get the room type associated with this rule (nullable).
     */
    public function roomType()
    {
        return $this->belongsTo(RoomType::class);
    }

    /**
     * Check if this rate rule is currently valid based on dates.
     */
    public function isCurrentlyValid(): bool
    {
        $today = now()->startOfDay();
        return $this->start_date->lte($today) && $this->end_date->gte($today);
    }

    /**
     * Scope a query to only include currently valid rate rules.
     */
    public function scopeCurrentlyValid($query)
    {
        return $query->whereDate('start_date', '<=', now())
                    ->whereDate('end_date', '>=', now());
    }

    /**
     * Scope a query to filter by date range.
     */
    public function scopeForDate($query, $date)
    {
        return $query->whereDate('start_date', '<=', $date)
                    ->whereDate('end_date', '>=', $date);
    }

    /**
     * Calculate the total price for given occupancy.
     *
     * @param int $adults
     * @param int $children
     * @param int $infants
     * @param int $extraBeds
     * @param bool $isSingleUse
     * @return float
     */
    public function calculatePrice(
        int $adults = 0,
        int $children = 0,
        int $infants = 0,
        int $extraBeds = 0,
        bool $isSingleUse = false
    ): float {
        $total = $this->base_price;

        // Add per-person prices for guests beyond included occupancy
        $guestsBeyondIncluded = max(0, ($adults + $children) - $this->included_occupancy);

        if ($guestsBeyondIncluded > 0) {
            $total += $guestsBeyondIncluded * $this->price_per_extra_person;
        }

        // Add per-adult price (if not already covered by base/included)
        if ($adults > 0 && $this->included_occupancy > 0 && ($adults + $children) <= $this->included_occupancy) {
            // All guests are within included occupancy
        } elseif ($adults > 0) {
            $adultsBeyond = max(0, $adults - ($this->included_occupancy - $children));
            $total += $adultsBeyond * $this->price_per_adult;
        }

        // Add per-child price
        if ($children > 0) {
            $total += $children * $this->price_per_child;
        }

        // Add per-infant price
        if ($infants > 0) {
            $total += $infants * $this->price_per_infant;
        }

        // Add extra bed prices
        if ($extraBeds > 0) {
            $total += $extraBeds * $this->price_per_extra_bed;
        }

        // Add single use supplement if applicable
        if ($isSingleUse) {
            $total += $this->single_use_supplement;
        }

        return round($total, 2);
    }
}
