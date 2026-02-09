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

    /**
     * Get the hotel that owns this room type (nullable for standalone houses).
     */
    public function hotel()
    {
        return $this->belongsTo(Hotel::class);
    }

    /**
     * Get all rate rules for this room type.
     */
    public function rateRules()
    {
        return $this->hasMany(RateRule::class);
    }

    /**
     * Get all bookings for this room type.
     */
    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    /**
     * Get all allotments for this room type.
     */
    public function allotments()
    {
        return $this->hasMany(Allotment::class);
    }

    /**
     * Check if this is a hotel room (vs standalone house).
     */
    public function isHotelRoom(): bool
    {
        return $this->room_type === 'hotel';
    }

    /**
     * Check if this is a standalone house.
     */
    public function isHouse(): bool
    {
        return $this->room_type === 'house';
    }

    /**
     * Get the maximum total occupancy including extra beds.
     */
    public function getMaxTotalOccupancyAttribute(): int
    {
        return $this->max_occupancy + $this->extra_bed_slots;
    }

    /**
     * Scope a query to only include hotel rooms.
     */
    public function scopeHotelRooms($query)
    {
        return $query->where('room_type', 'hotel');
    }

    /**
     * Scope a query to only include standalone houses.
     */
    public function scopeHouses($query)
    {
        return $query->where('room_type', 'house');
    }

    /**
     * Scope a query to only include active room types.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
