<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Hotel;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    /**
     * Display analytics dashboard.
     */
    public function analytics(Request $request)
    {
        $period = $request->get('period', '30');
        $startDate = Carbon::now()->subDays($period);
        $endDate = Carbon::now();

        // Occupancy trends
        $occupancyData = $this->getOccupancyTrend($startDate, $endDate);
        
        // Booking trends
        $bookingData = $this->getBookingTrend($startDate, $endDate);
        
        // Revenue by room type
        $revenueByRoomType = $this->getRevenueByRoomType($startDate, $endDate);
        
        // Summary stats
        $stats = $this->getStats($startDate, $endDate);

        return view('analytics', compact(
            'occupancyData', 
            'bookingData', 
            'revenueByRoomType',
            'stats',
            'period'
        ));
    }

    /**
     * Display bookings overview report.
     */
    public function bookingsOverview(Request $request)
    {
        $query = Booking::with(['hotel', 'roomType', 'guests']);

        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        if ($request->has('date_from') && $request->date_from) {
            $query->whereDate('check_in_date', '>=', $request->date_from);
        }

        if ($request->has('date_to') && $request->date_to) {
            $query->whereDate('check_in_date', '<=', $request->date_to);
        }

        $bookings = $query->latest()->paginate(25);

        return view('bookings-overview', compact('bookings'));
    }

    /**
     * Display revenue report.
     */
    public function revenue(Request $request)
    {
        $period = $request->get('period', '30');
        $startDate = Carbon::now()->subDays($period);
        $endDate = Carbon::now();

        // Revenue by day
        $revenueByDay = $this->getRevenueByDay($startDate, $endDate);
        
        // Revenue by hotel
        $revenueByHotel = $this->getRevenueByHotel($startDate, $endDate);
        
        // Revenue summary
        $summary = $this->getRevenueSummary($startDate, $endDate);

        return view('revenue', compact('revenueByDay', 'revenueByHotel', 'summary', 'period'));
    }

    /**
     * Get occupancy trend data.
     */
    protected function getOccupancyTrend($startDate, $endDate)
    {
        $days = [];
        $current = $startDate->copy();
        
        while ($current <= $endDate) {
            $checkedIn = Booking::whereDate('check_in_date', '<=', $current)
                ->whereDate('check_out_date', '>', $current)
                ->whereIn('status', ['confirmed', 'checked_in'])
                ->count();
            
            $days[] = [
                'date' => $current->format('Y-m-d'),
                'occupancy' => $checkedIn,
            ];
            
            $current->addDay();
        }
        
        return $days;
    }

    /**
     * Get booking trend data.
     */
    protected function getBookingTrend($startDate, $endDate)
    {
        return Booking::whereBetween('created_at', [$startDate, $endDate])
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'))
            ->groupBy('date')
            ->get()
            ->pluck('count', 'date')
            ->toArray();
    }

    /**
     * Get revenue by room type.
     */
    protected function getRevenueByRoomType($startDate, $endDate)
    {
        return Booking::whereBetween('created_at', [$startDate, $endDate])
            ->where('status', 'checked_out')
            ->join('room_types', 'bookings.room_type_id', '=', 'room_types.id')
            ->groupBy('room_types.name')
            ->select(['room_types.name', DB::raw('SUM(bookings.total_price) as revenue')])
            ->pluck('revenue', 'name')
            ->toArray();
    }

    /**
     * Get summary stats.
     */
    protected function getStats($startDate, $endDate)
    {
        return [
            'total_bookings' => Booking::whereBetween('created_at', [$startDate, $endDate])->count(),
            'total_revenue' => Booking::whereBetween('created_at', [$startDate, $endDate])
                ->where('status', 'checked_out')
                ->sum('total_price'),
            'avg_booking_value' => Booking::whereBetween('created_at', [$startDate, $endDate])
                ->where('status', 'checked_out')
                ->avg('total_price') ?? 0,
            'cancellation_rate' => Booking::whereBetween('created_at', [$startDate, $endDate])
                ->where('status', 'cancelled')
                ->count() / max(1, Booking::whereBetween('created_at', [$startDate, $endDate])->count()) * 100,
        ];
    }

    /**
     * Get revenue by day.
     */
    protected function getRevenueByDay($startDate, $endDate)
    {
        $days = [];
        $current = $startDate->copy();
        
        while ($current <= $endDate) {
            $revenue = Booking::whereDate('check_out_date', $current)
                ->where('status', 'checked_out')
                ->sum('total_price');
            
            $days[] = [
                'date' => $current->format('Y-m-d'),
                'revenue' => $revenue,
            ];
            
            $current->addDay();
        }
        
        return $days;
    }

    /**
     * Get revenue by hotel.
     */
    protected function getRevenueByHotel($startDate, $endDate)
    {
        return Booking::whereBetween('created_at', [$startDate, $endDate])
            ->where('status', 'checked_out')
            ->join('hotels', 'bookings.hotel_id', '=', 'hotels.id')
            ->groupBy('hotels.name')
            ->select(['hotels.name', DB::raw('SUM(bookings.total_price) as revenue')])
            ->pluck('revenue', 'name')
            ->toArray();
    }

    /**
     * Get revenue summary.
     */
    protected function getRevenueSummary($startDate, $endDate)
    {
        return [
            'total_revenue' => Booking::whereBetween('created_at', [$startDate, $endDate])
                ->where('status', 'checked_out')
                ->sum('total_price'),
            'avg_daily' => Booking::whereBetween('created_at', [$startDate, $endDate])
                ->where('status', 'checked_out')
                ->sum('total_price') / max(1, $startDate->diffInDays($endDate)),
            'total_bookings' => Booking::whereBetween('created_at', [$startDate, $endDate])
                ->count(),
        ];
    }
}
