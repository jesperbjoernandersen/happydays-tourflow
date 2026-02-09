<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookingGuest extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id',
        'name',
        'birthdate',
        'guest_category',
    ];

    protected $casts = [
        'birthdate' => 'date',
        'guest_category' => 'string',
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * Calculate age at check-in date.
     */
    public function getAgeAtCheckIn(): ?int
    {
        if (!$this->birthdate || !$this->booking) {
            return null;
        }
        return $this->birthdate->diffInYears($this->booking->check_in_date);
    }
}
