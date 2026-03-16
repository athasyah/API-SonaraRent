<?php

use App\Enums\RoleEnum;
use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\InstrumentConditionController;
use App\Http\Controllers\InstrumentController;
use App\Http\Controllers\RentalController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;


Route::post('login', [AuthController::class, 'login'])->name('login');
Route::post('register', [AuthController::class, 'register'])->name('register');
Route::post('/auth/verify-otp', [AuthController::class, 'verifyOtp']);
Route::post('/auth/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/auth/reset-password', [AuthController::class, 'resetPassword']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('me', [AuthController::class, 'getMe']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::put('/user/{id}/change-password', [AuthController::class, 'changePassword']);
});

Route::middleware('auth:sanctum')->group(function () {

    //Endpoint Role Admin
    Route::middleware(['role:' . RoleEnum::ADMIN->value])->group(function () {

        //Route User
        // Route::post('user/{id}', [UserController::class, 'update']);
        Route::resource('user', UserController::class);

        //route Kategori
        Route::get('category/no-paginate', [CategoryController::class, 'noPaginate'])->name('category-no-paginate');
        Route::resource('category', CategoryController::class);

        //Route Instrument
        Route::get('instrument/no-paginate', [InstrumentController::class, 'noPaginate'])->name('instrument-no-paginate');
        Route::post('instrument/{id}', [InstrumentController::class, 'update'])->name('instrument-update');
        Route::resource('instrument', InstrumentController::class);

        //Route Activity Log
        Route::get('activity-log/no-paginate', [ActivityLogController::class, 'noPaginate'])->name('activity-log-no-paginate');
        Route::resource('activity-log', ActivityLogController::class)->only('index');
    });

    //Endpoint Role Staff
    Route::middleware(['role:' . RoleEnum::STAFF->value . '|' . RoleEnum::ADMIN->value])->group(function () {
        //Route User
        Route::post('user/{id}', [UserController::class, 'update']);

        //Route Status Rental
        Route::put('/rental/{id}/status', [RentalController::class, 'statusRental'])->name('rental-status');

        //Route Instrument Condition
        Route::get('/instrument-condition/no-paginate', [InstrumentConditionController::class, 'noPaginate'])->name('instrument-condition-no-paginate');
        Route::resource('instrument-condition', InstrumentConditionController::class);

        //Route Rental
        Route::put('/rental/{id}/status', [RentalController::class, 'statusRental'])->name('rental-status');
        Route::get('rental/no-paginate', [RentalController::class, 'noPaginate'])->name('rental-no-paginate');
        Route::resource('rental', RentalController::class);
    });

    //Endpoint Role Customer
    Route::middleware(['role:' . RoleEnum::CUSTOMER->value . '|' . RoleEnum::ADMIN->value])->group(function () {

        //Route Instrument
        Route::get('instrument/no-paginate', [InstrumentController::class, 'noPaginate'])->name('instrument-no-paginate');
        Route::get('instrument', [InstrumentController::class, 'index']);

        //Route User
        Route::post('user/{id}', [UserController::class, 'update']);

        //Route Rental
        Route::get('rental/no-paginate', [RentalController::class, 'noPaginate'])->name('rental-no-paginate');
        Route::resource('rental', RentalController::class);

        //Route Review
        Route::get('review/no-paginate', [ReviewController::class, 'noPaginate'])->name('review-no-paginate');
        Route::resource('review', ReviewController::class);
    });
    //Route Rental
    Route::get('my/rental', [RentalController::class, 'getByUser'])->name('my-rental');

    Route::get('/cart/availability', [CartController::class, 'availability']);
});
