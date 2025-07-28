<?php

use App\Http\Controllers\BrandController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\DistributorClientController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\StockMovementController;
use App\Http\Controllers\SupplierInventoryController;
use App\Http\Controllers\TechnicalRecordController;
use App\Http\Controllers\DistributorTechnicalRecordController;
use App\Http\Controllers\StockAlertController;
use App\Models\DistributorTechnicalRecord;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

// Ruta principal
Route::get('/', [App\Http\Controllers\HomeController::class, 'index']);

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
    Route::post('distributor-clients/{id}/restore', [App\Http\Controllers\DistributorClientController::class, 'restore'])->name('distributor-clients.restore');
    
    // CRUD de fichas técnicas de distribuidores
    Route::resource('distributor-clients.technical-records', DistributorTechnicalRecordController::class);
    Route::post('distributor-clients/{distributorClient}/technical-records/{distributorTechnicalRecord}/delete-photo',
        [DistributorTechnicalRecordController::class, 'deletePhoto'])
        ->name('distributor-clients.technical-records.delete-photo');

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

    Route::post('clients/{id}/restore', [App\Http\Controllers\ClientController::class, 'restore'])->name('clients.restore');

    // Alertas de stock
    Route::resource('stock-alerts', StockAlertController::class)->only(['index', 'destroy']);
    Route::get('stock-alerts/peluqueria', [StockAlertController::class, 'peluqueria'])->name('stock-alerts.peluqueria');
    Route::get('stock-alerts/distribuidora', [StockAlertController::class, 'distribuidora'])->name('stock-alerts.distribuidora');
    Route::post('stock-alerts/{alert}/mark-read', [StockAlertController::class, 'markAsRead'])->name('stock-alerts.mark-read');
    Route::post('stock-alerts/mark-all-read', [StockAlertController::class, 'markAllAsRead'])->name('stock-alerts.mark-all-read');
    Route::post('stock-alerts/mark-all-read-by-type', [StockAlertController::class, 'markAllAsReadByType'])->name('stock-alerts.mark-all-read-by-type');
    Route::get('stock-alerts/unread-count', [StockAlertController::class, 'getUnreadCount'])->name('stock-alerts.unread-count');
    Route::get('stock-alerts/unread-count-peluqueria', [StockAlertController::class, 'getUnreadCountPeluqueria'])->name('stock-alerts.unread-count-peluqueria');
    Route::get('stock-alerts/unread-count-distribuidora', [StockAlertController::class, 'getUnreadCountDistribuidora'])->name('stock-alerts.unread-count-distribuidora');

});
