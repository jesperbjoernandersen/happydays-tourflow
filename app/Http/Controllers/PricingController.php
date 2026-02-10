<?php

namespace App\Http\Controllers;

use App\Models\RateRule;
use App\Models\RatePlan;
use App\Models\RoomType;
use App\Models\StayType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PricingController extends Controller
{
    /**
     * Display pricing rules.
     */
    public function rules(Request $request)
    {
        $query = RateRule::with(['roomType', 'stayType', 'ratePlan']);

        if ($request->has('room_type_id') && $request->room_type_id) {
            $query->where('room_type_id', $request->room_type_id);
        }

        $rules = $query->orderBy('start_date', 'desc')->get();
        $roomTypes = RoomType::where('is_active', true)->pluck('name', 'id');
        $stayTypes = StayType::pluck('name', 'id');
        $ratePlans = RatePlan::pluck('name', 'id');

        return view('pricing-rules', compact('rules', 'roomTypes', 'stayTypes', 'ratePlans'));
    }

    /**
     * Store a new pricing rule.
     */
    public function storeRule(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'room_type_id' => 'required|exists:room_types,id',
            'stay_type_id' => 'nullable|exists:stay_types,id',
            'rate_plan_id' => 'nullable|exists:rate_plans,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'base_price' => 'required|numeric|min:0',
            'price_per_adult' => 'nullable|numeric|min:0',
            'price_per_child' => 'nullable|numeric|min:0',
            'price_per_infant' => 'nullable|numeric|min:0',
            'included_occupancy' => 'nullable|integer|min:1',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        RateRule::create($validator->validated());

        return redirect()->route('pricing-rules.index')
            ->with('success', 'Pricing rule created successfully.');
    }

    /**
     * Delete a pricing rule.
     */
    public function destroyRule(RateRule $rule)
    {
        $rule->delete();
        return redirect()->route('pricing-rules.index')
            ->with('success', 'Pricing rule deleted.');
    }

    /**
     * Display discounts.
     */
    public function discounts(Request $request)
    {
        // Discounts are stored as RateRules with negative adjustment
        $discounts = RateRule::where('base_price', '<', 0)
            ->orWhereNotNull('price_per_child')
            ->with(['roomType', 'stayType'])
            ->get();
        
        return view('discounts', compact('discounts'));
    }

    /**
     * Display offers.
     */
    public function offers(Request $request)
    {
        $today = now();
        $offers = RateRule::where('start_date', '<=', $today)
            ->where('end_date', '>=', $today)
            ->with(['roomType', 'stayType'])
            ->get();
        
        return view('offers', compact('offers'));
    }
}
