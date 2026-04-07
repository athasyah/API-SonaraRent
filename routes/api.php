<?php

use App\Enums\RoleEnum;
use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\InstrumentConditionController;
use App\Http\Controllers\InstrumentController;
use App\Http\Controllers\RentalController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\PenaltyController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;


Route::post('login', [AuthController::class, 'login'])->name('login');
Route::post('register', [AuthController::class, 'register'])->name('register');
Route::post('/auth/verify-otp', [AuthController::class, 'verifyOtp']);
Route::post('/auth/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/auth/reset-password', [AuthController::class, 'resetPassword']);

Route::get('settings/{key}', [SettingController::class, 'getByKey']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('me', [AuthController::class, 'getMe']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::put('/user/{id}/change-password', [AuthController::class, 'changePassword']);
});

Route::get('review/no-paginate', [ReviewController::class, 'noPaginate'])->name('review-no-paginate');
Route::get('instrument/no-paginate', [InstrumentController::class, 'noPaginate'])->name('instrument-no-paginate');


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
        Route::post('instrument/{id}', [InstrumentController::class, 'update'])->name('instrument-update');
        Route::resource('instrument', InstrumentController::class);

        //Route Activity Log
        Route::get('activity-log/no-paginate', [ActivityLogController::class, 'noPaginate'])->name('activity-log-no-paginate');
        Route::resource('activity-log', ActivityLogController::class)->only('index');

        //Route Dashboard Admin
        Route::get('dashboard/admin', [DashboardController::class, 'admin'])->name('dashboard-admin');

        //Route Report (Admin)
        Route::get('report/popular', [ReportController::class, 'popularInstruments'])->name('report-popular');

        //Route Export (Admin)
        Route::prefix('export')->group(function () {
            Route::get('rentals', [ExportController::class, 'rentals'])->name('export-rentals');
            Route::get('instruments', [ExportController::class, 'instruments'])->name('export-instruments');
            Route::get('users', [ExportController::class, 'users'])->name('export-users');
            Route::get('reviews', [ExportController::class, 'reviews'])->name('export-reviews');
            Route::get('revenue', [ExportController::class, 'revenue'])->name('export-revenue');
            Route::get('popular-instruments', [ExportController::class, 'popularInstruments'])->name('export-popular-instruments');
            Route::get('sales-trend', [ExportController::class, 'salesTrend'])->name('export-sales-trend');
            Route::get('penalties', [ExportController::class, 'penalties'])->name('export-penalties');
        });

        //Route Settings (Admin)
        Route::get('settings', [SettingController::class, 'index']);
        Route::post('settings', [SettingController::class, 'update']);
    });

    //Endpoint Role Staff
    Route::middleware(['role:' . RoleEnum::STAFF->value . '|' . RoleEnum::ADMIN->value])->group(function () {
        //Route User
        Route::post('user/{id}', [UserController::class, 'update']);

        //Route Status Rental
        Route::put('/rental/{id}/status', [RentalController::class, 'statusRental'])->name('rental-status');
        Route::put('/rental/{id}/pay', [RentalController::class, 'markAsPaid'])->name('rental-pay');
        Route::post('/rental/{id}/guarantee', [RentalController::class, 'uploadGuarantee'])->name('rental-guarantee');

        //Route Instrument Condition
        Route::get('/instrument-condition/no-paginate', [InstrumentConditionController::class, 'noPaginate'])->name('instrument-condition-no-paginate');
        Route::resource('instrument-condition', InstrumentConditionController::class);

        //Route Rental


        //Route Dashboard Staff
        Route::get('dashboard/staff', [DashboardController::class, 'staff'])->name('dashboard-staff');

        //Route Export (Staff)
        Route::get('export/rentals', [ExportController::class, 'rentals'])->name('staff-export-rentals');

        //Route Report (Staff)
        Route::get('report', [ReportController::class, 'index'])->name('report-index');
    });

    //Endpoint Role Customer
    Route::middleware(['role:' . RoleEnum::CUSTOMER->value . '|' . RoleEnum::ADMIN->value])->group(function () {

        //Route Instrument
        Route::get('instrument', [InstrumentController::class, 'index']);

        //Route User
        Route::post('user/{id}', [UserController::class, 'update']);



        //Route Review
        Route::resource('review', ReviewController::class);
    });
    //Route Rental
    Route::get('my/rental', [RentalController::class, 'getByUser'])->name('my-rental');
    Route::post('/rental/{id}/simulate-payment', [RentalController::class, 'simulatePayment'])->name('rental-simulate-payment');

    Route::get('/cart/availability', [CartController::class, 'availability']);


    Route::middleware(['role:' . RoleEnum::ADMIN->value . '|' . RoleEnum::STAFF->value . '|' . RoleEnum::CUSTOMER->value])->group(function () {
        Route::get('rental/no-paginate', [RentalController::class, 'noPaginate'])->name('rental-no-paginate');
        Route::resource('rental', RentalController::class);

        Route::get('penalty/no-paginate', [PenaltyController::class, 'noPaginate'])->name('penalty-no-paginate');
        Route::resource('penalty', PenaltyController::class);
    });
});
