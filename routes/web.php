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
use App\Http\Controllers\DashboardController;   
use App\Http\Controllers\ItemCategoryController;
use App\Http\Controllers\UserRoleController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\MaterialUsageController;


use App\Models\StockTransaction;
use App\Models\StockLocation;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/dashboard', DashboardController::class)->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::middleware(['permission:manage users'])->group(function () {
        
        Route::resource('users', UserRoleController::class)->except(['show']);

        Route::resource('roles', RoleController::class);
    });



    // Client Management
    Route::resource('clients', ClientController::class);

    // Quotation Management
    Route::post('/api/quotation/resources', [\App\Http\Controllers\Api\QuotationCalculatorController::class, 'getProjectResources'])->name('api.quotation.resources');
    Route::post('/api/quotation/recalculate', [\App\Http\Controllers\Api\QuotationCalculatorController::class, 'recalculateAhsPrices'])->name('api.quotation.recalculate');

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
    // Inventory Import Step 1 (Show Form)
    Route::get('inventory-items/import', [\App\Http\Controllers\InventoryItemController::class, 'showImportForm'])->name('inventory-items.importForm');
    // Inventory Import Step 2 (Analyze File)
    Route::post('inventory-items/import/analyze', [\App\Http\Controllers\InventoryItemController::class, 'analyzeImport'])->name('inventory-items.import.analyze');
    // Inventory Import Step 3 (Show Confirmation)
    Route::get('inventory-items/import/confirm', [\App\Http\Controllers\InventoryItemController::class, 'showConfirmForm'])->name('inventory-items.import.confirm');
    // Inventory Import Step 4 (Process File)
    Route::post('inventory-items/import/process', [\App\Http\Controllers\InventoryItemController::class, 'processImport'])->name('inventory-items.import.process');
    Route::get('inventory-items/export', [\App\Http\Controllers\InventoryItemController::class, 'export'])->name('inventory-items.export');

    Route::resource('inventory-items', InventoryItemController::class);

    // Receipt
    Route::resource('goods-receipts', \App\Http\Controllers\GoodsReceiptController::class)->except(['destroy']);
    // This is the route for your "Create Back-Order / Close Short" logic
    Route::post('/goods-receipts/{goodsReceipt}/post', [\App\Http\Controllers\GoodsReceiptController::class, 'postReceipt'])
        ->name('goods-receipts.post');

    // This is the route for the "Force Close" button on the PO
    Route::post('/purchase-orders/{purchaseOrder}/force-close', [\App\Http\Controllers\PurchaseOrderController::class, 'forceClose'])
        ->name('purchase-orders.force-close');

    // Supplier Management
    Route::resource('suppliers', SupplierController::class);

    // PO Management
    Route::resource('purchase-orders', PurchaseOrderController::class);
    Route::post('/purchase-orders/{purchaseOrder}/status', [PurchaseOrderController::class, 'updateStatus'])->name('purchase-orders.updateStatus');
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

    // Stock Balance
    Route::get('/reports/stock-balance', [ReportController::class, 'stockBalanceReport'])
        ->name('reports.stock_balance');

    // Stock Location
    Route::resource('stock-locations', \App\Http\Controllers\StockLocationController::class);

    // Stock Adjustment
    Route::resource('stock-adjustments', \App\Http\Controllers\StockAdjustmentController::class)
         ->only(['index', 'create', 'store', 'show']);

    // Internal Transfers
    Route::resource('internal-transfers', \App\Http\Controllers\InternalTransferController::class);

    // Transfer Shipments
    Route::resource('transfer-shipments', \App\Http\Controllers\TransferShipmentController::class)->except(['create', 'store']);
    Route::get('internal-transfers/{internalTransfer}/ship', [\App\Http\Controllers\TransferShipmentController::class, 'create'])->name('transfer-shipments.create');
    Route::post('internal-transfers/{internalTransfer}/ship', [\App\Http\Controllers\TransferShipmentController::class, 'store'])->name('transfer-shipments.store');

    // Transfer Receipts
    Route::resource('transfer-receipts', \App\Http\Controllers\TransferReceiptController::class)->except(['create', 'store']);
    Route::get('transfer-shipments/{transferShipment}/receive', [\App\Http\Controllers\TransferReceiptController::class, 'create'])->name('transfer-receipts.create');
    Route::post('transfer-shipments/{transferShipment}/receive', [\App\Http\Controllers\TransferReceiptController::class, 'store'])->name('transfer-receipts.store');

    // API Route to get stock balances for a location
    Route::get('/web-api/locations/{stockLocation}/stock', function (StockLocation $stockLocation, Request $request) {
        
        // 1. Get ALL balances for the selected location and key them by item_id
        $stockBalances = StockTransaction::where('stock_location_id', $stockLocation->id)
            ->select('inventory_item_id', DB::raw('SUM(quantity) as on_hand'))
            ->groupBy('inventory_item_id')
            ->get()
            ->keyBy('inventory_item_id');

        // 2. Start a query for all Inventory Items
        $query = \App\Models\InventoryItem::query();

        // 3. Apply the user's filter
        if (!$request->boolean('include_zero')) {
            // IF 'include_zero' is FALSE:
            // Only get items that actually have a positive balance at this location.
            $itemIdsWithStock = $stockBalances->filter(fn($balance) => $balance->on_hand > 0.001)
                                            ->pluck('inventory_item_id');
            $query->whereIn('id', $itemIdsWithStock);
        }
        // IF 'include_zero' is TRUE:
        // The query remains `InventoryItem::query()`, so it will fetch ALL master items.

        $items = $query->orderBy('item_code')->get();

        // 4. Combine the data into a clean array
        $reportData = $items->map(function ($item) use ($stockBalances) {
            $balance = $stockBalances->get($item->id); // Find the balance (if it exists)
            return [
                'inventory_item_id' => $item->id,
                'item_code' => $item->item_code,
                'item_name' => $item->item_name,
                'uom' => $item->uom,
                'on_hand' => $balance ? (float)$balance->on_hand : 0, // Get the balance or default to 0
            ];
        });

        return response()->json($reportData);
        
    })->name('api.locations.stock');

    // Stock Summary
    Route::get('/stock-overview', [\App\Http\Controllers\StockOverviewController::class, 'index'])->name('stock-overview.index');
    Route::get('/stock-overview/{stockLocation}', [\App\Http\Controllers\StockOverviewController::class, 'show'])->name('stock-overview.show');

    // Material Usage
    Route::get('/material-usage', [MaterialUsageController::class, 'index'])->name('material-usage.index');

    // Labor
    Route::resource('labor-rates', LaborRateController::class);

    // AHS
    // AHS Import Step 1 (Show Form)
    Route::get('ahs-library/import', [\App\Http\Controllers\UnitRateAnalysisController::class, 'showImportForm'])->name('ahs-library.importForm');
    // AHS Import Step 2 (Analyze File)
    Route::post('ahs-library/import/analyze', [\App\Http\Controllers\UnitRateAnalysisController::class, 'analyzeImport'])->name('ahs-library.import.analyze');
    // AHS Import Step 3 (Show Confirmation)
    Route::get('ahs-library/import/confirm', [\App\Http\Controllers\UnitRateAnalysisController::class, 'showConfirmForm'])->name('ahs-library.import.confirm');
    // AHS Import Step 4 (Process File)
    Route::post('ahs-library/import/process', [\App\Http\Controllers\UnitRateAnalysisController::class, 'processImport'])->name('ahs-library.import.process');
    Route::get('ahs-library/export', [\App\Http\Controllers\UnitRateAnalysisController::class, 'export'])->name('ahs-library.export');
    Route::resource('ahs-library', UnitRateAnalysisController::class);

    // Material Request
    Route::resource('material-requests', MaterialRequestController::class);
    Route::post('/material-requests/{materialRequest}/status', [MaterialRequestController::class, 'updateStatus'])->name('material-requests.updateStatus');
    Route::post('/material-requests/{materialRequest}/create-po', [MaterialRequestController::class, 'createPurchaseOrder'])->name('material-requests.createPO');
    Route::post('/material-requests/{materialRequest}/create-transfer', [MaterialRequestController::class, 'createTransfer'])->name('material-requests.createTransfer');

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

    // Item Category Management
    Route::resource('item-categories', \App\Http\Controllers\ItemCategoryController::class);

});

require __DIR__.'/auth.php';
