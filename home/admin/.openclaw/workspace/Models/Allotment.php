<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Allotment extends Model
{
    use HasFactory;

    protected $fillable = [
        'room_type_id',
        'date',
        'quantity',
        'allocated',
        'price_override',
        'cta',
        'ctd',
        'min_stay',
        'max_stay',
        'release_days',
        'stop_sell',
    ];

    protected $casts = [
        'date' => 'date',
        'quantity' => 'integer',
        'allocated' => 'integer',
        'price_override' => 'decimal:2',
        'cta' => 'boolean',
        'ctd' => 'boolean',
        'min_stay' => 'integer',
        'max_stay' => 'integer',
        'release_days' => 'integer',
        'stop_sell' => 'boolean',
    ];

    /**
     * Get the room type that owns this allotment.
     */
    public function roomType()
    {
        return $this->belongsTo(RoomType::class);
    }

    /**
     * Get the remaining available quantity.
     */
    public function getRemainingAttribute(): int
    {
        return max(0, $this->quantity - $this->allocated);
    }

    /**
     * Check if this allotment is available.
     */
    public function getIsAvailableAttribute(): bool
    {
        return !$this->stop_sell && $this->remaining > 0;
    }

    /**
     * Scope a query to only include allotments for a specific date.
     */
    public function scopeForDate($query, Carbon $date)
    {
        return $query->whereDate('date', $date);
    }

    /**
     * Scope a query to only include allotments for a date range.
     */
    public function scopeForDateRange($query, Carbon $startDate, Carbon $endDate)
    {
        return $query->whereDate('date', '>=', $startDate)
                    ->whereDate('date', '<=', $endDate);
    }

    /**
     * Scope a query to only include available allotments.
     */
    public function scopeAvailable($query)
    {
        return $query->where('stop_sell', false)
                    ->whereRaw('quantity > allocated');
    }

    /**
     * Scope a query to only include unavailable allotments.
     */
    public function scopeUnavailable($query)
    {
        return $query->where('stop_sell', true)
                    ->orWhereRaw('quantity <= allocated');
    }

    /**
     * Scope a query to only include CT (close to) allotments.
     */
    public function scopeCloseTo($query)
    {
        return $query->where('cta', true)->orWhere('ctd', true);
    }

    /**
     * Scope a query to only include open for sale allotments.
     */
    public function scopeOpenForSale($query)
    {
        return $query->where('stop_sell', false);
    }

    /**
     * Scope a query to only include future allotments.
     */
    public function scopeFuture($query)
    {
        return $query->whereDate('date', '>', now()->endOfDay());
    }

    /**
     * Scope a query to only include current and future allotments.
     */
    public function scopeCurrentAndFuture($query)
    {
        return $query->whereDate('date', '>=', now()->startOfDay());
    }

    /**
     * Check if minimum stay requirement is met.
     */
    public function meetsMinStay(int $nights): bool
    {
        return !$this->min_stay || $nights >= $this->min_stay;
    }

    /**
     * Check if maximum stay requirement is met.
     */
    public function meetsMaxStay(int $nights): bool
    {
        return !$this->max_stay || $nights <= $this->max_stay;
    }

    /**
     * Check if the release days requirement is met.
     */
    public function withinReleaseDays(): bool
    {
        if (!$this->release_days) {
            return true;
        }

        return now()->diffInDays($this->date) >= $this->release_days;
    }

    /**
     * Get the effective price (price override or null).
     */
    public function getEffectivePriceAttribute()
    {
        return $this->price_override;
    }
}
