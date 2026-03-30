<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ProductApiController;
use App\Http\Controllers\Api\CategoryApiController;
use App\Http\Controllers\Api\BrandApiController;
use App\Http\Controllers\Api\SliderApiController;

Route::get('/sliders', [SliderApiController::class, 'index']);
Route::get('/products', [ProductApiController::class, 'index']);
Route::get('/products/{id}', [ProductApiController::class, 'show'])->whereNumber('id');
Route::get('/categories', [CategoryApiController::class, 'index']);
Route::get('/brands', [BrandApiController::class, 'index']);
