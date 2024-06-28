<?php

use App\Http\Controllers\Api\AddressController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

Route::controller(AuthController::class)
    ->group(function () {
        Route::post('/login', 'auth');
        Route::post('/register', 'register');
        Route::post('/logout', 'logout')->middleware('auth:sanctum');
        Route::post('/user/change-password', 'changePassword')->middleware('auth:sanctum');
    });

Route::middleware('auth:sanctum')->group(function () {
    Route::prefix('/user')
        ->controller(UserController::class)
        ->group(function () {
            Route::get('/', 'index');
            Route::put('/', 'update');
            Route::delete('/', 'destroy');

            Route::apiResource('address', AddressController::class);
        });
});
