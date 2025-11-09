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
use App\Http\Controllers\DistributorDiscountController;
use App\Http\Controllers\PriceIncreaseController;
use App\Http\Controllers\PriceDecreaseController;
use App\Http\Controllers\CostDecreaseController;
use App\Http\Controllers\CostIncreaseController;
use App\Http\Controllers\StockAlertController;
use App\Http\Controllers\ClientCurrentAccountController;
use App\Http\Controllers\AfipInvoiceController;
use App\Http\Controllers\AfipConfigurationController;
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

    // CRUD de presupuestos para clientes no registrados
    Route::resource('distributor-quotation-no-clients', \App\Http\Controllers\DistributorQuotationNoClientController::class);
    Route::get('distributor-quotation-no-clients/{distributorQuotationNoClient}/export-pdf', [\App\Http\Controllers\DistributorQuotationNoClientController::class, 'exportToPdf'])->name('distributor-quotation-no-clients.export-pdf');
    Route::post('distributor-quotation-no-clients/{distributorQuotationNoClient}/change-status', [\App\Http\Controllers\DistributorQuotationNoClientController::class, 'changeStatus'])->name('distributor-quotation-no-clients.change-status');

    // CRUD de descuentos de distribuidores
    Route::resource('distributor-discounts', DistributorDiscountController::class);
    Route::patch('distributor-discounts/{distributorDiscount}/toggle-status', [DistributorDiscountController::class, 'toggleStatus'])->name('distributor-discounts.toggle-status');
    Route::get('distributor-discounts/available-discounts', [DistributorDiscountController::class, 'getAvailableDiscounts'])->name('distributor-discounts.available');
    
    // CRUD de aumentos de precios
    Route::get('price-increases', [PriceIncreaseController::class, 'index'])->name('price-increases.index');
    Route::get('price-increases/create', [PriceIncreaseController::class, 'create'])->name('price-increases.create');
    Route::post('price-increases/preview', [PriceIncreaseController::class, 'preview'])->name('price-increases.preview');
    Route::post('price-increases', [PriceIncreaseController::class, 'store'])->name('price-increases.store');
    Route::get('price-increases/{priceIncrease}', [PriceIncreaseController::class, 'show'])->name('price-increases.show');
    
    // CRUD de disminuciones de precios
    Route::get('price-decreases', [PriceDecreaseController::class, 'index'])->name('price-decreases.index');
    Route::get('price-decreases/create', [PriceDecreaseController::class, 'create'])->name('price-decreases.create');
    Route::post('price-decreases/preview', [PriceDecreaseController::class, 'preview'])->name('price-decreases.preview');
    Route::post('price-decreases', [PriceDecreaseController::class, 'store'])->name('price-decreases.store');
    Route::get('price-decreases/{priceDecrease}', [PriceDecreaseController::class, 'show'])->name('price-decreases.show');
    
    // CRUD de disminuciones de costos
    Route::get('cost-decreases', [CostDecreaseController::class, 'index'])->name('cost-decreases.index');
    Route::get('cost-decreases/create', [CostDecreaseController::class, 'create'])->name('cost-decreases.create');
    Route::post('cost-decreases/preview', [CostDecreaseController::class, 'preview'])->name('cost-decreases.preview');
    Route::post('cost-decreases', [CostDecreaseController::class, 'store'])->name('cost-decreases.store');
    Route::get('cost-decreases/{costDecrease}', [CostDecreaseController::class, 'show'])->name('cost-decreases.show');
    
    // CRUD de aumentos de costos
    Route::get('cost-increases', [CostIncreaseController::class, 'index'])->name('cost-increases.index');
    Route::get('cost-increases/create', [CostIncreaseController::class, 'create'])->name('cost-increases.create');
    Route::post('cost-increases/preview', [CostIncreaseController::class, 'preview'])->name('cost-increases.preview');
    Route::post('cost-increases', [CostIncreaseController::class, 'store'])->name('cost-increases.store');
    Route::get('cost-increases/{costIncrease}', [CostIncreaseController::class, 'show'])->name('cost-increases.show');
    
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
    Route::delete('suppliers/{supplier}/destroy-purchase/{purchase}', [\App\Http\Controllers\SupplierController::class, 'destroyPurchase'])
        ->name('suppliers.destroy-purchase');
    Route::get('suppliers/{supplier}/get-receipt-total', [\App\Http\Controllers\SupplierController::class, 'getReceiptTotal'])
        ->name('suppliers.get-receipt-total');
    
    // Cuentas corrientes de proveedores distribuidora
    Route::get('suppliers/{supplier}/current-account', [\App\Http\Controllers\SupplierController::class, 'showCurrentAccount'])
        ->name('suppliers.current-account.show');

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
    Route::delete('hairdressing-suppliers/{hairdressingSupplier}/destroy-purchase/{purchase}', [\App\Http\Controllers\HairdressingSupplierController::class, 'destroyPurchase'])
        ->name('hairdressing-suppliers.destroy-purchase');
    Route::get('hairdressing-suppliers/{hairdressingSupplier}/get-receipt-total', [\App\Http\Controllers\HairdressingSupplierController::class, 'getReceiptTotal'])
        ->name('hairdressing-suppliers.get-receipt-total');
    
    // Cuentas corrientes de proveedores peluquería
    Route::get('hairdressing-suppliers/{hairdressingSupplier}/current-account', [\App\Http\Controllers\HairdressingSupplierController::class, 'showCurrentAccount'])
        ->name('hairdressing-suppliers.current-account.show');
    
    // Clientes No Frecuentes
    Route::resource('cliente-no-frecuentes', \App\Http\Controllers\ClienteNoFrecuenteController::class);
    
    // Clientes No Frecuentes - Distribuidora
    Route::resource('distributor-cliente-no-frecuentes', \App\Http\Controllers\DistributorClienteNoFrecuenteController::class);
    Route::get('distributor-cliente-no-frecuentes/{distributorClienteNoFrecuente}/remito', [\App\Http\Controllers\DistributorClienteNoFrecuenteController::class, 'generateRemito'])->name('distributor-cliente-no-frecuentes.remito');
    
    // Detalles de ventas diarias de peluquería
    Route::get('hairdressing-daily-sales/detail', [\App\Http\Controllers\HairdressingDailySalesController::class, 'detail'])
        ->name('hairdressing-daily-sales.detail');

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
    Route::get('daily-sales/detail/{category}', [DailySalesController::class, 'showDetail'])->name('daily-sales.detail');
    
    Route::get('daily-sales/export-pdf', [DailySalesController::class, 'exportPdf'])->name('daily-sales.export-pdf');

    // Módulo de Ventas por Día - Peluquería
    Route::get('hairdressing-daily-sales', [HairdressingDailySalesController::class, 'index'])->name('hairdressing-daily-sales.index');
    
    Route::get('hairdressing-daily-sales/export-pdf', [HairdressingDailySalesController::class, 'exportPdf'])->name('hairdressing-daily-sales.export-pdf');

    // Módulo de Facturación AFIP
    Route::prefix('facturacion')->name('facturacion.')->group(function () {
        // Rutas principales de facturas
        Route::get('/', [AfipInvoiceController::class, 'index'])->name('index');
        Route::get('/create', [AfipInvoiceController::class, 'create'])->name('create');
        Route::post('/', [AfipInvoiceController::class, 'store'])->name('store');
        
        // Configuración de AFIP (debe ir antes de las rutas con parámetros)
        Route::get('/configuration', [AfipConfigurationController::class, 'index'])->name('configuration');
        Route::post('/configuration', [AfipConfigurationController::class, 'update'])->name('configuration.update');
        Route::post('/configuration/validate', [AfipConfigurationController::class, 'validateConfiguration'])->name('configuration.validate');
        Route::post('/configuration/taxpayer-info', [AfipConfigurationController::class, 'getTaxpayerInfo'])->name('configuration.taxpayer-info');
        Route::post('/configuration/last-voucher', [AfipConfigurationController::class, 'getLastVoucher'])->name('configuration.last-voucher');
        
        // APIs para obtener información
        Route::get('/clients/{client}/info', [AfipInvoiceController::class, 'getClientInfo'])->name('clients.info');
        Route::get('/products/{product}/info', [AfipInvoiceController::class, 'getProductInfo'])->name('products.info');
        Route::get('/clients/{clientId}/purchases', [AfipInvoiceController::class, 'getClientPurchases'])->name('clients.purchases');
        Route::get('/technical-records/{technicalRecordId}/products', [AfipInvoiceController::class, 'getTechnicalRecordProducts'])->name('technical-records.products');
        
        // Rutas con parámetros (deben ir al final)
        Route::get('/{facturacion}', [AfipInvoiceController::class, 'show'])->name('show');
        Route::post('/{facturacion}/send', [AfipInvoiceController::class, 'sendToAfip'])->name('send');
        Route::post('/{facturacion}/cancel', [AfipInvoiceController::class, 'cancel'])->name('cancel');
        Route::get('/{facturacion}/download-pdf', [AfipInvoiceController::class, 'downloadPdf'])->name('download-pdf');
    });

});
