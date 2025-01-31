<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\ProductController;

Route::group(
    ['middleware' => 'api','prefix' => 'auth'], function ($router) {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:api');
    Route::post('/refresh', [AuthController::class, 'refresh'])->middleware('auth:api');
    Route::post('/profile', [AuthController::class, 'profile'])->middleware('auth:api');
});

Route::group(
    ['middleware' => 'api','prefix' => 'products'], function ($router) {
    Route::get('/', [ProductController::class, 'index'])->middleware('auth:api');
    Route::get('/{id}', [ProductController::class, 'show'])->middleware('auth:api');
    Route::post('/', [ProductController::class, 'store'])->middleware('auth:api');
    Route::put('/{id}', [ProductController::class, 'update'])->middleware('auth:api');
    Route::delete('/{id}', [ProductController::class, 'destroy'])->middleware('auth:api');
});
