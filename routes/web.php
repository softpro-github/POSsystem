<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SystemHealthController;
use App\Http\Controllers\Inventory\CategoryController;
use App\Http\Controllers\Inventory\ProductController;
use App\Http\Controllers\Inventory\BrandController;
use App\Http\Controllers\Inventory\UnitController;
use App\Http\Controllers\Inventory\LabelController;
use App\Http\Controllers\Inventory\StockController;
use App\Http\Controllers\Inventory\StockReconciliationController;
use App\Http\Controllers\Inventory\StockTransferController;
use App\Http\Controllers\Settings\AdjustmentReasonController;
use App\Http\Controllers\Pos\SaleController as PosSaleController;
use App\Http\Controllers\Pos\HeldOrderController;
use App\Http\Controllers\Pos\SyncLogController;
use App\Http\Controllers\Sales\SaleReturnController;
use App\Http\Controllers\Sales\SaleHistoryController;
use App\Http\Controllers\Customers\CustomerController;
use App\Http\Controllers\Suppliers\SupplierController;
use App\Http\Controllers\Suppliers\SupplierPaymentController;
use App\Http\Controllers\Purchases\PurchaseOrderController;
use App\Http\Controllers\Purchases\PurchaseReturnController;
use App\Http\Controllers\Warranty\WarrantyController;
use App\Http\Controllers\Warranty\WarrantyClaimController;
use App\Http\Controllers\Repairs\RepairTicketController;
use App\Http\Controllers\Reports\ReportController;
use App\Http\Controllers\Reports\SavedReportController;
use App\Http\Controllers\Users\UserController;
use App\Http\Controllers\Users\RoleController;
use App\Http\Controllers\DiscountRules\DiscountRuleController;
use App\Http\Controllers\Settings\ReturnReasonController;
use App\Http\Controllers\Settings\CashMismatchReasonController;
use App\Http\Controllers\Accounting\OpeningBalanceController;
use App\Http\Controllers\Accounting\AccountingReportController;
use App\Http\Controllers\Accounting\AccountController as ChartOfAccountsController;
use App\Http\Controllers\Accounting\JournalEntryController;
use App\Http\Controllers\Accounting\FiscalPeriodController;
use App\Http\Controllers\Accounting\TaxComponentController;
use App\Http\Controllers\Accounting\TaxGroupController;
use App\Http\Controllers\Shifts\ShiftController;
use App\Http\Controllers\Expenses\ExpenseController;
use App\Http\Controllers\Expenses\ExpenseCategoryController;
use App\Http\Controllers\Stores\StoreController;
use App\Http\Controllers\Stores\StoreSwitchController;
use App\Http\Controllers\Stores\TerminalController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\LocaleController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

// No auth middleware — a guest on the login page can also change display language.
Route::post('/locale', [LocaleController::class, 'update'])->name('locale.update');

// Served via a route (not a static /build/sw.js URL) so the response can carry
// Service-Worker-Allowed: / — widening the worker's control scope to the whole
// site, since it physically builds into /build/ but needs to control /pos.
Route::get('/service-worker.js', function () {
    return response()->file(public_path('build/sw.js'), [
        'Content-Type' => 'application/javascript',
        'Service-Worker-Allowed' => '/',
        'Cache-Control' => 'no-cache',
    ]);
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/today-summary', [DashboardController::class, 'todaySummary'])->name('dashboard.today-summary');

    Route::get('/help', [\App\Http\Controllers\DocsController::class, 'index'])->name('docs.index');
    Route::get('/help/{page}', [\App\Http\Controllers\DocsController::class, 'show'])->name('docs.show');

    Route::get('/search', [\App\Http\Controllers\SearchController::class, 'index'])->name('search.index');

    Route::get('/notifications', [\App\Http\Controllers\NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/recent', [\App\Http\Controllers\NotificationController::class, 'recent'])->name('notifications.recent');
    Route::get('/notifications/unread-count', [\App\Http\Controllers\NotificationController::class, 'unreadCount'])->name('notifications.unread-count');
    Route::post('/notifications/{notification}/read', [\App\Http\Controllers\NotificationController::class, 'markRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [\App\Http\Controllers\NotificationController::class, 'markAllRead'])->name('notifications.read-all');

    Route::post('/print-jobs', [\App\Http\Controllers\PrintJobController::class, 'store'])->name('print-jobs.store');
    Route::post('/print-jobs/{printJob}/closed', [\App\Http\Controllers\PrintJobController::class, 'markClosed'])->name('print-jobs.closed');
    Route::get('/print-jobs', [\App\Http\Controllers\PrintJobController::class, 'index'])->name('print-jobs.index');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::middleware('permission:manage products')->group(function () {
        Route::resource('inventory/products', ProductController::class)->except(['show'])->names('products');
        Route::resource('inventory/categories', CategoryController::class)->except(['show'])->names('categories');
        Route::resource('inventory/brands', BrandController::class)->except(['show'])->names('brands');
        Route::resource('inventory/units', UnitController::class)->except(['show'])->names('units');
        Route::get('/inventory/labels', [LabelController::class, 'create'])->name('labels.create');
        Route::get('/inventory/labels/print', [LabelController::class, 'print'])->name('labels.print');
        Route::get('/inventory/labels/from-purchase-order/{purchaseOrder}', [LabelController::class, 'fromPurchaseOrder'])->name('labels.from-purchase-order');
    });

    Route::middleware('permission:manage discount rules')->group(function () {
        Route::resource('discount-rules', DiscountRuleController::class)->except(['show']);
    });

    Route::middleware('permission:manage stock')->group(function () {
        Route::post('/inventory/stock/adjust', [StockController::class, 'adjust'])->name('stock.adjust');
        Route::resource('reconciliations', StockReconciliationController::class)->only(['index', 'create', 'store', 'show']);
        Route::resource('transfers', StockTransferController::class)->only(['index', 'create', 'store', 'show']);
        Route::post('/transfers/{transfer}/receive', [StockTransferController::class, 'receive'])->name('transfers.receive');
    });

    Route::middleware('permission:access pos')->group(function () {
        Route::get('/shifts/open', [ShiftController::class, 'openForm'])->name('shifts.open-form');
        Route::post('/shifts/open', [ShiftController::class, 'open'])->name('shifts.open');
        Route::get('/shifts/current', [ShiftController::class, 'current'])->name('shifts.current');
        Route::post('/shifts/close', [ShiftController::class, 'close'])->name('shifts.close');

        Route::get('/pos', [PosSaleController::class, 'create'])->name('pos.index');
        Route::post('/pos/hold', [PosSaleController::class, 'hold'])->name('pos.hold');
        Route::post('/pos/sync', [PosSaleController::class, 'sync'])->name('pos.sync');
        Route::get('/pos/held', [HeldOrderController::class, 'index'])->name('pos.held');
        Route::delete('/pos/held/{sale}', [HeldOrderController::class, 'destroy'])->name('pos.held.destroy');
    });

    // Shift History: every POS-using role sees the same full list, matching the
    // Hyper POS reference (not scoped to "my shifts only" for cashiers) — Managers
    // reach it via 'manage shifts' even without 'access pos'.
    Route::middleware('permission:access pos|manage shifts')->group(function () {
        Route::get('/shifts', [ShiftController::class, 'index'])->name('shifts.index');
        Route::get('/shifts/{shift}', [ShiftController::class, 'show'])->name('shifts.show');
    });

    Route::middleware('permission:view reports')->group(function () {
        Route::get('/pos/sync-log', [SyncLogController::class, 'index'])->name('pos.sync-log');
    });

    Route::middleware('permission:view sales')->group(function () {
        Route::get('/sales', [SaleHistoryController::class, 'index'])->name('sales.index');
        Route::get('/sales/{sale}', [SaleHistoryController::class, 'show'])->name('sales.show');
        Route::get('/sales/{sale}/receipt', [SaleHistoryController::class, 'receipt'])->name('sales.receipt');
        Route::get('/sales/{sale}/returns/create', [SaleReturnController::class, 'create'])
            ->middleware('permission:void sales')
            ->name('sales.returns.create');
        Route::post('/sales/{sale}/returns', [SaleReturnController::class, 'store'])
            ->middleware('permission:void sales')
            ->name('sales.returns.store');
        Route::post('/sales/{sale}/void', [SaleHistoryController::class, 'void'])
            ->middleware('permission:void sales')
            ->name('sales.void');
    });

    Route::middleware('permission:manage customers')->group(function () {
        Route::get('/customers/create', [CustomerController::class, 'create'])->name('customers.create');
        Route::post('/customers', [CustomerController::class, 'store'])->name('customers.store');
        Route::get('/customers/{customer}/edit', [CustomerController::class, 'edit'])->name('customers.edit');
        Route::put('/customers/{customer}', [CustomerController::class, 'update'])->name('customers.update');
        Route::delete('/customers/{customer}', [CustomerController::class, 'destroy'])->name('customers.destroy');
    });

    Route::middleware('permission:view customers')->group(function () {
        Route::get('/customers', [CustomerController::class, 'index'])->name('customers.index');
        Route::get('/customers/{customer}', [CustomerController::class, 'show'])->name('customers.show');
    });

    Route::middleware('permission:manage suppliers')->group(function () {
        Route::resource('suppliers', SupplierController::class);
        Route::post('/suppliers/{supplier}/payments', [SupplierPaymentController::class, 'store'])->name('suppliers.payments.store');
    });

    Route::middleware('permission:manage purchase orders')->group(function () {
        Route::resource('purchase-orders', PurchaseOrderController::class)->only(['index', 'create', 'store', 'show']);
        Route::post('/purchase-orders/{purchaseOrder}/receive', [PurchaseOrderController::class, 'receive'])->name('purchase-orders.receive');
    });

    Route::middleware('permission:manage purchase returns')->group(function () {
        Route::get('/purchase-orders/{purchaseOrder}/returns/create', [PurchaseReturnController::class, 'create'])->name('purchase-orders.returns.create');
        Route::post('/purchase-orders/{purchaseOrder}/returns', [PurchaseReturnController::class, 'store'])->name('purchase-orders.returns.store');
    });

    Route::middleware('permission:manage warranties')->group(function () {
        Route::resource('warranties', WarrantyController::class)->except(['edit', 'update', 'destroy']);
        Route::post('/warranties/{warranty}/claims', [WarrantyClaimController::class, 'store'])->name('warranties.claims.store');
        Route::patch('/warranty-claims/{warrantyClaim}', [WarrantyClaimController::class, 'update'])->name('warranty-claims.update');
    });

    Route::middleware('permission:manage repairs')->group(function () {
        Route::resource('repair-tickets', RepairTicketController::class);
        Route::patch('/repair-tickets/{repairTicket}/status', [RepairTicketController::class, 'updateStatus'])->name('repair-tickets.status');
    });

    Route::middleware('permission:view reports')->group(function () {
        Route::get('/reports/sales', [ReportController::class, 'sales'])->name('reports.sales');
        Route::get('/reports/inventory', [ReportController::class, 'inventory'])->name('reports.inventory');
        Route::get('/reports/profit', [ReportController::class, 'profit'])->name('reports.profit');
        Route::get('/reports/warranty', [ReportController::class, 'warranty'])->name('reports.warranty');
        Route::get('/reports/repair', [ReportController::class, 'repair'])->name('reports.repair');
        Route::get('/saved-reports', [SavedReportController::class, 'index'])->name('saved-reports.index');
        Route::post('/saved-reports', [SavedReportController::class, 'store'])->name('saved-reports.store');
        Route::delete('/saved-reports/{savedReport}', [SavedReportController::class, 'destroy'])->name('saved-reports.destroy');
    });

    Route::middleware('permission:manage users')->group(function () {
        Route::resource('users', UserController::class)->except(['show']);
    });

    Route::middleware('permission:manage roles')->group(function () {
        Route::resource('roles', RoleController::class)->except(['show']);
    });

    Route::middleware('permission:view system health')->group(function () {
        Route::get('/system-health', [SystemHealthController::class, 'index'])->name('system-health.index');
    });

    Route::middleware('permission:manage settings')->group(function () {
        Route::get('/settings', [SettingsController::class, 'edit'])->name('settings.edit');
        Route::put('/settings', [SettingsController::class, 'update'])->name('settings.update');
        Route::resource('return-reasons', ReturnReasonController::class)->except(['show']);
        Route::resource('cash-mismatch-reasons', CashMismatchReasonController::class)->except(['show']);
        Route::resource('adjustment-reasons', AdjustmentReasonController::class)->except(['show']);
    });

    Route::middleware('permission:manage tax settings')->group(function () {
        Route::resource('tax-components', TaxComponentController::class)->except(['show']);
        Route::resource('tax-groups', TaxGroupController::class)->except(['show']);
    });

    Route::middleware('permission:manage stores')->group(function () {
        Route::resource('stores', StoreController::class)->except(['show']);
        Route::resource('terminals', TerminalController::class)->except(['show']);
        Route::post('/store-switch', [StoreSwitchController::class, 'switch'])->name('store.switch');
    });

    Route::middleware('permission:manage expenses')->group(function () {
        Route::resource('expenses', ExpenseController::class)->only(['index', 'create', 'store']);
        Route::resource('expense-categories', ExpenseCategoryController::class)->except(['show']);
    });

    Route::middleware('permission:manage chart of accounts')->group(function () {
        Route::get('/accounting/opening-balances', [OpeningBalanceController::class, 'edit'])->name('accounting.opening-balances.edit');
        Route::put('/accounting/opening-balances', [OpeningBalanceController::class, 'update'])->name('accounting.opening-balances.update');

        Route::resource('accounting/chart-of-accounts', ChartOfAccountsController::class)->except(['show'])
            ->names('accounting.accounts')
            ->parameters(['chart-of-accounts' => 'account']);

        Route::get('/accounting/fiscal-periods', [FiscalPeriodController::class, 'index'])->name('accounting.fiscal-periods.index');
        Route::post('/accounting/fiscal-periods/toggle', [FiscalPeriodController::class, 'toggle'])->name('accounting.fiscal-periods.toggle');
        Route::post('/accounting/fiscal-periods/close-year', [FiscalPeriodController::class, 'closeYear'])->name('accounting.fiscal-periods.close-year');
    });

    Route::middleware('permission:view accounting')->group(function () {
        Route::get('/accounting/trial-balance', [AccountingReportController::class, 'trialBalance'])->name('accounting.trial-balance');
        Route::get('/accounting/general-ledger', [AccountingReportController::class, 'generalLedger'])->name('accounting.general-ledger');
        Route::get('/accounting/profit-and-loss', [AccountingReportController::class, 'profitAndLoss'])->name('accounting.profit-and-loss');
        Route::get('/accounting/balance-sheet', [AccountingReportController::class, 'balanceSheet'])->name('accounting.balance-sheet');
        Route::get('/accounting/cash-flow', [AccountingReportController::class, 'cashFlow'])->name('accounting.cash-flow');
    });

    Route::get('/accounting/journal', [JournalEntryController::class, 'index'])
        ->middleware('permission:view accounting')->name('accounting.journal.index');
    Route::get('/accounting/journal/create', [JournalEntryController::class, 'create'])
        ->middleware('permission:manage chart of accounts')->name('accounting.journal.create');
    Route::post('/accounting/journal', [JournalEntryController::class, 'store'])
        ->middleware('permission:manage chart of accounts')->name('accounting.journal.store');
    Route::get('/accounting/journal/{journalEntry}', [JournalEntryController::class, 'show'])
        ->middleware('permission:view accounting')->name('accounting.journal.show');
    Route::post('/accounting/journal/{journalEntry}/reverse', [JournalEntryController::class, 'reverse'])
        ->middleware('permission:manage chart of accounts')->name('accounting.journal.reverse');
});

require __DIR__.'/auth.php';
