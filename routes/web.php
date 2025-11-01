<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\QuotationController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ProgressUpdateController;
use App\Http\Controllers\InventoryItemController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\PurchaseOrderController;
use App\Http\Controllers\BillingController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\StockLedgerController;
use App\Http\Controllers\LaborRateController;
use App\Http\Controllers\UnitRateAnalysisController;
use App\Http\Controllers\MaterialRequestController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\EquipmentController;
use App\Http\Controllers\WorkTypeController;
use App\Http\Controllers\WorkItemController;


use Illuminate\Support\Facades\Route;


Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Client Management
    Route::resource('clients', ClientController::class);

    // Quotation Management
    Route::resource('clients', ClientController::class);
    Route::resource('quotations', QuotationController::class);
    Route::post('/quotations/{quotation}/status', [QuotationController::class, 'updateStatus'])
     ->name('quotations.updateStatus');

    // Project Management
    Route::resource('projects', ProjectController::class);
    Route::get('/projects/{project}/progress/create', [ProgressUpdateController::class, 'create'])->name('progress.create');
    Route::post('/projects/{project}/progress', [ProgressUpdateController::class, 'store'])->name('progress.store');
    Route::post('/projects/{project}/complete', [ProjectController::class, 'markAsComplete'])->name('projects.complete');
    Route::post('/projects/{project}/close', [ProjectController::class, 'markAsClosed'])->name('projects.close');

    // Progress Management
    Route::get('/progress/{quotation_item}/history', [ProgressUpdateController::class, 'history'])->name('progress.history');

    // Inventory Management
    Route::resource('inventory-items', InventoryItemController::class);

    // Supplier Management
    Route::resource('suppliers', SupplierController::class);

    // PO Management
    Route::resource('purchase-orders', PurchaseOrderController::class);
    Route::post('/purchase-orders/{purchaseOrder}/status', [PurchaseOrderController::class, 'updateStatus'])->name('purchase-orders.updateStatus');

    Route::get('/purchase-orders/{purchaseOrder}/receive', [PurchaseOrderController::class, 'showReceiveForm'])->name('purchase-orders.receiveForm');
    Route::post('/purchase-orders/{purchaseOrder}/receive', [PurchaseOrderController::class, 'processReceive'])->name('purchase-orders.receive');

    // Billings
    Route::resource('billings', BillingController::class);
    Route::post('/billings/{billing}/status', [BillingController::class, 'updateStatus'])
     ->name('billings.updateStatus');

     // Invoices
     Route::resource('invoices', InvoiceController::class);
     Route::post('/invoices/{invoice}/status', [InvoiceController::class, 'updateStatus'])->name('invoices.updateStatus');
     Route::post('/invoices/{invoice}/payments', [PaymentController::class, 'store'])->name('payments.store');

    // Payments
    Route::post('/invoices/{invoice}/payments', [PaymentController::class, 'store'])->name('payments.store');

    // Stock Ledger
    Route::get('/stock-ledger', [StockLedgerController::class, 'index'])->name('stock-ledger.index');

    // Labor
    Route::resource('labor-rates', LaborRateController::class);

    // AHS
    Route::resource('ahs-library', UnitRateAnalysisController::class);

    // Material Request
    Route::resource('material-requests', MaterialRequestController::class);
    Route::post('/material-requests/{materialRequest}/status', [MaterialRequestController::class, 'updateStatus'])->name('material-requests.updateStatus');
    Route::post('/material-requests/{materialRequest}/create-po', [MaterialRequestController::class, 'createPurchaseOrder'])->name('material-requests.createPO');

    // Reporting Routes
    Route::get('/reports/material-flow/{project}', [ReportController::class, 'materialFlowReport'])
    ->name('reports.material_flow');

    Route::get('/reports/project-performance/{project}', [ReportController::class, 'projectPerformanceReport'])
    ->name('reports.project_performance');

    // Equipment
    Route::resource('equipment', EquipmentController::class);

    // Work Library - Types
    Route::resource('work-types', WorkTypeController::class);

    // Work Library - Items
    Route::resource('work-items', WorkItemController::class);

    // S Curve (Time Scheduler)
    Route::get('/projects/{project}/scheduler', [ProjectController::class, 'showScheduler'])->name('projects.scheduler');
    Route::post('/projects/{project}/scheduler', [ProjectController::class, 'storeScheduler'])->name('projects.scheduler.store');

});

require __DIR__.'/auth.php';
