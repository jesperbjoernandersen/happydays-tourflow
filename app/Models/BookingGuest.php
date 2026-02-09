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
}
