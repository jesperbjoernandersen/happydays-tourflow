<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoomType extends Model
{
    use HasFactory;

    protected $fillable = [
        'hotel_id',
        'name',
        'code',
        'room_type',
        'base_occupancy',
        'max_occupancy',
        'extra_bed_slots',
        'single_use_supplement',
        'is_active',
    ];

    protected $casts = [
        'base_occupancy' => 'integer',
        'max_occupancy' => 'integer',
        'extra_bed_slots' => 'integer',
        'single_use_supplement' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function hotel()
    {
        return $this->belongsTo(Hotel::class);
    }

    public function rateRules()
    {
        return $this->hasMany(RateRule::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function allotments()
    {
        return $this->hasMany(Allotment::class);
    }
}
