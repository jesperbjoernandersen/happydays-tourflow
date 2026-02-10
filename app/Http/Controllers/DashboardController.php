<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Hotel;
use App\Models\RoomType;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Display the dashboard with stats and quick actions.
     */
    public function index()
    {
        $today = Carbon::today();
        
        // Get stats for dashboard
        $todayCheckins = Booking::whereDate('check_in_date', $today)
            ->where('status', 'confirmed')
            ->count();
        
        $activeBookings = Booking::whereIn('status', ['confirmed', 'checked_in'])
            ->whereDate('check_out_date', '>=', $today)
            ->count();
        
        $totalRooms = RoomType::sum('base_occupancy') + RoomType::sum('extra_bed_slots');
        $bookedRooms = Booking::whereIn('status', ['confirmed', 'checked_in'])
            ->whereDate('check_in_date', '<=', $today)
            ->whereDate('check_out_date', '>', $today)
            ->sum('guest_count');
        
        $availableRooms = max(0, $totalRooms - $bookedRooms);
        
        // Revenue today (from checked-out bookings today)
        $revenueToday = Booking::whereDate('check_out_date', $today)
            ->where('status', 'checked_out')
            ->sum('total_price');
        
        // Recent activity
        $recentBookings = Booking::with(['hotel', 'roomType'])
            ->latest()
            ->take(5)
            ->get();
        
        return view('welcome', compact(
            'todayCheckins',
            'activeBookings',
            'availableRooms',
            'totalRooms',
            'revenueToday',
            'recentBookings'
        ));
    }
}
