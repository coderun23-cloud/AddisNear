<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\PlaceController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ReservationController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
/*
    Authentication endpoints
*/
Route::post('register',[AuthController::class,'register']);
Route::post('login',[AuthController::class,'login']);
Route::post('logout',[AuthController::class,'logout'])->middleware('auth:sanctum');
Route::post('forgot-password', [AuthController::class, 'sendResetLinkEmail']);
Route::post('reset-password', [AuthController::class, 'reset']);

Route::apiResource('categories', CategoryController::class)->middleware('auth:sanctum');
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/place', [PlaceController::class, 'index']);
    Route::post('/place', [PlaceController::class, 'store']);
    Route::get('/place/{place}', [PlaceController::class, 'show']);
    Route::post('/place/{place}', [PlaceController::class, 'update']);
    Route::delete('/place/{place}', [PlaceController::class, 'destroy']);
});
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/menu', [MenuController::class, 'index']);
    Route::post('/menu', [MenuController::class, 'store']);
    Route::get('/menu/{menu}', [MenuController::class, 'show']);
    Route::post('/menu/{menu}', [MenuController::class, 'update']);
    Route::delete('/menu/{menu}', [MenuController::class, 'destroy']);
});
Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('reviews', ReviewController::class);
});
Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('reservations', ReservationController::class);
});
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/pay', [PaymentController::class, 'pay']);
    Route::get('/payment/callback', [PaymentController::class, 'callback'])->name('payment.callback');
});

Route::get('/payment-success', function () {
    return view('payment-success');
})->name('payment.success');

