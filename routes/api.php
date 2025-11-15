<?php

use App\Http\Controllers\Api\RoomAvailabilityController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Public API routes - No authentication required
Route::prefix('v1')->group(function () {
    // Room availability endpoints
    Route::get('/rooms', [RoomAvailabilityController::class, 'index']);
    Route::get('/rooms/search', [RoomAvailabilityController::class, 'search']);
    Route::get('/rooms/{roomId}/availability', [RoomAvailabilityController::class, 'checkAvailability']);
});
