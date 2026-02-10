<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Hotel;
use App\Services\AvailabilityService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CheckinController extends Controller
{
    /**
     * Display the check-in page.
     */
    public function index(Request $request)
    {
        $bookings = collect();
        $searchDate = Carbon::today();
        
        if ($request->has('booking_reference') && $request->booking_reference) {
            $bookings = Booking::where('booking_reference', $request->booking_reference)
                ->where('status', 'confirmed')
                ->get();
        } else {
            // Show today's arrivals by default
            $bookings = Booking::whereDate('check_in_date', $searchDate)
                ->where('status', 'confirmed')
                ->with(['hotel', 'roomType', 'guests'])
                ->get();
        }

        return view('checkin', compact('bookings'));
    }

    /**
     * Process check-in for a booking.
     */
    public function process(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'booking_id' => 'required|exists:bookings,id',
            'room_number' => 'nullable|string|max:50',
            'notes' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $booking = Booking::findOrFail($request->booking_id);

        if ($booking->status !== 'confirmed') {
            return redirect()->back()
                ->with('error', 'Only confirmed bookings can be checked in.');
        }

        $booking->update([
            'status' => 'checked_in',
            'notes' => $request->notes,
        ]);

        return redirect()->route('checkin.index')
            ->with('success', "Guest checked in successfully. Booking: {$booking->booking_reference}");
    }

    /**
     * Search for a booking by reference.
     */
    public function search(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'booking_reference' => 'required|string',
        ]);

        if ($validator->fails()) {
            return redirect()->route('checkin.index')
                ->withErrors($validator);
        }

        return redirect()->route('checkin.index', ['booking_reference' => $request->booking_reference]);
    }
}
