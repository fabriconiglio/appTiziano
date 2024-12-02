<?php

use App\Http\Controllers\ProductController;
use App\Http\Controllers\StockMovementController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

// Ruta principal
Route::get('/', function () {
    return view('home');
});

// Rutas de autenticación con verificación de correo habilitada
Auth::routes(['verify' => true]);

// Ruta protegida, accesible solo para usuarios verificados
Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])
    ->name('home')
    ->middleware('verified');

// Rutas protegidas: Solo accesibles si el usuario está logeado
Route::middleware(['auth'])->group(function () {
    // CRUD de productos
    Route::resource('products', ProductController::class);

    // Movimiento de stock
    Route::post('stock-movements', [StockMovementController::class, 'store'])->name('stock-movements.store');
});
