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

    public function ratePlan()
    {
        return $this->belongsTo(RatePlan::class);
    }

    public function stayType()
    {
        return $this->belongsTo(StayType::class);
    }

    public function roomType()
    {
        return $this->belongsTo(RoomType::class);
    }

    /**
     * Calculate price based on occupancy.
     */
    public function calculatePrice(
        int $adults = 0,
        int $children = 0,
        int $infants = 0,
        int $extraBeds = 0,
        bool $isSingleUse = false
    ): float {
        $price = 0;

        // Base price
        $price += $this->base_price;

        // Adult pricing
        $price += $adults * $this->price_per_adult;

        // Child pricing
        $price += $children * $this->price_per_child;

        // Infant pricing (usually free)
        $price += $infants * $this->price_per_infant;

        // Extra beds
        $price += $extraBeds * $this->price_per_extra_bed;

        // Single use supplement
        if ($isSingleUse) {
            $price += $this->single_use_supplement;
        }

        return round($price, 2);
    }
}
