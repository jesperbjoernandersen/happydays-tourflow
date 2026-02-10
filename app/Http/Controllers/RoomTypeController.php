<?php

namespace App\Http\Controllers;

use App\Models\RoomType;
use App\Models\Hotel;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class RoomTypeController extends Controller
{
    /**
     * Display a listing of room types.
     */
    public function index(Request $request): View
    {
        $query = RoomType::with(['hotel']);

        if ($request->filled('hotel_id')) {
            $query->where('hotel_id', $request->hotel_id);
        }

        $roomTypes = $query->paginate(15);
        $hotels = Hotel::where('is_active', true)->get();

        return view('room-types', compact('roomTypes', 'hotels'));
    }

    /**
     * Show the form for creating a new room type.
     */
    public function create(): View
    {
        $hotels = Hotel::where('is_active', true)->get();
        return view('room-types.create', compact('hotels'));
    }

    /**
     * Store a newly created room type.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'hotel_id' => 'required|exists:hotels,id',
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:room_types',
            'description' => 'nullable|string',
            'capacity' => 'required|integer|min:1',
            'base_price' => 'required|numeric|min:0',
            'is_active' => 'boolean',
        ]);

        RoomType::create($validated);

        return redirect()->route('room-types.index')
            ->with('success', 'Room type created successfully.');
    }

    /**
     * Display a specific room type.
     */
    public function show(RoomType $roomType): View
    {
        $roomType->load(['hotel', 'bookings' => function ($query) {
            $query->latest()->limit(10);
        }]);

        return view('room-types.show', compact('roomType'));
    }

    /**
     * Show the form for editing a room type.
     */
    public function edit(RoomType $roomType): View
    {
        $hotels = Hotel::where('is_active', true)->get();
        return view('room-types.edit', compact('roomType', 'hotels'));
    }

    /**
     * Update a room type.
     */
    public function update(Request $request, RoomType $roomType): RedirectResponse
    {
        $validated = $request->validate([
            'hotel_id' => 'required|exists:hotels,id',
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:room_types,code,' . $roomType->id,
            'description' => 'nullable|string',
            'capacity' => 'required|integer|min:1',
            'base_price' => 'required|numeric|min:0',
            'is_active' => 'boolean',
        ]);

        $roomType->update($validated);

        return redirect()->route('room-types.show', $roomType)
            ->with('success', 'Room type updated successfully.');
    }

    /**
     * Delete a room type.
     */
    public function destroy(RoomType $roomType): RedirectResponse
    {
        $roomType->delete();

        return redirect()->route('room-types.index')
            ->with('success', 'Room type deleted successfully.');
    }
}
