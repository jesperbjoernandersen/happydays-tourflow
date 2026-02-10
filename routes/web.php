<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

// Dashboard Routes
Route::get('/checkin', function () {
    return view('checkin');
})->name('checkin');

Route::get('/checkout', function () {
    return view('checkout');
})->name('checkout');

Route::get('/guests/search', function () {
    return view('guests.search');
})->name('guests.search');

// Booking Routes
Route::get('/booking', function () {
    return view('booking');
})->name('booking');

Route::get('/reservations', function () {
    return view('reservations');
})->name('reservations');

Route::get('/availability', function () {
    return view('availability');
})->name('availability');

// Hotel Routes
Route::get('/hotels', function () {
    return view('hotels');
})->name('hotels');

Route::get('/room-types', function () {
    return view('room-types');
})->name('room-types');

Route::get('/policies', function () {
    return view('policies');
})->name('policies');

// Pricing Routes
Route::get('/pricing-rules', function () {
    return view('pricing-rules');
})->name('pricing-rules');

Route::get('/discounts', function () {
    return view('discounts');
})->name('discounts');

Route::get('/offers', function () {
    return view('offers');
})->name('offers');

// Reports Routes
Route::get('/analytics', function () {
    return view('analytics');
})->name('analytics');

Route::get('/bookings-overview', function () {
    return view('bookings-overview');
})->name('bookings-overview');

Route::get('/revenue', function () {
    return view('revenue');
})->name('revenue');

// Settings Routes
Route::prefix('settings')->group(function () {
    Route::get('/general', function () {
        return view('settings.general');
    })->name('settings.general');
    
    Route::get('/users', function () {
        return view('settings.users');
    })->name('settings.users');
    
    Route::get('/integrations', function () {
        return view('settings.integrations');
    })->name('settings.integrations');
});

// Additional Pages
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
