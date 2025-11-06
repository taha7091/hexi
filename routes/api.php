<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\API\AuthController as APIAuthController;
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
use App\Http\Controllers\EndOfDayController;
use App\Http\Controllers\SyncLogController;
use App\Http\Controllers\POSLayoutController;
use App\Http\Controllers\POSLimitController;
use App\Http\Controllers\MacActivationController;
use App\Http\Controllers\PurchaseOrderController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PosActivationController;

// CORS Preflight requests - This needs to be at the top to handle all methods
Route::options('{any}', function() {
    return response('', 200)
        ->header('Access-Control-Allow-Origin', '*')
        ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
        ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization, Accept, X-Requested-With');
})->where('any', '.*');

// =====================================================================================
// PUBLIC API ENDPOINTS (No Authentication Required)
// These endpoints are for device activation, company lookup, and general health checks.
// =====================================================================================

// Ping and Company lookups
Route::get('/ping', function() {
    return response()->json(['status' => 'ok', 'message' => 'Server is running']);
});

Route::get('/companies/{id}', [CompanyController::class, 'show']);
Route::get('/companies/{id}/device-info', [CompanyController::class, 'getDeviceInfo']);
Route::post('/companies/verify', [CompanyController::class, 'verifyLicense']);
Route::get('/companies/{id}/users', [UserController::class, 'getUsersForCompany']);
Route::get('/companies/{id}/devices', [PosActivationController::class, 'getCompanyDevices']);

// POS Device Activation & Management Routes
Route::prefix('pos')->group(function () {
    Route::post('/activate', [PosActivationController::class, 'activate']);
    Route::post('/deactivate', [PosActivationController::class, 'deactivate']);
    Route::post('/status', [PosActivationController::class, 'status']);
    Route::post('/heartbeat', [PosActivationController::class, 'heartbeat']);
    Route::get('/device-info', [PosActivationController::class, 'getDeviceInfo']);
    Route::post('/transfer', [PosActivationController::class, 'transfer']);
    Route::post('/check-fingerprint', [PosActivationController::class, 'checkFingerprint']);
    Route::post('/check-mac', [PosActivationController::class, 'checkMac']);
    Route::post('/reset-mac', [PosActivationController::class, 'resetMacAddress']);
});

// MAC Address Activation Endpoints
Route::prefix('mac-activation')->group(function() {
    Route::post('/activate-device', [MacActivationController::class, 'activateDevice']);
    Route::post('/check-activation', [MacActivationController::class, 'checkMacActivation']);
});

// Authentication Endpoints for Electron App (Public for login)
Route::post('/auth/login', [APIAuthController::class, 'login']);
Route::post('/auth/pin-login', [APIAuthController::class, 'pinLogin']);


// =====================================================================================
// PROTECTED API ENDPOINTS (Authentication Required - use Sanctum token)
// =====================================================================================
Route::middleware('auth:sanctum')->group(function () {
    // Auth-related Routes
    Route::post('/auth/logout', [APIAuthController::class, 'logout']);
    Route::get('/auth/me', [APIAuthController::class, 'user']);
    Route::get('/auth/check', [APIAuthController::class, 'check']);
    Route::get('/user/privileges', [\App\Http\Controllers\CashierPrivilegeController::class, 'getUserPrivileges']);

    // General Resource API Endpoints (e.g., for POS app)
    Route::get('/products', [ProductController::class, 'getInventory']);
    Route::post('/sync/products', [ProductController::class, 'syncProducts']);
    Route::post('/sales', [SaleController::class, 'storePOSSale']);
    Route::post('/sales/batch', [SaleController::class, 'batchSyncSales']);
    Route::get('/sales', [SaleController::class, 'index']);
    Route::post('/pos/check-limits', [SaleController::class, 'checkLimits']);
    Route::get('/screens', [POSLayoutController::class, 'getScreens']);
    Route::get('/end-of-day/sales', [EndOfDayController::class, 'getTodaysSales']);
    Route::get('/end-of-day/low-stock', [EndOfDayController::class, 'getLowStockItems']);
    Route::post('/end-of-day/sync', [EndOfDayController::class, 'syncToCloud']);
    Route::get('/end-of-day/report', [EndOfDayController::class, 'generateReport']);
    Route::delete('/admin/companies/{company_id}/reset-sales', [SaleController::class, 'resetCompanySales']);

    // Master Admin MAC Management endpoints (protected)
    Route::prefix('admin/mac-management')->group(function() {
        Route::post('/reset-mac', [MacActivationController::class, 'resetMacAddress']);
        Route::get('/company/{companyId}/macs', [MacActivationController::class, 'getCompanyMacAddresses']);
        Route::get('/all-companies-macs', [MacActivationController::class, 'getAllCompaniesWithMacs']);
    });

    // Screen Setup Sync API for Desktop App
    Route::prefix('screens')->group(function() {
        Route::get('/', [\App\Http\Controllers\API\ScreenSyncController::class, 'getScreens']);
        Route::get('/default', [\App\Http\Controllers\API\ScreenSyncController::class, 'getDefaultScreen']);
        Route::get('/groups', [\App\Http\Controllers\API\ScreenSyncController::class, 'getScreenGroups']);
        Route::get('/items-by-group/{groupId}', [\App\Http\Controllers\API\ScreenSyncController::class, 'getItemsByGroup']);

        Route::get('/{screenId}', [\App\Http\Controllers\API\ScreenSyncController::class, 'getScreen']);
        Route::get('/sync/check-updates', [\App\Http\Controllers\API\ScreenSyncController::class, 'checkUpdates']);
        Route::post('/{screenId}/sync', [\App\Http\Controllers\API\ScreenSyncController::class, 'syncScreen']);
    });
});