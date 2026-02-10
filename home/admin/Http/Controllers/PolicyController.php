<?php

namespace App\Http\Controllers;

use App\Models\HotelAgePolicy;
use App\Models\Hotel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PolicyController extends Controller
{
    /**
     * Display a listing of policies.
     */
    public function index(Request $request)
    {
        $hotels = Hotel::with(['agePolicies'])->get();
        return view('policies', compact('hotels'));
    }

    /**
     * Show the form for creating a new policy.
     */
    public function create()
    {
        $hotels = Hotel::where('is_active', true)->pluck('name', 'id');
        return view('policies.create', compact('hotels'));
    }

    /**
     * Store a newly created policy.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'hotel_id' => 'required|exists:hotels,id',
            'name' => 'required|string|max:255',
            'infant_max_age' => 'nullable|integer|min:0',
            'child_max_age' => 'nullable|integer|min:0',
            'adult_min_age' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        HotelAgePolicy::create($validator->validated());

        return redirect()->route('policies.index')
            ->with('success', 'Policy created successfully.');
    }

    /**
     * Display the specified policy.
     */
    public function show(HotelAgePolicy $policy)
    {
        $policy->load('hotel');
        return view('policies.show', compact('policy'));
    }

    /**
     * Show the form for editing the specified policy.
     */
    public function edit(HotelAgePolicy $policy)
    {
        $hotels = Hotel::where('is_active', true)->pluck('name', 'id');
        return view('policies.edit', compact('policy', 'hotels'));
    }

    /**
     * Update the specified policy.
     */
    public function update(Request $request, HotelAgePolicy $policy)
    {
        $validator = Validator::make($request->all(), [
            'hotel_id' => 'required|exists:hotels,id',
            'name' => 'required|string|max:255',
            'infant_max_age' => 'nullable|integer|min:0',
            'child_max_age' => 'nullable|integer|min:0',
            'adult_min_age' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $policy->update($validator->validated());

        return redirect()->route('policies.index')
            ->with('success', 'Policy updated successfully.');
    }

    /**
     * Remove the specified policy.
     */
    public function destroy(HotelAgePolicy $policy)
    {
        $policy->delete();
        return redirect()->route('policies.index')
            ->with('success', 'Policy deleted successfully.');
    }
}
