<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ProductApiController;
use App\Http\Controllers\Api\CategoryApiController;
use App\Http\Controllers\Api\BrandApiController;
use App\Http\Controllers\Api\SliderApiController;
use App\Http\Controllers\Api\AuthApiController;
use App\Http\Controllers\Api\OrderApiController;
use App\Http\Controllers\Api\ArrepentimientoController;

Route::get('/sliders', [SliderApiController::class, 'index']);
Route::get('/products', [ProductApiController::class, 'index']);
Route::get('/products/{id}', [ProductApiController::class, 'show'])->whereNumber('id');
Route::get('/categories', [CategoryApiController::class, 'index']);
Route::get('/brands', [BrandApiController::class, 'index']);

Route::post('/arrepentimiento', [ArrepentimientoController::class, 'store']);

Route::post('/auth/register', [AuthApiController::class, 'register']);
Route::post('/auth/login', [AuthApiController::class, 'login']);
Route::post('/auth/google', [AuthApiController::class, 'googleLogin']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/auth/me', [AuthApiController::class, 'me']);
    Route::post('/auth/logout', [AuthApiController::class, 'logout']);

    Route::post('/orders', [OrderApiController::class, 'store']);
    Route::get('/orders', [OrderApiController::class, 'index']);
    Route::get('/orders/{id}', [OrderApiController::class, 'show'])->whereNumber('id');
});
