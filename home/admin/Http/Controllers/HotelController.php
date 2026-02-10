<?php

namespace App\Http\Controllers;

use App\Models\Hotel;
use App\Models\RoomType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class HotelController extends Controller
{
    /**
     * Display a listing of hotels.
     */
    public function index()
    {
        $hotels = Hotel::with(['roomTypes', 'bookings'])
            ->withCount(['roomTypes', 'bookings'])
            ->orderBy('name')
            ->get();
        
        return view('hotels', compact('hotels'));
    }

    /**
     * Show the form for creating a new hotel.
     */
    public function create()
    {
        return view('hotels.create');
    }

    /**
     * Store a newly created hotel.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:20|unique:hotels',
            'address' => 'required|string|max:500',
            'city' => 'required|string|max:100',
            'country' => 'required|string|max:100',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $hotel = Hotel::create($validator->validated());

        return redirect()->route('hotels.index')
            ->with('success', 'Hotel created successfully.');
    }

    /**
     * Display the specified hotel.
     */
    public function show(Hotel $hotel)
    {
        $hotel->load(['roomTypes', 'agePolicies', 'stayTypes', 'bookings' => function($query) {
            $query->latest()->take(10);
        }]);
        
        return view('hotels.show', compact('hotel'));
    }

    /**
     * Show the form for editing the specified hotel.
     */
    public function edit(Hotel $hotel)
    {
        return view('hotels.edit', compact('hotel'));
    }

    /**
     * Update the specified hotel.
     */
    public function update(Request $request, Hotel $hotel)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:20|unique:hotels,code,' . $hotel->id,
            'address' => 'required|string|max:500',
            'city' => 'required|string|max:100',
            'country' => 'required|string|max:100',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $hotel->update($validator->validated());

        return redirect()->route('hotels.index')
            ->with('success', 'Hotel updated successfully.');
    }

    /**
     * Remove the specified hotel.
     */
    public function destroy(Hotel $hotel)
    {
        // Check if hotel has dependencies
        if ($hotel->bookings()->exists()) {
            return redirect()->route('hotels.index')
                ->with('error', 'Cannot delete hotel with existing bookings.');
        }

        $hotel->delete();

        return redirect()->route('hotels.index')
            ->with('success', 'Hotel deleted successfully.');
    }

    /**
     * Toggle hotel active status.
     */
    public function toggleStatus(Hotel $hotel)
    {
        $hotel->update(['is_active' => !$hotel->is_active]);
        
        $status = $hotel->is_active ? 'activated' : 'deactivated';
        return redirect()->route('hotels.index')
            ->with('success', "Hotel {$status} successfully.");
    }
}
