<?php

use App\Http\Controllers\InspectionController;
use App\Http\Controllers\PublicApiController;
use App\Http\Controllers\MockControlController;
use App\Http\Controllers\PublicApiController;
use Illuminate\Support\Facades\Route;

Route::prefix('public')->group(function () {
    Route::post('/createMember', [PublicApiController::class, 'createMember']);
    Route::post('/updateMember', [PublicApiController::class, 'updateMember']);
    Route::post('/setInvestmentProfile', [PublicApiController::class, 'setInvestmentProfile']);
});

Route::prefix('mock')->group(function () {
    Route::post('addTransactions', [MockControlController::class, 'addTransactions']);
    Route::post('setDailyUnitPrices', [MockControlController::class, 'setDailyUnitPrices']);
    Route::post('resetSubjectState', [MockControlController::class, 'resetSubjectState']);
});

Route::prefix('inspection')->group(function () {
    Route::post('getRequestAudit', [InspectionController::class, 'getRequestAudit']);
    Route::post('listAuditEvents', [InspectionController::class, 'listAuditEvents']);
});
