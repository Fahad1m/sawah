<?php

use Illuminate\Support\Facades\Route;

// Controllers
use App\Http\Controllers\TripController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\BookingRequestController;
use App\Http\Controllers\AdminTripController;
use App\Http\Controllers\RecommendationController;


use App\Http\Middleware\AdminOnly;

 
Route::view('/', 'index')->name('home');
// صفحات Blade المستقلة (SSR)
Route::get('/mybookings', fn() => view('my'))->middleware('auth')->name('bookings');
Route::get('/recommendations', fn() => view('recommendations'))->name('recommendations');

// حجوزات المستخدم (واجهات الويب تستخدم جلسة و CSRF)
Route::middleware(['web','auth'])->group(function () {
    Route::post('/bookings',             [BookingController::class, 'store'])->name('bookings.store');
    Route::get('/bookings/mine',         [BookingController::class, 'mine'])->name('bookings.mine');

    // طلبات الإلغاء/التعديل (يرسلها الـJS كـ JSON)
  Route::get('/requests', [BookingRequestController::class, 'index'])->name('requests.index');
    Route::post('/bookings/{booking}/cancel-request', [BookingRequestController::class, 'cancel'])
        ->name('bookings.cancel-request');
    Route::post('/bookings/{booking}/modify-request', [BookingRequestController::class, 'modify'])
        ->name('bookings.modify-request');

});


Route::middleware(['web','auth'])->group(function () {
    Route::get('/recommendations', [RecommendationController::class, 'index'])->name('recommendations');
    Route::post('/recommendations', [RecommendationController::class, 'store'])->name('recommendations.store');
});

Route::prefix('admin')->middleware(['auth', AdminOnly::class])->name('admin.')->group(function () {

    // لوحة التحكم (Landing للأدمن)
    Route::get('/dashboard', [AdminTripController::class, 'index'])->name('dashboard');

    // إدارة الرحلات
    Route::get('/trips',          [AdminTripController::class, 'index'])->name('trips.manage');
    Route::post('/trips',         [AdminTripController::class, 'store'])->name('trips.store');
    Route::delete('/trips/{trip}',[AdminTripController::class, 'destroy'])->name('trips.destroy');


    // قبول/رفض طلبات العملاء (داخل AdminTripController)

    Route::post('/bookings/{booking}/approve', [AdminTripController::class, 'approveBooking'])
        ->name('bookings.approve');
    Route::post('/bookings/{booking}/reject',  [AdminTripController::class, 'rejectBooking'])
        ->name('bookings.reject');

    Route::post('/booking-requests/{bookingRequest}/approve', [AdminTripController::class, 'approveBookingRequest'])
        ->name('requests.approve');
    Route::post('/booking-requests/{bookingRequest}/reject',  [AdminTripController::class, 'rejectBookingRequest'])
        ->name('requests.reject');
});
require __DIR__.'/auth.php';
