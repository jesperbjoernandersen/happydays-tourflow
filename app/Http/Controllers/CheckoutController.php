<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CheckoutController extends Controller
{
    /**
     * Display the check-out page.
     */
    public function index(Request $request)
    {
        $bookings = collect();
        $searchDate = Carbon::today();
        
        if ($request->has('room_number') && $request->room_number) {
            // Find booking by room number (stored in notes or we could add a field)
            $bookings = Booking::where('status', 'checked_in')
                ->where('check_out_date', '>=', $searchDate)
                ->get()
                ->filter(function($booking) use ($request) {
                    return str_contains($booking->notes ?? '', $request->room_number);
                });
        } else {
            // Show checked-in guests by default
            $bookings = Booking::where('status', 'checked_in')
                ->whereDate('check_out_date', '>=', $searchDate)
                ->with(['hotel', 'roomType', 'guests'])
                ->get();
        }

        return view('checkout', compact('bookings'));
    }

    /**
     * Process check-out for a booking.
     */
    public function process(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'booking_id' => 'required|exists:bookings,id',
            'additional_charges' => 'nullable|numeric|min:0',
            'payment_method' => 'nullable|string|max:50',
            'notes' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $booking = Booking::findOrFail($request->booking_id);

        if ($booking->status !== 'checked_in') {
            return redirect()->back()
                ->with('error', 'Only checked-in guests can be checked out.');
        }

        // Update total price with any additional charges
        $totalPrice = $booking->total_price;
        if ($request->has('additional_charges') && $request->additional_charges > 0) {
            $totalPrice += $request->additional_charges;
            $booking->update([
                'total_price' => $totalPrice,
                'notes' => ($booking->notes ? $booking->notes . '\n' : '') . 'Additional charges: €' . $request->additional_charges,
            ]);
        }

        $booking->update(['status' => 'checked_out']);

        return redirect()->route('checkout.index')
            ->with('success', "Guest checked out successfully. Final total: €{$totalPrice}");
    }

    /**
     * Search for checked-in guest by room number.
     */
    public function search(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'room_number' => 'required|string',
        ]);

        if ($validator->fails()) {
            return redirect()->route('checkout.index')
                ->withErrors($validator);
        }

        return redirect()->route('checkout.index', ['room_number' => $request->room_number]);
    }
}
