<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Booking extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'booking_reference',
        'stay_type_id',
        'room_type_id',
        'hotel_id',
        'check_in_date',
        'check_out_date',
        'total_price',
        'currency',
        'status',
        'hotel_age_policy_snapshot',
        'rate_rule_snapshot',
        'price_breakdown_json',
        'guest_count',
        'notes',
    ];

    protected $casts = [
        'check_in_date' => 'date',
        'check_out_date' => 'date',
        'total_price' => 'decimal:2',
        'hotel_age_policy_snapshot' => 'array',
        'rate_rule_snapshot' => 'array',
        'price_breakdown_json' => 'array',
    ];

    protected $attributes = [
        'currency' => 'EUR',
        'status' => 'pending',
    ];

    public function stayType()
    {
        return $this->belongsTo(StayType::class);
    }

    public function roomType()
    {
        return $this->belongsTo(RoomType::class);
    }

    public function hotel()
    {
        return $this->belongsTo(Hotel::class);
    }

    public function guests()
    {
        return $this->hasMany(BookingGuest::class);
    }

    /**
     * Generate a unique booking reference.
     */
    public function generateBookingReference(): string
    {
        $prefix = 'BK';
        $date = now()->format('Ymd');
        $random = strtoupper(substr(md5(uniqid()), 0, 6));
        return $prefix . $date . $random;
    }

    /**
     * Get total number of guests.
     */
    public function getTotalGuestsAttribute(): int
    {
        return $this->guests()->count();
    }

    /**
     * Get number of nights.
     */
    public function getNightsAttribute(): int
    {
        return $this->check_in_date->diffInDays($this->check_out_date);
    }

    /**
     * Get price per night.
     */
    public function getPricePerNightAttribute(): float
    {
        if ($this->nights > 0) {
            return round($this->total_price / $this->nights, 2);
        }
        return $this->total_price;
    }

    /**
     * Scope for pending bookings.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for confirmed bookings.
     */
    public function scopeConfirmed($query)
    {
        return $query->where('status', 'confirmed');
    }
}
