<?php

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
