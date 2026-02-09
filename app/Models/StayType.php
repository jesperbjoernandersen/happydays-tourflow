<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StayType extends Model
{
    use HasFactory;

    protected $fillable = [
        'hotel_id',
        'name',
        'description',
        'code',
        'nights',
        'included_board_type',
        'is_active',
    ];

    protected $casts = [
        'nights' => 'integer',
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
}
