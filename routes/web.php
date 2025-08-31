<?php

use App\Http\Controllers\BrandController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\DistributorClientController;
use App\Http\Controllers\DistributorCurrentAccountController;
use App\Http\Controllers\DistributorQuotationController;
use App\Http\Controllers\DailySalesController;
use App\Http\Controllers\HairdressingDailySalesController;
use App\Http\Controllers\ArtisanController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\StockMovementController;
use App\Http\Controllers\SupplierInventoryController;
use App\Http\Controllers\TechnicalRecordController;
use App\Http\Controllers\DistributorTechnicalRecordController;
use App\Http\Controllers\StockAlertController;
use App\Http\Controllers\ClientCurrentAccountController;
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
    
    // CRUD de cuentas corrientes de clientes de peluquería
    Route::get('client-current-accounts', [ClientCurrentAccountController::class, 'index'])->name('client-current-accounts.index');
    Route::get('clients/{client}/current-accounts', [ClientCurrentAccountController::class, 'show'])->name('clients.current-accounts.show');
    Route::get('clients/{client}/current-accounts/create', [ClientCurrentAccountController::class, 'create'])->name('clients.current-accounts.create');
    Route::post('clients/{client}/current-accounts', [ClientCurrentAccountController::class, 'store'])->name('clients.current-accounts.store');
    Route::get('clients/{client}/current-accounts/{currentAccount}/edit', [ClientCurrentAccountController::class, 'edit'])->name('clients.current-accounts.edit');
    Route::put('clients/{client}/current-accounts/{currentAccount}', [ClientCurrentAccountController::class, 'update'])->name('clients.current-accounts.update');
    Route::delete('clients/{client}/current-accounts/{currentAccount}', [ClientCurrentAccountController::class, 'destroy'])->name('clients.current-accounts.destroy');
    Route::delete('clients/{client}/current-accounts', [ClientCurrentAccountController::class, 'destroyAll'])->name('clients.current-accounts.destroy-all');

    Route::post('clients/{client}/technical-records/{technicalRecord}/delete-photo',
        [TechnicalRecordController::class, 'deletePhoto'])
        ->name('clients.technical-records.delete-photo');

    // CRUD de clientes de distribuidores
    Route::resource('distributor-clients', DistributorClientController::class);
    Route::post('distributor-clients/{id}/restore', [App\Http\Controllers\DistributorClientController::class, 'restore'])->name('distributor-clients.restore');
    
    // CRUD de cuentas corrientes de distribuidores
    Route::get('distributor-current-accounts', [DistributorCurrentAccountController::class, 'index'])->name('distributor-current-accounts.index');
    Route::get('distributor-clients/{distributorClient}/current-accounts', [DistributorCurrentAccountController::class, 'show'])->name('distributor-clients.current-accounts.show');
    Route::get('distributor-clients/{distributorClient}/current-accounts/create', [DistributorCurrentAccountController::class, 'create'])->name('distributor-clients.current-accounts.create');
    Route::post('distributor-clients/{distributorClient}/current-accounts', [DistributorCurrentAccountController::class, 'store'])->name('distributor-clients.current-accounts.store');
    Route::get('distributor-clients/{distributorClient}/current-accounts/{currentAccount}/edit', [DistributorCurrentAccountController::class, 'edit'])->name('distributor-clients.current-accounts.edit');
    Route::put('distributor-clients/{distributorClient}/current-accounts/{currentAccount}', [DistributorCurrentAccountController::class, 'update'])->name('distributor-clients.current-accounts.update');
    Route::delete('distributor-clients/{distributorClient}/current-accounts/{currentAccount}', [DistributorCurrentAccountController::class, 'destroy'])->name('distributor-clients.current-accounts.destroy');
    Route::delete('distributor-clients/{distributorClient}/current-accounts', [DistributorCurrentAccountController::class, 'destroyAll'])->name('distributor-clients.current-accounts.destroy-all');
    Route::post('distributor-clients/{distributorClient}/current-accounts/create-from-technical-record/{technicalRecord}', [DistributorCurrentAccountController::class, 'createFromTechnicalRecord'])->name('distributor-clients.current-accounts.create-from-technical-record');
    Route::get('distributor-clients/{distributorClient}/current-accounts/export-pdf', [DistributorCurrentAccountController::class, 'exportToPdf'])->name('distributor-clients.current-accounts.export-pdf');
    
    // CRUD de presupuestos de distribuidores
    Route::get('distributor-quotations', [DistributorQuotationController::class, 'index'])->name('distributor-quotations.index');
    Route::get('distributor-quotations/create', [DistributorQuotationController::class, 'createSelectClient'])->name('distributor-quotations.create');
    Route::resource('distributor-clients.quotations', DistributorQuotationController::class);
    Route::get('distributor-clients/{distributorClient}/quotations/{quotation}/export-pdf', [DistributorQuotationController::class, 'exportToPdf'])->name('distributor-clients.quotations.export-pdf');
    Route::post('distributor-clients/{distributorClient}/quotations/{quotation}/change-status', [DistributorQuotationController::class, 'changeStatus'])->name('distributor-clients.quotations.change-status');

    
    // Ruta para ejecutar comandos Artisan
    Route::post('artisan', [ArtisanController::class, 'executeCommand'])->name('artisan.execute');
    
    // CRUD de fichas técnicas de distribuidores
    Route::resource('distributor-clients.technical-records', DistributorTechnicalRecordController::class);
Route::get('/api/supplier-inventories/search', [App\Http\Controllers\SupplierInventoryController::class, 'search'])->name('api.supplier-inventories.search');
Route::get('/api/supplier-inventories/get-product', [App\Http\Controllers\SupplierInventoryController::class, 'getProduct'])->name('api.supplier-inventories.get-product');
    Route::post('distributor-clients/{distributorClient}/technical-records/{distributorTechnicalRecord}/delete-photo',
        [DistributorTechnicalRecordController::class, 'deletePhoto'])
        ->name('distributor-clients.technical-records.delete-photo');
    Route::get('distributor-clients/{distributorClient}/technical-records/{distributorTechnicalRecord}/remito',
        [DistributorTechnicalRecordController::class, 'generateRemito'])
        ->name('distributor-clients.technical-records.remito');

    // CRUD de inventario de proveedores
    Route::resource('supplier-inventories', SupplierInventoryController::class);
    Route::post('supplier-inventories/{supplierInventory}/adjust-stock', [SupplierInventoryController::class, 'adjustStock'])
        ->name('supplier-inventories.adjust-stock');
    Route::get('supplier-inventories/export/excel', [SupplierInventoryController::class, 'exportToExcel'])
        ->name('supplier-inventories.export-excel');
    Route::get('supplier-inventories/export/lista-mayorista', [SupplierInventoryController::class, 'exportListaMayorista'])
        ->name('supplier-inventories.export-lista-mayorista');
    Route::get('supplier-inventories/export/lista-minorista', [SupplierInventoryController::class, 'exportListaMinorista'])
        ->name('supplier-inventories.export-lista-minorista');

    // CRUD de categorías
    Route::resource('categories', CategoryController::class);

    // CRUD de marcas
    Route::resource('brands', BrandController::class); 

    // CRUD de distributor brands
    Route::resource('distributor_brands', \App\Http\Controllers\DistributorBrandController::class);

    // CRUD de distributor categories
    Route::resource('distributor_categories', \App\Http\Controllers\DistributorCategoryController::class);

    // CRUD de proveedores
    Route::resource('suppliers', \App\Http\Controllers\SupplierController::class);
    Route::patch('suppliers/{supplier}/toggle-status', [\App\Http\Controllers\SupplierController::class, 'toggleStatus'])
        ->name('suppliers.toggle-status');
    Route::post('suppliers/{id}/restore', [\App\Http\Controllers\SupplierController::class, 'restore'])
        ->name('suppliers.restore');

    // Rutas para compras de proveedores
    Route::get('suppliers/{supplier}/create-purchase', [\App\Http\Controllers\SupplierController::class, 'createPurchase'])
        ->name('suppliers.create-purchase');
    Route::post('suppliers/{supplier}/store-purchase', [\App\Http\Controllers\SupplierController::class, 'storePurchase'])
        ->name('suppliers.store-purchase');
    Route::get('suppliers/{supplier}/edit-purchase/{purchase}', [\App\Http\Controllers\SupplierController::class, 'editPurchase'])
        ->name('suppliers.edit-purchase');
    Route::put('suppliers/{supplier}/update-purchase/{purchase}', [\App\Http\Controllers\SupplierController::class, 'updatePurchase'])
        ->name('suppliers.update-purchase');

    // CRUD de proveedores de peluquería
    Route::resource('hairdressing-suppliers', \App\Http\Controllers\HairdressingSupplierController::class);
    Route::patch('hairdressing-suppliers/{hairdressingSupplier}/toggle-status', [\App\Http\Controllers\HairdressingSupplierController::class, 'toggleStatus'])
        ->name('hairdressing-suppliers.toggle-status');
    Route::post('hairdressing-suppliers/{id}/restore', [\App\Http\Controllers\HairdressingSupplierController::class, 'restore'])
        ->name('hairdressing-suppliers.restore');

    // Rutas para compras de proveedores de peluquería
    Route::get('hairdressing-suppliers/{hairdressingSupplier}/create-purchase', [\App\Http\Controllers\HairdressingSupplierController::class, 'createPurchase'])
        ->name('hairdressing-suppliers.create-purchase');
    Route::post('hairdressing-suppliers/{hairdressingSupplier}/store-purchase', [\App\Http\Controllers\HairdressingSupplierController::class, 'storePurchase'])
        ->name('hairdressing-suppliers.store-purchase');
    Route::get('hairdressing-suppliers/{hairdressingSupplier}/edit-purchase/{purchase}', [\App\Http\Controllers\HairdressingSupplierController::class, 'editPurchase'])
        ->name('hairdressing-suppliers.edit-purchase');
    Route::put('hairdressing-suppliers/{hairdressingSupplier}/update-purchase/{purchase}', [\App\Http\Controllers\HairdressingSupplierController::class, 'updatePurchase'])
        ->name('hairdressing-suppliers.update-purchase');

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

    // Módulo de Ventas por Día - Distribuidora
    Route::get('daily-sales', [DailySalesController::class, 'index'])->name('daily-sales.index');
    Route::get('daily-sales/chart-data', [DailySalesController::class, 'getChartData'])->name('daily-sales.chart-data');
    Route::get('daily-sales/export-pdf', [DailySalesController::class, 'exportPdf'])->name('daily-sales.export-pdf');

    // Módulo de Ventas por Día - Peluquería
    Route::get('hairdressing-daily-sales', [HairdressingDailySalesController::class, 'index'])->name('hairdressing-daily-sales.index');
    Route::get('hairdressing-daily-sales/chart-data', [HairdressingDailySalesController::class, 'getChartData'])->name('hairdressing-daily-sales.chart-data');
    Route::get('hairdressing-daily-sales/export-pdf', [HairdressingDailySalesController::class, 'exportPdf'])->name('hairdressing-daily-sales.export-pdf');

});
