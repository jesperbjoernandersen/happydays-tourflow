<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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

    public function roomType()
    {
        return $this->belongsTo(RoomType::class);
    }

    /**
     * Get remaining availability.
     */
    public function getRemainingAttribute(): int
    {
        return max(0, $this->quantity - $this->allocated);
    }

    /**
     * Check if available.
     */
    public function getIsAvailableAttribute(): bool
    {
        return !$this->stop_sell && $this->remaining > 0;
    }

    /**
     * Scope for specific date.
     */
    public function scopeForDate($query, $date)
    {
        return $query->whereDate('date', $date);
    }

    /**
     * Scope for available allotments.
     */
    public function scopeAvailable($query)
    {
        return $query->where('stop_sell', false)
            ->whereRaw('quantity > allocated');
    }
}
