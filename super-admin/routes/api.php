<?php

use Illuminate\Support\Facades\Route;

Route::prefix('public')->group(function () {
    // Public API endpoints
});

Route::prefix('mock')->group(function () {
    // Mock Control API endpoints
});

Route::prefix('inspection')->group(function () {
    // Inspection API endpoints
});
