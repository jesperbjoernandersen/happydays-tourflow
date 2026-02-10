<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HotelController;
use App\Http\Controllers\RoomTypeController;
use App\Http\Controllers\PolicyController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\CheckinController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\PricingController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\GuestController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', [DashboardController::class, 'index'])->name('home');

// Dashboard Routes
Route::get('/checkin', [CheckinController::class, 'index'])->name('checkin');
Route::post('/checkin/search', [CheckinController::class, 'search'])->name('checkin.search');
Route::post('/checkin/process', [CheckinController::class, 'process'])->name('checkin.process');

Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout');
Route::post('/checkout/search', [CheckoutController::class, 'search'])->name('checkout.search');
Route::post('/checkout/process', [CheckoutController::class, 'process'])->name('checkout.process');

Route::get('/guests/search', [GuestController::class, 'search'])->name('guests.search');
Route::get('/guests/{guest}', [GuestController::class, 'show'])->name('guests.show');

// Booking Routes
Route::get('/booking', [BookingController::class, 'index'])->name('booking');
Route::post('/booking', [BookingController::class, 'store'])->name('booking.store');

Route::get('/reservations', [BookingController::class, 'reservations'])->name('reservations');
Route::get('/reservations/{booking}', [BookingController::class, 'showReservation'])->name('reservations.show');
Route::post('/reservations/{booking}/cancel', [BookingController::class, 'cancel'])->name('reservations.cancel');
Route::patch('/reservations/{booking}/status', [BookingController::class, 'updateStatus'])->name('reservations.status');

Route::get('/availability', [BookingController::class, 'availability'])->name('availability');

// Hotel Routes
Route::resource('/hotels', HotelController::class)->except(['show']);

Route::get('/hotels/{hotel}', [HotelController::class, 'show'])->name('hotels.show');
Route::patch('/hotels/{hotel}/toggle-status', [HotelController::class, 'toggleStatus'])->name('hotels.toggle-status');

Route::resource('/room-types', RoomTypeController::class)->except(['show']);
Route::get('/room-types/{roomType}', [RoomTypeController::class, 'show'])->name('room-types.show');
Route::patch('/room-types/{roomType}/toggle-status', [RoomTypeController::class, 'toggleStatus'])->name('room-types.toggle-status');

Route::resource('/policies', PolicyController::class);

// Pricing Routes
Route::prefix('pricing')->name('pricing.')->group(function () {
    Route::get('/rules', [PricingController::class, 'rules'])->name('rules');
    Route::post('/rules', [PricingController::class, 'storeRule'])->name('rules.store');
    Route::delete('/rules/{rule}', [PricingController::class, 'destroyRule'])->name('rules.destroy');
    Route::get('/discounts', [PricingController::class, 'discounts'])->name('discounts');
    Route::get('/offers', [PricingController::class, 'offers'])->name('offers');
});

// Reports Routes
Route::prefix('reports')->name('reports.')->group(function () {
    Route::get('/analytics', [ReportController::class, 'analytics'])->name('analytics');
    Route::get('/bookings', [ReportController::class, 'bookingsOverview'])->name('bookings');
    Route::get('/revenue', [ReportController::class, 'revenue'])->name('revenue');
});

// Settings Routes
Route::prefix('settings')->name('settings.')->group(function () {
    Route::get('/general', [SettingsController::class, 'general'])->name('general');
    Route::post('/general', [SettingsController::class, 'updateGeneral'])->name('general.update');
    
    Route::get('/users', [SettingsController::class, 'users'])->name('users');
    Route::post('/users', [SettingsController::class, 'storeUser'])->name('users.store');
    Route::patch('/users/{user}/toggle', [SettingsController::class, 'toggleUser'])->name('users.toggle');
    
    Route::get('/integrations', [SettingsController::class, 'integrations'])->name('integrations');
    Route::post('/integrations/{integration}', [SettingsController::class, 'updateIntegration'])->name('integrations.update');
});

// Additional Pages (static)
Route::get('/help', function () {
    return view('help');
})->name('help');

Route::get('/contact', function () {
    return view('contact');
})->name('contact');

Route::get('/faq', function () {
    return view('faq');
})->name('faq');

Route::get('/privacy', function () {
    return view('privacy');
})->name('privacy');

Route::get('/terms', function () {
    return view('terms');
})->name('terms');
