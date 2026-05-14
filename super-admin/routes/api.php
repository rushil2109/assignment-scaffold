<?php

use App\Http\Controllers\MockControlController;
use Illuminate\Support\Facades\Route;

Route::prefix('public')->group(function () {
    // Public API endpoints
});

Route::prefix('mock')->group(function () {
    Route::post('addTransactions', [MockControlController::class, 'addTransactions']);
    Route::post('setDailyUnitPrices', [MockControlController::class, 'setDailyUnitPrices']);
});

Route::prefix('inspection')->group(function () {
    // Inspection API endpoints
});
