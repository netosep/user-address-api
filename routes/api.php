<?php

use App\Http\Controllers\Api\AddressController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'auth'])->name('auth');
Route::post('/register', [AuthController::class, 'register'])->name('register');

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::controller(UserController::class)
        ->prefix('/user')
        ->group(function () {
            Route::get('/me', 'me')->name('me');
            Route::apiResource('address', AddressController::class);
        });
});
