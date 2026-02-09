<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Booking extends Model
{
    use HasFactory, SoftDeletes;

    public const STATUS_PENDING = 'pending';
    public const STATUS_CONFIRMED = 'confirmed';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_NO_SHOW = 'no_show';

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
        'status' => self::STATUS_PENDING,
    ];

    /**
     * Get the stay type associated with this booking.
     */
    public function stayType()
    {
        return $this->belongsTo(StayType::class);
    }

    /**
     * Get the room type associated with this booking.
     */
    public function roomType()
    {
        return $this->belongsTo(RoomType::class);
    }

    /**
     * Get the hotel associated with this booking.
     */
    public function hotel()
    {
        return $this->belongsTo(Hotel::class);
    }

    /**
     * Get all guests for this booking.
     */
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
        $reference = $prefix . $date . $random;

        // Ensure uniqueness
        while (self::where('booking_reference', $reference)->exists()) {
            $random = strtoupper(substr(md5(uniqid()), 0, 6));
            $reference = $prefix . $date . $random;
        }

        $this->booking_reference = $reference;
        return $reference;
    }

    /**
     * Get the total number of guests (computed from guests relationship).
     */
    public function getTotalGuestsAttribute(): int
    {
        return $this->guests->count();
    }

    /**
     * Get the number of nights for this booking.
     */
    public function getNightsAttribute(): int
    {
        if (!$this->check_in_date || !$this->check_out_date) {
            return 0;
        }

        return $this->check_in_date->diffInDays($this->check_out_date);
    }

    /**
     * Get the total price attribute (alias for backward compatibility).
     */
    public function getTotalPriceAttribute(): float
    {
        return $this->attributes['total_price'] ?? 0;
    }

    /**
     * Get the price per night.
     */
    public function getPricePerNightAttribute(): float
    {
        $nights = $this->nights;
        if ($nights > 0) {
            return round($this->total_price / $nights, 2);
        }
        return $this->total_price;
    }

    /**
     * Get the number of adult guests.
     */
    public function getAdultCountAttribute(): int
    {
        return $this->guests->where('guest_category', 'adult')->count();
    }

    /**
     * Get the number of child guests.
     */
    public function getChildCountAttribute(): int
    {
        return $this->guests->where('guest_category', 'child')->count();
    }

    /**
     * Get the number of infant guests.
     */
    public function getInfantCountAttribute(): int
    {
        return $this->guests->where('guest_category', 'infant')->count();
    }

    /**
     * Check if the booking is pending.
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Check if the booking is confirmed.
     */
    public function isConfirmed(): bool
    {
        return $this->status === self::STATUS_CONFIRMED;
    }

    /**
     * Check if the booking is cancelled.
     */
    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    /**
     * Check if the booking can be cancelled.
     */
    public function canBeCancelled(): bool
    {
        return in_array($this->status, [self::STATUS_PENDING, self::STATUS_CONFIRMED])
            && $this->check_in_date->isFuture();
    }

    /**
     * Scope a query to only include pending bookings.
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope a query to only include confirmed bookings.
     */
    public function scopeConfirmed($query)
    {
        return $query->where('status', self::STATUS_CONFIRMED);
    }

    /**
     * Scope a query to only include upcoming bookings.
     */
    public function scopeUpcoming($query)
    {
        return $query->where('check_in_date', '>=', now()->startOfDay())
                    ->whereIn('status', [self::STATUS_PENDING, self::STATUS_CONFIRMED]);
    }

    /**
     * Scope a query to only include active bookings.
     */
    public function scopeActive($query)
    {
        return $query->whereIn('status', [self::STATUS_PENDING, self::STATUS_CONFIRMED]);
    }
}
