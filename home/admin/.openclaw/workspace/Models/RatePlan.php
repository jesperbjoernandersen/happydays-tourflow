<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RatePlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'hotel_id',
        'name',
        'code',
        'description',
        'pricing_model',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the hotel that owns this rate plan.
     */
    public function hotel()
    {
        return $this->belongsTo(Hotel::class);
    }

    /**
     * Get all rate rules for this rate plan.
     */
    public function rateRules()
    {
        return $this->hasMany(RateRule::class);
    }

    /**
     * Scope a query to only include active rate plans.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get the pricing model label.
     */
    public function getPricingModelLabelAttribute(): string
    {
        $models = [
            'per_person' => 'Per Person',
            'per_room' => 'Per Room',
            'mixed' => 'Mixed',
        ];

        return $models[$this->pricing_model] ?? $this->pricing_model ?? 'Unknown';
    }

    /**
     * Check if this is a per-person pricing model.
     */
    public function isPerPerson(): bool
    {
        return $this->pricing_model === 'per_person';
    }

    /**
     * Check if this is a per-room pricing model.
     */
    public function isPerRoom(): bool
    {
        return $this->pricing_model === 'per_room';
    }
}
