<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Hotel;
use App\Models\RoomType;
use App\Models\StayType;
use App\Models\HotelAgePolicy;
use App\Services\PricingService;
use App\Services\AvailabilityService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class BookingController extends Controller
{
    /**
     * Display the booking page.
     */
    public function index(Request $request)
    {
        $hotels = Hotel::where('is_active', true)->get();
        $stayTypes = StayType::all();
        $roomTypes = RoomType::with('hotel')->where('is_active', true)->get();
        
        return view('booking', compact('hotels', 'stayTypes', 'roomTypes'));
    }

    /**
     * Store a new booking.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'hotel_id' => 'required|exists:hotels,id',
            'stay_type_id' => 'required|exists:stay_types,id',
            'room_type_id' => 'required|exists:room_types,id',
            'check_in_date' => 'required|date|after_or_equal:today',
            'check_out_date' => 'required|date|after:check_in_date',
            'adults' => 'required|integer|min:1',
            'children' => 'nullable|integer|min:0',
            'infants' => 'nullable|integer|min:0',
            'guests' => 'required|array|min:1',
            'guests.*.name' => 'required|string|max:255',
            'guests.*.birthdate' => 'required|date',
            'guests.*.category' => 'required|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $data = $validator->validated();
        
        // Calculate pricing
        $pricingService = app(PricingService::class);
        $stayType = StayType::find($data['stay_type_id']);
        $roomType = RoomType::find($data['room_type_id']);
        $hotel = Hotel::find($data['hotel_id']);
        $agePolicy = $hotel->agePolicies()->first();
        
        $checkIn = Carbon::parse($data['check_in_date']);
        $checkOut = Carbon::parse($data['check_out_date']);
        
        $pricing = $pricingService->calculatePrice(
            $stayType,
            $roomType,
            $checkIn,
            $checkOut,
            $data['adults'],
            $data['children'] ?? 0,
            $data['infants'] ?? 0,
            $agePolicy
        );

        // Generate booking reference
        $reference = 'BK-' . strtoupper(Str::random(6));

        // Create booking
        $booking = Booking::create([
            'booking_reference' => $reference,
            'stay_type_id' => $data['stay_type_id'],
            'room_type_id' => $data['room_type_id'],
            'hotel_id' => $data['hotel_id'],
            'check_in_date' => $data['check_in_date'],
            'check_out_date' => $data['check_out_date'],
            'total_price' => $pricing->getTotalPrice(),
            'currency' => 'EUR',
            'status' => 'confirmed',
            'hotel_age_policy_snapshot' => $agePolicy ? $agePolicy->toArray() : null,
            'price_breakdown_json' => $pricing->toArray(),
            'guest_count' => count($data['guests']),
            'notes' => null,
        ]);

        // Create guest records
        foreach ($data['guests'] as $guest) {
            $booking->guests()->create([
                'name' => $guest['name'],
                'birthdate' => $guest['birthdate'],
                'age_category' => $guest['category'],
            ]);
        }

        return redirect()->route('reservations.show', $booking->id)
            ->with('success', "Booking {$reference} created successfully!");
    }

    /**
     * Display a listing of reservations.
     */
    public function reservations(Request $request)
    {
        $query = Booking::with(['hotel', 'roomType', 'guests']);

        // Filters
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('booking_reference', 'like', "%{$search}%")
                  ->orWhereHas('guests', function($g) use ($search) {
                      $g->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $reservations = $query->latest()->paginate(20);
        
        return view('reservations', compact('reservations'));
    }

    /**
     * Display the specified reservation.
     */
    public function showReservation(Booking $booking)
    {
        $booking->load(['hotel', 'roomType', 'stayType', 'guests']);
        return view('reservations.show', compact('booking'));
    }

    /**
     * Cancel a booking.
     */
    public function cancel(Booking $booking)
    {
        if (in_array($booking->status, ['checked_in', 'checked_out', 'cancelled'])) {
            return redirect()->back()
                ->with('error', 'Cannot cancel this booking.');
        }

        $booking->update(['status' => 'cancelled']);
        
        return redirect()->route('reservations.index')
            ->with('success', "Booking {$booking->booking_reference} cancelled.");
    }

    /**
     * Update booking status (check-in/out).
     */
    public function updateStatus(Request $request, Booking $booking)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:confirmed,checked_in,checked_out,cancelled',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator);
        }

        $booking->update(['status' => $request->status]);
        
        return redirect()->back()
            ->with('success', "Booking status updated to {$request->status}.");
    }

    /**
     * Get availability for dates/room type.
     */
    public function availability(Request $request)
    {
        $hotels = Hotel::where('is_active', true)->get();
        $stayTypes = StayType::all();
        
        $availability = null;
        $selectedHotel = null;
        $selectedRoomType = null;
        $startDate = Carbon::today();
        $endDate = Carbon::today()->addDays(28);

        if ($request->has('hotel_id') && $request->has('room_type_id')) {
            $selectedHotel = Hotel::find($request->hotel_id);
            $selectedRoomType = RoomType::find($request->room_type_id);
            
            $availabilityService = app(AvailabilityService::class);
            $availability = $availabilityService->getAvailabilityCalendar(
                $selectedRoomType,
                $startDate,
                $endDate
            );
        }

        return view('availability', compact('hotels', 'stayTypes', 'availability', 'selectedHotel', 'selectedRoomType'));
    }
}
