<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\POSDeviceController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DivisionController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\SyncLogController;
use App\Http\Controllers\POSLayoutController;
use App\Http\Controllers\POSLimitController;
use App\Http\Controllers\PurchaseOrderController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\CashierPrivilegeController;
use App\Http\Controllers\CompanyRoleController;
use App\Http\Controllers\NotificationController;


// Authentication Routes
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Note: API routes are now handled in routes/api.php



// Protected Routes
Route::middleware(['custom.auth'])->group(function () {
    // Dashboard
    Route::get('/', function () {
        return view('dashboard');
    })->name('layouts.admin');


    // Master Admin Only Routes
    Route::middleware(['master.admin'])->group(function () {
        Route::apiResource('companies', CompanyController::class);

        // Sales reset functionality for companies
        Route::delete('/admin/companies/{id}/reset-sales', [AdminController::class, 'resetCompanySales'])->name('companies.reset-sales');

        // Remove all company data functionality
        Route::delete('/admin/companies/{id}/remove-all-data', [AdminController::class, 'removeAllCompanyData'])->name('companies.remove-all-data');
    });

    // Admin Routes (Master Admin + Company Admins)
    Route::middleware(['admin'])->group(function () {
        Route::apiResource('brands', BrandController::class);
        Route::apiResource('branches', BranchController::class);
    });

    // Product Management Routes (Company Managers)
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::resource('categories', \App\Http\Controllers\Admin\CategoryController::class);
        Route::resource('divisions', \App\Http\Controllers\Admin\DivisionController::class);
        Route::resource('groups', \App\Http\Controllers\Admin\GroupController::class);
        Route::resource('products', \App\Http\Controllers\Admin\ProductController::class);
        Route::resource('sales', \App\Http\Controllers\Admin\SalesController::class);
        Route::resource('pos-layouts', \App\Http\Controllers\Admin\PosLayoutController::class);

        // Additional product routes
        Route::put('products/{product}/update-stock', [\App\Http\Controllers\Admin\ProductController::class, 'updateStock'])
            ->name('products.update-stock');

        // Sales routes
        Route::get('sales/pos', [\App\Http\Controllers\Admin\SalesController::class, 'pos'])
            ->name('sales.pos');
        Route::get('sales/{sale}/receipt', [\App\Http\Controllers\Admin\SalesController::class, 'receipt'])
            ->name('sales.receipt');

        // Inventory routes
        Route::get('inventory', [\App\Http\Controllers\Admin\InventoryController::class, 'index'])
            ->name('inventory.index');
        Route::get('inventory/{product}/adjust-stock', [\App\Http\Controllers\Admin\InventoryController::class, 'adjustStock'])
            ->name('inventory.adjust-stock');
        Route::post('inventory/{product}/process-adjustment', [\App\Http\Controllers\Admin\InventoryController::class, 'processStockAdjustment'])
            ->name('inventory.process-stock-adjustment');
        Route::get('inventory/adjustment-history', [\App\Http\Controllers\Admin\InventoryController::class, 'adjustmentHistory'])
            ->name('inventory.adjustment-history');
        Route::get('inventory/report', [\App\Http\Controllers\Admin\InventoryController::class, 'report'])
            ->name('inventory.report');

        // Settings routes
        Route::get('settings', [\App\Http\Controllers\Admin\SettingsController::class, 'index'])
            ->name('settings.index');
        Route::post('settings/theme', [\App\Http\Controllers\Admin\SettingsController::class, 'updateTheme'])
            ->name('settings.update-theme');
        Route::post('settings/preferences', [\App\Http\Controllers\Admin\SettingsController::class, 'updatePreferences'])
            ->name('settings.update-preferences');
        Route::get('settings/reset', [\App\Http\Controllers\Admin\SettingsController::class, 'reset'])
            ->name('settings.adminreset');

        // AJAX routes for POS layout
        Route::post('pos-layouts/{layout}/update-positions', [\App\Http\Controllers\Admin\PosLayoutController::class, 'updatePositions'])
            ->name('pos-layouts.update-positions');
        Route::get('pos-layouts/{layout}/groups', [\App\Http\Controllers\Admin\PosLayoutController::class, 'getGroups'])
            ->name('pos-layouts.groups');
    });

    // General Authenticated Routes
    Route::apiResource('pos-devices', POSDeviceController::class);

    // POS Device management routes
    Route::put('pos-devices/{posDevice}/activate', [POSDeviceController::class, 'activate']);
    Route::put('pos-devices/{posDevice}/deactivate', [POSDeviceController::class, 'deactivate']);

    Route::apiResource('users', UserController::class);
    Route::apiResource('categories', CategoryController::class);
    Route::apiResource('divisions', DivisionController::class);
    Route::apiResource('groups', GroupController::class);
    Route::apiResource('products', ProductController::class);

    // Additional product routes
    Route::put('products/{product}/stock', [ProductController::class, 'updateStock']);

    Route::apiResource('sales', SaleController::class);

    // POS Sync routes
    Route::post('pos/sync', [SaleController::class, 'sync']);

    Route::apiResource('sync-logs', SyncLogController::class);
    Route::apiResource('pos-layouts', POSLayoutController::class);
    Route::apiResource('pos-limits', POSLimitController::class);
    Route::apiResource('purchase-orders', PurchaseOrderController::class);
    Route::apiResource('payments', PaymentController::class);
    Route::apiResource('notifications', NotificationController::class);

    // Admin Web Routes (Admin and Master Admin)
    Route::prefix('admin')->name('admin.')->middleware('admin')->group(function () {
        // Screen Setup Management
        Route::resource('screens', \App\Http\Controllers\Admin\ScreenController::class);
        Route::post('screens/{screen}/duplicate', [\App\Http\Controllers\Admin\ScreenController::class, 'duplicate'])->name('screens.duplicate');
        Route::post('screens/{screen}/items', [\App\Http\Controllers\Admin\ScreenController::class, 'addItem'])->name('screens.add-item');
        Route::put('screens/{screen}/items/{item}', [\App\Http\Controllers\Admin\ScreenController::class, 'updateItem'])->name('screens.update-item');
        Route::delete('screens/{screen}/items/{item}', [\App\Http\Controllers\Admin\ScreenController::class, 'removeItem'])->name('screens.remove-item');
        Route::post('screens/{screen}/clear', [\App\Http\Controllers\Admin\ScreenController::class, 'clearScreen'])->name('screens.clear');
        // JSON helpers for Screen Setup (web guard)
        Route::get('screens/groups/json', [\App\Http\Controllers\Admin\ScreenController::class, 'groupsJson'])->name('screens.groups.json');
        Route::get('screens/items-by-group/{group}', [\App\Http\Controllers\Admin\ScreenController::class, 'itemsByGroupJson'])->name('screens.items-by-group.json');
        Route::post('screens/{screen}/sync', [\App\Http\Controllers\Admin\ScreenController::class, 'sync'])->name('screens.sync');


        // Screen Groups Management
        Route::resource('screen-groups', \App\Http\Controllers\Admin\ScreenGroupController::class);

        // Customers Management
        Route::resource('customers', \App\Http\Controllers\Admin\CustomerController::class);
        Route::resource('customer-groups', \App\Http\Controllers\Admin\CustomerGroupController::class);

        // Companies Management
        Route::get('companies', [AdminController::class, 'companiesIndex'])->name('companies.index');
        Route::get('companies/create', [AdminController::class, 'companiesCreate'])->name('companies.create');
        Route::post('companies', [AdminController::class, 'companiesStore'])->name('companies.store');
        Route::get('companies/{company}', [AdminController::class, 'companiesShow'])->name('companies.show');
        Route::get('companies/{company}/edit', [AdminController::class, 'companiesEdit'])->name('companies.edit');
        Route::put('companies/{company}', [AdminController::class, 'companiesUpdate'])->name('companies.update');
        Route::delete('companies/{company}', [AdminController::class, 'companiesDestroy'])->name('companies.destroy');

        // Brands Management
        Route::get('brands', [AdminController::class, 'brandsIndex'])->name('brands.index');
        Route::get('brands/create', [AdminController::class, 'brandsCreate'])->name('brands.create');
        Route::post('brands', [AdminController::class, 'brandsStore'])->name('brands.store');
        Route::get('brands/{brand}', [AdminController::class, 'brandsShow'])->name('brands.show');
        Route::get('brands/{brand}/edit', [AdminController::class, 'brandsEdit'])->name('brands.edit');
        Route::put('brands/{brand}', [AdminController::class, 'brandsUpdate'])->name('brands.update');
        Route::delete('brands/{brand}', [AdminController::class, 'brandsDestroy'])->name('brands.destroy');

        // Master Admin only - Add default brand data

            // Stock Management - Setup
            Route::resource('inventory-units', \App\Http\Controllers\Admin\InventoryUnitController::class);
            Route::resource('inventory-suppliers', \App\Http\Controllers\Admin\InventorySupplierController::class);
            Route::resource('inventory-locations', \App\Http\Controllers\Admin\InventoryLocationController::class);
            Route::resource('inv-categories', \App\Http\Controllers\Admin\InvCategoryController::class);
            Route::resource('inv-divisions', \App\Http\Controllers\Admin\InvDivisionController::class);
            Route::resource('inv-groups', \App\Http\Controllers\Admin\InvGroupController::class);
            Route::resource('inventory-items', \App\Http\Controllers\Admin\InventoryItemController::class);
            Route::get('inventory-items/{inventory_item}/history', [\App\Http\Controllers\Admin\InventoryItemController::class, 'history'])->name('inventory-items.history');

            Route::get('inventory-items/{inventory_item}/used-in', [\App\Http\Controllers\Admin\InventoryItemController::class, 'usedIn'])->name('inventory-items.used-in');

            // Stock Management - Actions
            Route::resource('inventory-purchases', \App\Http\Controllers\Admin\InventoryPurchaseController::class);
            // Place specific routes BEFORE the resource to avoid {inventory_adjustment} catch-all capturing them
            Route::get('inventory-adjustments/stock-count', [\App\Http\Controllers\Admin\InventoryAdjustmentController::class, 'count'])->name('inventory-adjustments.count');
            Route::post('inventory-adjustments/stock-count', [\App\Http\Controllers\Admin\InventoryAdjustmentController::class, 'countApply'])->name('inventory-adjustments.count-apply');
            Route::resource('inventory-adjustments', \App\Http\Controllers\Admin\InventoryAdjustmentController::class)->only(['index','create','store','show']);

	        // Standalone Recipes Management
	        Route::get('recipes', [\App\Http\Controllers\Admin\RecipeController::class, 'index'])->name('recipes.index');
	        Route::get('recipes/search-products', [\App\Http\Controllers\Admin\RecipeController::class, 'searchProducts'])->name('recipes.search-products');
	        Route::get('recipes/search-inventory-items', [\App\Http\Controllers\Admin\RecipeController::class, 'searchInventoryItems'])->name('recipes.search-inventory-items');

				Route::post('recipes/quick-add', [\App\Http\Controllers\Admin\RecipeController::class, 'quickAdd'])->name('recipes.quick-add');



	        // Product Recipes (Admin)
	        Route::get('products/{product}/recipes', [\App\Http\Controllers\Admin\RecipeController::class, 'edit'])->name('products.recipes.edit');
	        Route::post('products/{product}/recipes', [\App\Http\Controllers\Admin\RecipeController::class, 'store'])->name('products.recipes.store');
	        Route::put('products/{product}/recipes/{recipeItem}', [\App\Http\Controllers\Admin\RecipeController::class, 'update'])->name('products.recipes.update');
	        Route::delete('products/{product}/recipes/{recipeItem}', [\App\Http\Controllers\Admin\RecipeController::class, 'destroy'])->name('products.recipes.destroy');

        Route::post('brands/add-default-data', [AdminController::class, 'addBrandDefaultData'])->name('brands.add-default-data');

        // Cashier Privileges Management (Manager/Admin only)
        Route::get('cashier-privileges', [CashierPrivilegeController::class, 'index'])->name('cashier-privileges.index');
        Route::get('cashier-privileges/{cashier}/edit', [CashierPrivilegeController::class, 'edit'])->name('cashier-privileges.edit');
        Route::put('cashier-privileges/{cashier}', [CashierPrivilegeController::class, 'update'])->name('cashier-privileges.update');

        // Company Role Management (Admin only)
        Route::get('roles', [CompanyRoleController::class, 'index'])->name('roles.index');
        Route::get('roles/create', [CompanyRoleController::class, 'create'])->name('roles.create');
        Route::post('roles', [CompanyRoleController::class, 'store'])->name('roles.store');
        Route::get('roles/{role}/privileges', [CompanyRoleController::class, 'privileges'])->name('roles.privileges');
        Route::put('roles/{role}/privileges', [CompanyRoleController::class, 'updatePrivileges'])->name('roles.update-privileges');
        Route::delete('roles/{role}', [CompanyRoleController::class, 'destroy'])->name('roles.destroy');

        // Debug route to check user role
        Route::get('debug-user', function() {
            $user = Auth::user();
            return response()->json([
                'id' => $user->id,
                'name' => $user->name,
                'role' => $user->role,
                'company_id' => $user->company_id,
                'isMasterAdmin' => $user->isMasterAdmin(),
                'isAdmin' => $user->isAdmin(),
                'isManager' => $user->isManager(),
            ]);
        })->name('debug.user');

        // Get company roles for AJAX
        Route::get('company-roles/{company}', function($companyId) {
            $user = Auth::user();

            // Security check
            if (!$user->isMasterAdmin() && $user->company_id != $companyId) {
                abort(403, 'Unauthorized');
            }

            $roles = \App\Models\CompanyRole::where('company_id', $companyId)
                                           ->where('is_active', true)
                                           ->with('privileges')
                                           ->get();

            return response()->json($roles);
        })->name('company.roles');



        // POS Devices Management
        Route::get('pos-devices', [AdminController::class, 'posDevicesIndex'])->name('pos-devices.index');

        // MAC Address Management (Master Admin only)
        Route::get('mac-management', function() {
            return view('admin.mac-management.index');
        })->name('mac-management.index');

        // Settings Management
        Route::prefix('settings')->name('settings.')->group(function() {
            Route::get('/', [App\Http\Controllers\SettingsController::class, 'index'])->name('index');
            Route::post('/theme', [App\Http\Controllers\SettingsController::class, 'updateTheme'])->name('theme');
            Route::post('/notifications', [App\Http\Controllers\SettingsController::class, 'updateNotifications'])->name('notifications');
            Route::post('/display', [App\Http\Controllers\SettingsController::class, 'updateDisplay'])->name('display');
            Route::post('/sidebar', [App\Http\Controllers\SettingsController::class, 'updateSidebar'])->name('sidebar');
            Route::post('/profile', [App\Http\Controllers\SettingsController::class, 'updateProfile'])->name('profile');
            Route::get('/system-info', [App\Http\Controllers\SettingsController::class, 'getSystemInfo'])->name('system-info');
            Route::post('/clear-cache', [App\Http\Controllers\SettingsController::class, 'clearCache'])->name('clear-cache');
            Route::get('/export', [App\Http\Controllers\SettingsController::class, 'exportSettings'])->name('export');
            Route::post('/reset', [App\Http\Controllers\SettingsController::class, 'resetSettings'])->name('reset');
        });
    });

    // User Management Routes (Available to Master Admin, Company Admin, and Managers)
    Route::prefix('admin')->name('admin.')->group(function () {
        // Users Management - Available to Master Admin, Company Admin, and Managers
        Route::get('users', [AdminController::class, 'usersIndex'])->name('users.index');
        Route::get('users/create', [AdminController::class, 'usersCreate'])->name('users.create');
        Route::post('users', [AdminController::class, 'usersStore'])->name('users.store');
        Route::get('users/{user}', [AdminController::class, 'usersShow'])->name('users.show');
        Route::get('users/{user}/edit', [AdminController::class, 'usersEdit'])->name('users.edit');
        Route::put('users/{user}', [AdminController::class, 'usersUpdate'])->name('users.update');
        Route::delete('users/{user}', [AdminController::class, 'usersDestroy'])->name('users.destroy');
    });
});

// Reports routes - Add these to your main web.php file inside the admin prefix group
Route::prefix('admin')->name('admin.')->middleware(['custom.auth'])->group(function () {
    // Reports routes
    Route::get('reports', function() {
        return view('admin.reports.index');
    })->name('reports.index');

    Route::get('reports/sales-by-item', function() {
        return view('admin.reports.sales-by-item');
    })->name('reports.sales-by-item');

    Route::get('reports/sales-by-category', function() {
        return view('admin.reports.sales-by-category');
    })->name('reports.sales-by-category');

    Route::get('reports/inventory-status', function() {
        return view('admin.reports.inventory-status');
    })->name('reports.inventory-status');

    Route::get('reports/profit-analysis', function() {
        return view('admin.reports.profit-analysis');
    })->name('reports.profit-analysis');

    // Export routes for reports (placeholder for future implementation)
    Route::get('reports/sales-by-item/export/{format}', function($format) {
        // Future implementation for Excel/PDF export
        return response()->json(['message' => 'Export functionality coming soon']);
    })->name('reports.sales-by-item.export');

    Route::get('reports/sales-by-category/export/{format}', function($format) {
        // Future implementation for Excel/PDF export
        return response()->json(['message' => 'Export functionality coming soon']);
    })->name('reports.sales-by-category.export');

    Route::get('reports/inventory-status/export/{format}', function($format) {
        // Future implementation for Excel/PDF export
        return response()->json(['message' => 'Export functionality coming soon']);
    })->name('reports.inventory-status.export');

    Route::get('reports/profit-analysis/export/{format}', function($format) {
        // Future implementation for Excel/PDF export
        return response()->json(['message' => 'Export functionality coming soon']);
    })->name('reports.profit-analysis.export');
});
// Reports routes - Added for dashboard access
Route::middleware(['custom.auth'])->prefix('admin')->name('admin.')->group(function () {
    // Reports routes
    Route::get('reports', [\App\Http\Controllers\Admin\ReportsController::class, 'index'])->name('reports.index');
    Route::get('reports/sales-by-item', [\App\Http\Controllers\Admin\ReportsController::class, 'salesByItem'])->name('reports.sales-by-item');
    Route::get('reports/sales-by-category', [\App\Http\Controllers\Admin\ReportsController::class, 'salesByCategory'])->name('reports.sales-by-category');
    Route::get('reports/inventory-status', [\App\Http\Controllers\Admin\ReportsController::class, 'inventoryStatus'])->name('reports.inventory-status');
    Route::get('reports/profit-analysis', [\App\Http\Controllers\Admin\ReportsController::class, 'profitAnalysis'])->name('reports.profit-analysis');
});

// POS Device Management Routes
Route::middleware(['custom.auth'])->prefix('admin')->name('admin.')->group(function () {
    Route::resource('pos-devices', \App\Http\Controllers\Admin\PosDeviceController::class);
    Route::post('pos-devices/{posDevice}/activate', [\App\Http\Controllers\Admin\PosDeviceController::class, 'activate'])->name('pos-devices.activate');
    Route::post('pos-devices/{posDevice}/deactivate', [\App\Http\Controllers\Admin\PosDeviceController::class, 'deactivate'])->name('pos-devices.deactivate');
    Route::post('pos-devices/{posDevice}/regenerate-key', [\App\Http\Controllers\Admin\PosDeviceController::class, 'regenerateKey'])->name('pos-devices.regenerate-key');
});

// POS Device Activation API Routes
Route::prefix('api/pos')->group(function () {
    Route::post('activate', [App\Http\Controllers\PosActivationController::class, 'activate']);
    Route::post('deactivate', [App\Http\Controllers\PosActivationController::class, 'deactivate']);
    Route::post('status', [App\Http\Controllers\PosActivationController::class, 'status']);
    Route::post('heartbeat', [App\Http\Controllers\PosActivationController::class, 'heartbeat']);
    Route::get('device-info', [App\Http\Controllers\PosActivationController::class, 'getDeviceInfo']);
    Route::post('transfer', [App\Http\Controllers\PosActivationController::class, 'transfer']);
});
    Route::post('check-fingerprint', [App\Http\Controllers\PosActivationController::class, 'checkFingerprint']);

use App\Http\Controllers\Admin\ReportsController;

Route::get('/admin/reports/inventory-status/export-excel', [ReportsController::class, 'exportInventoryExcel'])
    ->name('admin.reports.inventory-status.excel');

Route::get('/admin/reports/inventory-status/export-pdf', [ReportsController::class, 'exportInventoryPDF'])
    ->name('admin.reports.inventory-status.pdf');

    Route::get('screens/{screen}/designer', [\App\Http\Controllers\Admin\ScreenController::class, 'designer'])
    ->name('screens.designer');

Route::post('screens/{screen}/designer/add-item1', [\App\Http\Controllers\Admin\ScreenController::class, 'addItem1'])
    ->name('screens.designer.addItem1');

Route::post('screens/{screen}/designer/clear', [\App\Http\Controllers\Admin\ScreenController::class, 'clear'])
    ->name('screens.designer.clear');
