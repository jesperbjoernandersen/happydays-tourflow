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
}
