<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TripController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\TripRequestController;
Route::get('/trips', [TripController::class, 'apiIndex']);   // JSON + Pagination

Route::get('/requests', [TripRequestController::class, 'index']);
Route::get('/requests/mine', [TripRequestController::class, 'mine']);
Route::post('/requests', [TripRequestController::class, 'store']);
Route::patch('/requests/{id}/approve', [TripRequestController::class, 'approve']);
Route::patch('/requests/{id}/reject', [TripRequestController::class, 'reject']);

Route::delete('/requests/{id}', [TripRequestController::class, 'destroy']);
Route::post('/bookings', [BookingController::class, 'store']);     // إنشاء حجز
Route::get('/bookings/mine', [BookingController::class, 'mine']);  // حجوزاتي
Route::get('/bookings', [BookingController::class, 'index']);      // (عرض للإدارة/الواجهة)

