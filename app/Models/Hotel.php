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

    public function agePolicies()
    {
        return $this->hasMany(HotelAgePolicy::class);
    }

    public function stayTypes()
    {
        return $this->hasMany(StayType::class);
    }

    public function roomTypes()
    {
        return $this->hasMany(RoomType::class);
    }

    public function ratePlans()
    {
        return $this->hasMany(RatePlan::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }
}
