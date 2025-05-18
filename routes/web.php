<?php

use App\Http\Controllers\BrandController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\DistributorClientController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\StockMovementController;
use App\Http\Controllers\SupplierInventoryController;
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

    Route::get('/products/brands-by-category/{category}', [ProductController::class, 'getBrandsByCategory'])
        ->name('products.brands-by-category');

    // Movimiento de stock
    Route::get('stock-movements/create', [StockMovementController::class, 'create'])->name('stock-movements.create');
    Route::post('stock-movements', [StockMovementController::class, 'store'])->name('stock-movements.store');

    // CRUD de clientes y registros técnicos
    Route::resource('clients', ClientController::class);
    Route::resource('clients.technical-records', TechnicalRecordController::class);

    Route::post('clients/{client}/technical-records/{technicalRecord}/delete-photo',
        [TechnicalRecordController::class, 'deletePhoto'])
        ->name('clients.technical-records.delete-photo');

    // CRUD de clientes de distribuidores
    Route::resource('distributor-clients', DistributorClientController::class);

    // CRUD de inventario de proveedores
    Route::resource('supplier-inventories', SupplierInventoryController::class);
    Route::post('supplier-inventories/{supplierInventory}/adjust-stock', [SupplierInventoryController::class, 'adjustStock'])
        ->name('supplier-inventories.adjust-stock');

    // CRUD de categorías
    Route::resource('categories', CategoryController::class);

    // CRUD de marcas
    Route::resource('brands', BrandController::class);

    // CRUD de distributor brands
    Route::resource('distributor_brands', \App\Http\Controllers\DistributorBrandController::class);

    // CRUD de distributor categories
    Route::resource('distributor_categories', \App\Http\Controllers\DistributorCategoryController::class);

});
