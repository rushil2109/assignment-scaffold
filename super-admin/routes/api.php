<?php

use App\Http\Controllers\PublicApiController;
use Illuminate\Support\Facades\Route;

Route::prefix('public')->group(function () {
    Route::post('/createMember', [PublicApiController::class, 'createMember']);
});

Route::prefix('mock')->group(function () {
    // Mock Control API endpoints
});

Route::prefix('inspection')->group(function () {
    // Inspection API endpoints
});
