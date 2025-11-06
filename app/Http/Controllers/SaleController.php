<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Product;
use App\Models\PosDevice;
use App\Models\Payment;
use App\Models\User;
use App\Models\Company;
use App\Models\Branch;
use App\Models\Brand;

class SaleController extends Controller
{
    /**
     * Get sales with access control and filtering
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // Master admin sees all sales
        if ($user->isMasterAdmin()) {
            $query = Sale::with(['branch.company', 'user', 'posDevice', 'saleItems.product', 'payments']);
        } else {
            // Regular users see only their company's sales
            $query = Sale::whereHas('branch', function($q) use ($user) {
                $q->where('company_id', $user->company_id);
            })->with(['branch', 'user', 'posDevice', 'saleItems.product', 'payments']);
        }

        // Apply filters
        if ($request->has('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        if ($request->has('pos_device_id')) {
            $query->where('pos_device_id', $request->pos_device_id);
        }

        if ($request->has('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        if ($request->has('date_from')) {
            $query->whereDate('sale_date', '>=', $request->date_from);
        }

        if ($request->has('date_to')) {
            $query->whereDate('sale_date', '<=', $request->date_to);
        }

        // Pagination
        $perPage = $request->get('per_page', 15);
        $sales = $query->orderBy('sale_date', 'desc')->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $sales
        ]);
    }

    /**
     * Create new sale with items
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'pos_device_id' => 'required|exists:pos_devices,id',
            'branch_id' => 'nullable|exists:branches,id',
            'tax_amount' => 'nullable|numeric|min:0',
            'discount_amount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.discount_amount' => 'nullable|numeric|min:0',
            'payments' => 'nullable|array',
            'payments.*.method' => 'required|in:cash,card,bank,wallet',
            'payments.*.amount' => 'required|numeric|min:0'
        ]);

        $user = Auth::user();

        // Check if user can create sales for this branch
        if (!$user->isMasterAdmin()) {
            $posDevice = PosDevice::find($validated['pos_device_id']);
            if ($posDevice->branch->company_id !== $user->company_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'You can only create sales for your company branches.'
                ], 403);
            }
        }

        DB::beginTransaction();

        try {
            // Generate sale number
            $saleNumber = 'SALE-' . date('Ymd') . '-' . str_pad(Sale::whereDate('created_at', today())->count() + 1, 4, '0', STR_PAD_LEFT);

            // Create sale
            $sale = Sale::create([
                'sale_number' => $saleNumber,
                'pos_device_id' => $validated['pos_device_id'],
                'branch_id' => $validated['branch_id'],
                'user_id' => $user->id,
                'tax_amount' => $validated['tax_amount'] ?? 0,
                'discount_amount' => $validated['discount_amount'] ?? 0,
                'sale_date' => now(),
                'notes' => $validated['notes'] ?? null,
                'payment_status' => 'pending'
            ]);

            $subtotal = 0;

            // Create sale items
            foreach ($validated['items'] as $item) {
                $product = Product::find($item['product_id']);

                // No product-level stock gate.
                // Inventory is enforced via recipe deduction service (Inventory Items).

                $itemTotal = ($item['unit_price'] * $item['quantity']) - ($item['discount_amount'] ?? 0);

                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'total_price' => $itemTotal,
                    'discount_amount' => $item['discount_amount'] ?? 0
                ]);

                // Do not update Product stock here; inventory is managed via Inventory Items and recipes.

                $subtotal += $itemTotal;
            }

            // Update sale totals
            $totalAmount = $subtotal + $sale->tax_amount - $sale->discount_amount;
            $sale->update([
                'subtotal' => $subtotal,
                'total_amount' => $totalAmount
            ]);

            // Process payments if provided
            if (!empty($validated['payments'])) {
                $totalPaid = 0;
                foreach ($validated['payments'] as $payment) {
                    $sale->payments()->create([
                        'method' => $payment['method'],
                        'amount' => $payment['amount']
                    ]);
                    $totalPaid += $payment['amount'];
                }

                // Update payment status (use 'completed' instead of 'paid' for consistency with admin dashboard)
                if ($totalPaid >= $totalAmount) {
                    $sale->update(['payment_status' => 'completed']);
                } elseif ($totalPaid > 0) {
                    $sale->update(['payment_status' => 'partial']);
                }
            }

            // Deduct inventory when sale is completed/paid
            if (in_array($sale->payment_status, ['completed','paid'])) {
                app(\App\Services\RecipeStockDeductionService::class)->deductForSale($sale);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $sale->load(['saleItems.product', 'payments']),
                'message' => 'Sale created successfully.'
            ], 201);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Failed to create sale: ' . $e->getMessage()
            ], 400);
        }
    }

    /**
     * Show specific sale
     */
    public function show(Sale $sale)
    {
        $user = Auth::user();

        // Check access
        if (!$user->isMasterAdmin() && $sale->branch->company_id !== $user->company_id) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied.'
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => $sale->load(['branch.company', 'user', 'posDevice', 'saleItems.product', 'payments'])
        ]);
    }

    /**
     * Update sale (limited fields)
     */
    public function update(Request $request, Sale $sale)
    {
        $validated = $request->validate([
            'notes' => 'nullable|string',
            'payment_status' => 'sometimes|in:pending,partial,completed,refunded'
        ]);

        $user = Auth::user();

        // Check access
        if (!$user->isMasterAdmin() && $sale->branch->company_id !== $user->company_id) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied.'
            ], 403);
        }

        $sale->update($validated);

        return response()->json([
            'success' => true,
            'data' => $sale->load(['saleItems.product', 'payments']),
            'message' => 'Sale updated successfully.'
        ]);
    }

    /**
     * Delete sale (soft delete or mark as cancelled)
     */
    public function destroy(Sale $sale)
    {
        $user = Auth::user();

        // Check access
        if (!$user->isMasterAdmin() && $sale->branch->company_id !== $user->company_id) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied.'
            ], 403);
        }

        // No product stock to restore; recipe-based inventory restore happens below.

        // Restore inventory deducted via recipes
        app(\App\Services\RecipeStockDeductionService::class)->restoreForSale($sale);

        $sale->delete();

        return response()->json([
            'success' => true,
            'message' => 'Sale deleted successfully.'
        ]);
    }

    /**
     * POS Sync endpoint - bulk create sales
     */
    public function sync(Request $request)
    {
        $validated = $request->validate([
            'pos_device_id' => 'required|exists:pos_devices,id',
            'sales' => 'required|array|min:1',
            'sales.*.local_id' => 'required|string',
            'sales.*.branch_id' => 'required|exists:branches,id',
            'sales.*.tax_amount' => 'nullable|numeric|min:0',
            'sales.*.discount_amount' => 'nullable|numeric|min:0',
            'sales.*.sale_date' => 'required|date',
            'sales.*.notes' => 'nullable|string',
            'sales.*.items' => 'required|array|min:1',
            'sales.*.items.*.product_id' => 'required|exists:products,id',
            'sales.*.items.*.quantity' => 'required|integer|min:1',
            'sales.*.items.*.unit_price' => 'required|numeric|min:0',
            'sales.*.items.*.discount_amount' => 'nullable|numeric|min:0',
            'sales.*.payments' => 'nullable|array',
            'sales.*.payments.*.method' => 'required|in:cash,card,bank,wallet',
            'sales.*.payments.*.amount' => 'required|numeric|min:0'
        ]);

        $user = Auth::user();
        $posDevice = PosDevice::find($validated['pos_device_id']);

        // Check access
        if (!$user->isMasterAdmin() && $posDevice->branch->company_id !== $user->company_id) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied.'
            ], 403);
        }

        $syncResults = [];
        $successCount = 0;
        $errorCount = 0;

        foreach ($validated['sales'] as $saleData) {
            DB::beginTransaction();

            try {
                // Generate sale number
                $saleNumber = 'SALE-' . date('Ymd', strtotime($saleData['sale_date'])) . '-' . str_pad(Sale::whereDate('created_at', $saleData['sale_date'])->count() + 1, 4, '0', STR_PAD_LEFT);

                // Create sale
                $sale = Sale::create([
                    'sale_number' => $saleNumber,
                    'pos_device_id' => $validated['pos_device_id'],
                    'branch_id' => $saleData['branch_id'],
                    'user_id' => $user->id,
                    'tax_amount' => $saleData['tax_amount'] ?? 0,
                    'discount_amount' => $saleData['discount_amount'] ?? 0,
                    'sale_date' => $saleData['sale_date'],
                    'notes' => $saleData['notes'] ?? null,
                    'payment_status' => 'pending'
                ]);

                $subtotal = 0;

                // Create sale items
                foreach ($saleData['items'] as $item) {
                    $itemTotal = ($item['unit_price'] * $item['quantity']) - ($item['discount_amount'] ?? 0);

                    SaleItem::create([
                        'sale_id' => $sale->id,
                        'product_id' => $item['product_id'],
                        'quantity' => $item['quantity'],
                        'unit_price' => $item['unit_price'],
                        'total_price' => $itemTotal,
                        'discount_amount' => $item['discount_amount'] ?? 0
                    ]);

                    // Do not update Product stock; inventory movement is handled via recipes (Inventory Items).

                    $subtotal += $itemTotal;
                }

                // Update sale totals
                $totalAmount = $subtotal + $sale->tax_amount - $sale->discount_amount;
                $sale->update([
                    'subtotal' => $subtotal,
                    'total_amount' => $totalAmount
                ]);

                // Process payments
                if (!empty($saleData['payments'])) {
                    $totalPaid = 0;
                    foreach ($saleData['payments'] as $payment) {
                        $sale->payments()->create([
                            'method' => $payment['method'],
                            'amount' => $payment['amount']
                        ]);
                        $totalPaid += $payment['amount'];
                    }

                    // Update payment status (use 'completed' instead of 'paid' for consistency with admin dashboard)
                    if ($totalPaid >= $totalAmount) {
                        $sale->update(['payment_status' => 'completed']);
                    } elseif ($totalPaid > 0) {
                        $sale->update(['payment_status' => 'partial']);
                    }
                }

                // Deduct inventory when sale is completed/paid
                if (in_array($sale->payment_status, ['completed','paid'])) {
                    app(\App\Services\RecipeStockDeductionService::class)->deductForSale($sale);
                }

                DB::commit();

                $syncResults[] = [
                    'local_id' => $saleData['local_id'],
                    'server_id' => $sale->id,
                    'status' => 'success'
                ];
                $successCount++;

            } catch (\Exception $e) {
                DB::rollback();
                $syncResults[] = [
                    'local_id' => $saleData['local_id'],
                    'status' => 'error',
                    'message' => $e->getMessage()
                ];
                $errorCount++;
            }
        }

        // Log sync activity
        $posDevice->syncLogs()->create([
            'synced_at' => now(),
            'details' => json_encode([
                'total_sales' => count($validated['sales']),
                'success_count' => $successCount,
                'error_count' => $errorCount,
                'results' => $syncResults
            ])
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'total_processed' => count($validated['sales']),
                'success_count' => $successCount,
                'error_count' => $errorCount,
                'results' => $syncResults
            ],
            'message' => "Sync completed. {$successCount} successful, {$errorCount} errors."
        ]);
    }

    /**
     * Store a single POS sale (for real-time processing)
     */
    public function storePOSSale(Request $request)
    {
        // Validate the incoming request
        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
            'items.*.total' => 'required|numeric|min:0',
            'total_amount' => 'required|numeric|min:0',
            'cashier_id' => 'required|exists:users,id',
            'payment_method' => 'nullable|string|in:cash,card,bank,wallet'
        ]);

        $user = Auth::user();
        $company = $user->company;

        // Check POS transaction limits
        if ($company && $company->enforce_limits) {
            // Check per-transaction limit
            if ($company->exceedsTransactionLimit($validated['total_amount'])) {
                return response()->json([
                    'success' => false,
                    'message' => "Transaction amount ${$validated['total_amount']} exceeds the per-transaction limit of ${$company->per_transaction_limit}",
                    'error_type' => 'TRANSACTION_LIMIT_EXCEEDED',
                    'limit_info' => [
                        'per_transaction_limit' => $company->per_transaction_limit,
                        'attempted_amount' => $validated['total_amount']
                    ]
                ], 403);
            }

            // Check daily sales limit
            if ($company->exceedsDailySalesLimit($validated['total_amount'])) {
                $remainingLimit = $company->getRemainingDailySalesLimit();
                return response()->json([
                    'success' => false,
                    'message' => "This transaction would exceed the daily sales limit. Remaining limit: ${$remainingLimit}",
                    'error_type' => 'DAILY_SALES_LIMIT_EXCEEDED',
                    'limit_info' => [
                        'daily_sales_limit' => $company->daily_sales_limit,
                        'todays_sales' => $company->getTodaysSalesTotal(),
                        'remaining_limit' => $remainingLimit,
                        'attempted_amount' => $validated['total_amount']
                    ]
                ], 403);
            }

            // Check daily transaction count limit
            if ($company->exceedsDailyTransactionLimit()) {
                $remainingTransactions = $company->getRemainingDailyTransactionLimit();
                return response()->json([
                    'success' => false,
                    'message' => "Daily transaction limit reached. Remaining transactions: {$remainingTransactions}",
                    'error_type' => 'DAILY_TRANSACTION_COUNT_EXCEEDED',
                    'limit_info' => [
                        'daily_transaction_limit' => $company->daily_transaction_limit,
                        'todays_transactions' => $company->getTodaysTransactionCount(),
                        'remaining_transactions' => $remainingTransactions
                    ]
                ], 403);
            }
        }

        DB::beginTransaction();

        try {
            // Get the cashier user to determine the branch
            $cashier = User::find($validated['cashier_id']);
            
            if (!$cashier) {
                throw new \Exception("Cashier not found");
            }

            // Determine the correct branch inside the authenticated user's company
            $companyId = $user->company_id;
            $branchId = null;

            // Prefer cashier's branch if it belongs to the same company
            if (!empty($cashier->branch_id)) {
                $cashierBranch = Branch::find($cashier->branch_id);
                if ($cashierBranch && (int) $cashierBranch->company_id === (int) $companyId) {
                    $branchId = $cashierBranch->id;
                }
            }

            // Fallback to authenticated user's branch if valid
            if (!$branchId && !empty($user->branch_id)) {
                $userBranch = Branch::find($user->branch_id);
                if ($userBranch && (int) $userBranch->company_id === (int) $companyId) {
                    $branchId = $userBranch->id;
                }
            }

            // If still not found, pick or create a branch within this company
            if (!$branchId) {
                $branch = Branch::where('company_id', $companyId)->first();
                if (!$branch) {
                    $brand = Brand::where('company_id', $companyId)->first();
                    $branch = Branch::create([
                        'name' => 'Default Branch',
                        'company_id' => $companyId,
                        'brand_id' => $brand ? $brand->id : null,
                    ]);
                }
                $branchId = $branch->id;
            }

            // Find or create a POS device for this branch
            $posDevice = PosDevice::where('branch_id', $branchId)
                                  ->where('status', 'active')
                                  ->first();

            if (!$posDevice) {
                // Create a default web POS device
                $posDevice = PosDevice::create([
                    'branch_id' => $branchId,
                    'device_name' => 'Web POS - Branch ' . $branchId,
                    'device_serial' => 'WEB-' . $branchId . '-' . time(),
                    'status' => 'active'
                ]);
            }

            // Generate sale number
            $saleNumber = 'SALE-' . date('Ymd') . '-' . str_pad(Sale::whereDate('created_at', today())->count() + 1, 4, '0', STR_PAD_LEFT);

            // Create sale
            $sale = Sale::create([
                'sale_number' => $saleNumber,
                'pos_device_id' => $posDevice->id,
                'branch_id' => $branchId,
                'user_id' => $validated['cashier_id'],
                'subtotal' => $validated['total_amount'],
                'total_amount' => $validated['total_amount'],
                'tax_amount' => 0,
                'discount_amount' => 0,
                'sale_date' => now(),
                'payment_status' => 'paid'
            ]);

            // Create sale items
            foreach ($validated['items'] as $item) {
                $product = Product::find($item['product_id']);

                // Skip product-level stock gate for POS sales. Inventory is enforced via recipe deduction.
                // If you want to hard-block by product stock, re-enable this check behind a feature flag.

                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['price'],
                    'total_price' => $item['total'],
                    'discount_amount' => 0
                ]);

                // Do not update Product stock; inventory is managed via Inventory Items and recipes.
            }

            // Create payment record
            $sale->payments()->create([
                'method' => $validated['payment_method'] ?? 'cash',
                'amount' => $validated['total_amount']
            ]);

            // Deduct inventory when sale is completed/paid
            if (in_array($sale->payment_status, ['completed','paid'])) {
                app(\App\Services\RecipeStockDeductionService::class)->deductForSale($sale);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => [
                    'sale_id' => $sale->id,
                    'sale_number' => $sale->sale_number,
                    'total_amount' => $sale->total_amount
                ],
                'message' => 'Sale processed successfully!'
            ]);
        } catch (\Exception $e) {
            DB::rollback();

            return response()->json([
                'success' => false,
                'message' => 'Failed to process sale: ' . $e->getMessage()
            ], 400);
        }
    }

    /**
     * Reset sales data for a specific company (Master Admin only)
     * WARNING: This will permanently delete sales data for the specified company
     */
    public function resetCompanySales(Request $request, $company_id)
    {
        $user = Auth::user();

        // Only master admins can reset sales data
        if ($user->role !== 'master_admin') {
            return response()->json([
                'success' => false,
                'message' => 'Access denied. Only master administrators can reset sales data.'
            ], 403);
        }

        // Validate company exists
        $company = Company::find($company_id);
        if (!$company) {
            return response()->json([
                'success' => false,
                'message' => 'Company not found.'
            ], 404);
        }

        // Require confirmation parameter
        $confirmation = $request->input('confirm');
        if ($confirmation !== 'RESET_COMPANY_SALES_PERMANENTLY') {
            return response()->json([
                'success' => false,
                'message' => 'Confirmation required. Please provide confirm parameter with value "RESET_COMPANY_SALES_PERMANENTLY"'
            ], 400);
        }

        try {
            // Get sales for this company through branches
            $companyBranches = Branch::where('company_id', $company_id)->pluck('id');
            $companySales = Sale::whereIn('branch_id', $companyBranches)->pluck('id');

            // Get counts before deletion for logging
            $salesCount = Sale::whereIn('id', $companySales)->count();
            $saleItemsCount = SaleItem::whereIn('sale_id', $companySales)->count();
            $paymentsCount = Payment::whereIn('sale_id', $companySales)->count();

            // Delete company-specific sales data in correct order
            DB::transaction(function () use ($companySales) {
                // Delete payments first
                Payment::whereIn('sale_id', $companySales)->delete();

                // Delete sale items
                SaleItem::whereIn('sale_id', $companySales)->delete();

                // Delete sales
                Sale::whereIn('id', $companySales)->delete();
            });

            // Log the action
            \Log::warning('MASTER ADMIN RESET COMPANY SALES', [
                'admin_id' => $user->id,
                'admin_name' => $user->name,
                'admin_email' => $user->email,
                'company_id' => $company_id,
                'company_name' => $company->name,
                'deleted_sales' => $salesCount,
                'deleted_sale_items' => $saleItemsCount,
                'deleted_payments' => $paymentsCount,
                'timestamp' => now()->toISOString(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            return response()->json([
                'success' => true,
                'message' => "All sales data for company '{$company->name}' has been permanently deleted.",
                'data' => [
                    'company_id' => $company_id,
                    'company_name' => $company->name,
                    'deleted_sales' => $salesCount,
                    'deleted_sale_items' => $saleItemsCount,
                    'deleted_payments' => $paymentsCount,
                    'reset_by' => $user->name,
                    'reset_at' => now()->toISOString()
                ]
            ]);

        } catch (\Exception $e) {

            \Log::error('FAILED TO RESET COMPANY SALES', [
                'admin_id' => $user->id,
                'admin_name' => $user->name,
                'company_id' => $company_id,
                'error' => $e->getMessage(),
                'timestamp' => now()->toISOString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to reset company sales data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check POS limits before processing a transaction
     */
    public function checkLimits(Request $request)
    {
        $validated = $request->validate([
            'transaction_amount' => 'required|numeric|min:0'
        ]);

        $user = Auth::user();
        $company = $user->company;

        if (!$company || !$company->enforce_limits) {
            return response()->json([
                'allowed' => true,
                'message' => 'No limits enforced'
            ]);
        }

        // Check per-transaction limit
        if ($company->exceedsTransactionLimit($validated['transaction_amount'])) {
            return response()->json([
                'allowed' => false,
                'message' => "Transaction amount ${$validated['transaction_amount']} exceeds the per-transaction limit of ${$company->per_transaction_limit}",
                'error_type' => 'TRANSACTION_LIMIT_EXCEEDED',
                'limit_info' => [
                    'per_transaction_limit' => $company->per_transaction_limit,
                    'attempted_amount' => $validated['transaction_amount']
                ]
            ], 403);
        }

        // Check daily sales limit
        if ($company->exceedsDailySalesLimit($validated['transaction_amount'])) {
            $remainingLimit = $company->getRemainingDailySalesLimit();
            return response()->json([
                'allowed' => false,
                'message' => "This transaction would exceed the daily sales limit. Remaining limit: ${$remainingLimit}",
                'error_type' => 'DAILY_SALES_LIMIT_EXCEEDED',
                'limit_info' => [
                    'daily_sales_limit' => $company->daily_sales_limit,
                    'todays_sales' => $company->getTodaysSalesTotal(),
                    'remaining_limit' => $remainingLimit,
                    'attempted_amount' => $validated['transaction_amount']
                ]
            ], 403);
        }

        // Check daily transaction count limit
        if ($company->exceedsDailyTransactionLimit()) {
            $remainingTransactions = $company->getRemainingDailyTransactionLimit();
            return response()->json([
                'allowed' => false,
                'message' => "Daily transaction limit reached. Remaining transactions: {$remainingTransactions}",
                'error_type' => 'DAILY_TRANSACTION_COUNT_EXCEEDED',
                'limit_info' => [
                    'daily_transaction_limit' => $company->daily_transaction_limit,
                    'todays_transactions' => $company->getTodaysTransactionCount(),
                    'remaining_transactions' => $remainingTransactions
                ]
            ], 403);
        }

        return response()->json([
            'allowed' => true,
            'message' => 'Transaction within limits',
            'limit_info' => [
                'per_transaction_limit' => $company->per_transaction_limit,
                'daily_sales_limit' => $company->daily_sales_limit,
                'daily_transaction_limit' => $company->daily_transaction_limit,
                'todays_sales' => $company->getTodaysSalesTotal(),
                'todays_transactions' => $company->getTodaysTransactionCount(),
                'remaining_sales_limit' => $company->getRemainingDailySalesLimit(),
                'remaining_transaction_limit' => $company->getRemainingDailyTransactionLimit()
            ]
        ]);
    }
}
