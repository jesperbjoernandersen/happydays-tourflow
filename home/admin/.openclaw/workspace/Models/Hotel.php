<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Hotel extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'code',
        'address',
        'city',
        'country',
        'email',
        'phone',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get all age policies for this hotel.
     */
    public function agePolicies()
    {
        return $this->hasMany(HotelAgePolicy::class);
    }

    /**
     * Get all stay types for this hotel.
     */
    public function stayTypes()
    {
        return $this->hasMany(StayType::class);
    }

    /**
     * Get all room types for this hotel.
     */
    public function roomTypes()
    {
        return $this->hasMany(RoomType::class);
    }

    /**
     * Get all rate plans for this hotel.
     */
    public function ratePlans()
    {
        return $this->hasMany(RatePlan::class);
    }

    /**
     * Get all bookings for this hotel.
     */
    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    /**
     * Get active room types only.
     */
    public function activeRoomTypes()
    {
        return $this->roomTypes()->where('is_active', true);
    }

    /**
     * Get active stay types only.
     */
    public function activeStayTypes()
    {
        return $this->stayTypes()->where('is_active', true);
    }

    /**
     * Get active rate plans only.
     */
    public function activeRatePlans()
    {
        return $this->ratePlans()->where('is_active', true);
    }
}
