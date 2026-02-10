<?php

use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\AvailabilityController;
use App\Http\Controllers\Api\PricingController;
use App\Http\Controllers\Api\StayTypeController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group.
|
*/

// Stay Types API
Route::prefix('stay-types')->controller(StayTypeController::class)->group(function () {
    Route::get('/', 'index');
    Route::get('/{stayType}', 'show');
    Route::post('/', 'store');
    Route::put('/{stayType}', 'update');
    Route::delete('/{stayType}', 'destroy');
});

// Pricing API
Route::prefix('pricing')->controller(PricingController::class)->group(function () {
    Route::post('/calculate', 'calculate');
    Route::get('/breakdown/{stayType}/{checkInDate}/{nights}', 'breakdown');
    Route::get('/availability/{stayType}/{year}/{month}', 'availability');
});

// Availability API
Route::prefix('availability')->controller(AvailabilityController::class)->group(function () {
    // GET /api/availability/{stay_type_id} - Check availability for a stay type
    // Query params: check_in_date (required), nights, occupancy
    Route::get('/{stayType}', 'index');

    // GET /api/availability/{stay_type_id}/calendar/{year}/{month} - Get monthly calendar view
    Route::get('/{stayType}/calendar/{year}/{month}', 'calendar');

    // POST /api/availability/check - Bulk availability check
    Route::post('/check', 'bulkCheck');
});

// Bookings API
Route::prefix('bookings')->controller(BookingController::class)->group(function () {
    Route::get('/', 'index');
    Route::post('/', 'store');
    Route::get('/{id}', 'show');
    Route::put('/{id}/cancel', 'cancel');
    Route::put('/{id}/status', 'updateStatus');
});
