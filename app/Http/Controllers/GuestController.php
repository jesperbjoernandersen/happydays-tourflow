<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\BookingGuest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class GuestController extends Controller
{
    /**
     * Display guest search page.
     */
    public function search(Request $request)
    {
        $guests = collect();
        $query = $request->get('q', '');

        if (strlen($query) >= 2) {
            $guests = BookingGuest::where('name', 'like', "%{$query}%")
                ->with(['booking.hotel', 'booking.roomType'])
                ->distinct()
                ->get();
        }

        return view('guests.search', compact('guests', 'query'));
    }

    /**
     * Show guest details and their bookings.
     */
    public function show(BookingGuest $guest)
    {
        $guest->load(['booking.hotel', 'booking.roomType', 'booking.stayType']);
        $allGuestBookings = BookingGuest::where('email', $guest->email)
            ->where('id', '!=', $guest->id)
            ->with('booking')
            ->get();

        return view('guests.show', compact('guest', 'allGuestBookings'));
    }
}
