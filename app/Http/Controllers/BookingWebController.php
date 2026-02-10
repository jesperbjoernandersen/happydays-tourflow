<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Hotel;
use App\Models\RoomType;
use App\Models\StayType;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Carbon\Carbon;

class BookingWebController extends Controller
{
    public function index(): View
    {
        $hotels = Hotel::where('is_active', true)->get();
        $stayTypes = StayType::where('is_active', true)->with('hotel')->get();

        return view('booking', compact('hotels', 'stayTypes'));
    }

    public function reservations(Request $request): View
    {
        $query = Booking::with(['hotel', 'roomType', 'stayType', 'guests']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('hotel_id')) {
            $query->where('hotel_id', $request->hotel_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('check_in_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('check_in_date', '<=', $request->date_to);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('booking_reference', 'like', "%{$search}%")
                  ->orWhereHas('guests', function ($guestQuery) use ($search) {
                      $guestQuery->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $bookings = $query->latest()->paginate(15);
        $hotels = Hotel::where('is_active', true)->get();

        return view('reservations', compact('bookings', 'hotels'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'hotel_id' => 'required|exists:hotels,id',
            'stay_type_id' => 'required|exists:stay_types,id',
            'room_type_id' => 'nullable|exists:room_types,id',
            'check_in_date' => 'required|date',
            'nights' => 'required|integer|min:1',
            'adults' => 'required|integer|min:1',
            'children_count' => 'nullable|integer|min:0',
            'guest_name' => 'required|string|max:255',
            'guest_email' => 'nullable|email',
            'guest_phone' => 'nullable|string|max:50',
            'notes' => 'nullable|string',
        ]);

        $booking = Booking::create([
            'booking_reference' => 'BK' . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 8)),
            'hotel_id' => $validated['hotel_id'],
            'stay_type_id' => $validated['stay_type_id'],
            'room_type_id' => $validated['room_type_id'] ?? null,
            'check_in_date' => $validated['check_in_date'],
            'check_out_date' => Carbon::parse($validated['check_in_date'])->addDays($validated['nights']),
            'nights' => $validated['nights'],
            'status' => 'pending',
            'guest_count' => $validated['adults'] + ($validated['children_count'] ?? 0),
            'notes' => $validated['notes'] ?? null,
        ]);

        \App\Models\BookingGuest::create([
            'booking_id' => $booking->id,
            'name' => $validated['guest_name'],
            'email' => $validated['guest_email'] ?? null,
            'phone' => $validated['guest_phone'] ?? null,
            'guest_category' => 'adult',
        ]);

        return redirect()->route('reservations')
            ->with('success', "Booking {$booking->booking_reference} created successfully.");
    }

    public function show(Booking $booking): View
    {
        $booking->load(['hotel', 'roomType', 'stayType', 'guests']);
        return view('booking.show', compact('booking'));
    }

    public function updateStatus(Request $request, Booking $booking): RedirectResponse
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,confirmed,checked_in,completed,cancelled',
        ]);

        $booking->update(['status' => $validated['status']]);
        return redirect()->route('reservations')
            ->with('success', "Booking {$booking->booking_reference} status updated.");
    }

    public function cancel(Request $request, Booking $booking): RedirectResponse
    {
        if (in_array($booking->status, ['cancelled', 'completed'])) {
            return redirect()->back()->with('error', 'Cannot cancel this booking.');
        }

        $booking->update([
            'status' => 'cancelled',
            'notes' => $booking->notes . "\nCancelled: " . ($request->reason ?? 'No reason'),
        ]);

        return redirect()->route('reservations')
            ->with('success', "Booking {$booking->booking_reference} has been cancelled.");
    }
}
