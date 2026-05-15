<?php

use App\Http\Controllers\PublicApiController;
use App\Http\Controllers\MockControlController;
use Illuminate\Support\Facades\Route;

Route::prefix('public')->group(function () {
    Route::post('/createMember', [PublicApiController::class, 'createMember']);
});

Route::prefix('mock')->group(function () {
    Route::post('addTransactions', [MockControlController::class, 'addTransactions']);
    Route::post('setDailyUnitPrices', [MockControlController::class, 'setDailyUnitPrices']);
    Route::post('moveDayForward', [MockControlController::class, 'moveDayForward']);
});

Route::prefix('inspection')->group(function () {
    // Inspection API endpoints
});
