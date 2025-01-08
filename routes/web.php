<?php

use App\Http\Controllers\ClientController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\StockMovementController;
use App\Http\Controllers\TechnicalRecordController;
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
    Route::get('stock-movements/create', [StockMovementController::class, 'create'])->name('stock-movements.create');
    Route::post('stock-movements', [StockMovementController::class, 'store'])->name('stock-movements.store');

    // CRUD de clientes y registros técnicos
    Route::resource('clients', ClientController::class);
    Route::resource('clients.technical-records', TechnicalRecordController::class);

    Route::post('clients/{client}/technical-records/{technicalRecord}/delete-photo',
        [TechnicalRecordController::class, 'deletePhoto'])
        ->name('clients.technical-records.delete-photo');
});
