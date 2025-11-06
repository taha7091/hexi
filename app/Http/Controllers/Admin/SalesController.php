<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Sale;
use App\Models\Product;
use App\Models\Group;
use App\Models\Branch;
use App\Models\PosDevice;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Services\RecipeStockDeductionService;

class SalesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        $query = Sale::with(['branch.company', 'user', 'posDevice', 'saleItems.product']);
        
        // Filter by user access
        if (!$user->isMasterAdmin()) {
            $query->whereHas('branch', function($q) use ($user) {
                $q->where('company_id', $user->company_id);
            });
        }

        // Date filters
        if ($request->filled('date_from')) {
            $query->whereDate('sale_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('sale_date', '<=', $request->date_to);
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('payment_status', $request->status);
        }
        
        $sales = $query->orderBy('sale_date', 'desc')->paginate(20);

        // Calculate summary statistics
        $todaySales = Sale::whereDate('sale_date', Carbon::today())
            ->when(!$user->isMasterAdmin(), function($q) use ($user) {
                $q->whereHas('branch', function($subQ) use ($user) {
                    $subQ->where('company_id', $user->company_id);
                });
            })
            ->sum('total_amount');

        $weekSales = Sale::whereBetween('sale_date', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])
            ->when(!$user->isMasterAdmin(), function($q) use ($user) {
                $q->whereHas('branch', function($subQ) use ($user) {
                    $subQ->where('company_id', $user->company_id);
                });
            })
            ->sum('total_amount');

        $monthSales = Sale::whereMonth('sale_date', Carbon::now()->month)
            ->whereYear('sale_date', Carbon::now()->year)
            ->when(!$user->isMasterAdmin(), function($q) use ($user) {
                $q->whereHas('branch', function($subQ) use ($user) {
                    $subQ->where('company_id', $user->company_id);
                });
            })
            ->sum('total_amount');

        $totalSales = Sale::when(!$user->isMasterAdmin(), function($q) use ($user) {
                $q->whereHas('branch', function($subQ) use ($user) {
                    $subQ->where('company_id', $user->company_id);
                });
            })
            ->count();
        
        return view('admin.sales.index', compact('sales', 'todaySales', 'weekSales', 'monthSales', 'totalSales'));
    }

    /**
     * Show POS interface
     */
    public function pos()
    {
        $user = Auth::user();
        
        // Get products for POS
        $query = Product::with(['group.division.category.brand'])
            ->where('is_active', true);
        
        // Filter by user access
        if (!$user->isMasterAdmin()) {
            $query->whereHas('group.division.category.brand', function($q) use ($user) {
                $q->where('company_id', $user->company_id);
            });
        }
        
        $products = $query->orderBy('name')->get();

        // Get groups for filtering
        $groupsQuery = Group::with(['division.category.brand'])
            ->where('is_active', true);
        
        if (!$user->isMasterAdmin()) {
            $groupsQuery->whereHas('division.category.brand', function($q) use ($user) {
                $q->where('company_id', $user->company_id);
            });
        }
        
        $groups = $groupsQuery->orderBy('sort_order')->orderBy('name')->get();
        
        return view('admin.sales.pos', compact('products', 'groups'));
    }

    /**
     * Display the specified resource.
     */
    public function show(Sale $sale)
    {
        $user = Auth::user();

        // Check access
        if (!$user->isMasterAdmin() && $sale->branch->company_id !== $user->company_id) {
            abort(403, 'Access denied.');
        }

        $sale->load(['branch.company', 'user', 'posDevice', 'saleItems.product', 'payments']);

        return view('admin.sales.show', compact('sale'));
    }

    /**
     * Show receipt
     */
    public function receipt(Sale $sale)
    {
        $user = Auth::user();

        // Check access
        if (!$user->isMasterAdmin() && $sale->branch->company_id !== $user->company_id) {
            abort(403, 'Access denied.');
        }

        $sale->load(['branch.company', 'user', 'posDevice', 'saleItems.product', 'payments']);

        return view('admin.sales.receipt', compact('sale'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $user = Auth::user();
        
        // Get available branches
        $branchesQuery = Branch::where('is_active', true);
        if (!$user->isMasterAdmin()) {
            $branchesQuery->where('company_id', $user->company_id);
        }
        $branches = $branchesQuery->get();

        // Get available POS devices
        $posDevicesQuery = PosDevice::where('is_active', true);
        if (!$user->isMasterAdmin()) {
            $posDevicesQuery->whereHas('branch', function($q) use ($user) {
                $q->where('company_id', $user->company_id);
            });
        }
        $posDevices = $posDevicesQuery->get();

        // Get products
        $productsQuery = Product::with(['group.division.category.brand'])
            ->where('is_active', true);
        
        if (!$user->isMasterAdmin()) {
            $productsQuery->whereHas('group.division.category.brand', function($q) use ($user) {
                $q->where('company_id', $user->company_id);
            });
        }
        
        $products = $productsQuery->orderBy('name')->get();
        
        return view('admin.sales.create', compact('branches', 'posDevices', 'products'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'pos_device_id' => 'required|exists:pos_devices,id',
            'tax_amount' => 'nullable|numeric|min:0',
            'discount_amount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.discount_amount' => 'nullable|numeric|min:0',
            'payment_method' => 'required|in:cash,card,bank,wallet',
            'amount_received' => 'nullable|numeric|min:0'
        ]);

        $user = Auth::user();

        // Check access to branch
        $branch = Branch::findOrFail($validated['branch_id']);
        if (!$user->isMasterAdmin() && $branch->company_id !== $user->company_id) {
            abort(403, 'Access denied.');
        }

        DB::beginTransaction();
        try {
            // Calculate totals
            $subtotal = 0;
            foreach ($validated['items'] as $item) {
                $itemTotal = ($item['unit_price'] * $item['quantity']) - ($item['discount_amount'] ?? 0);
                $subtotal += $itemTotal;
            }

            $taxAmount = $validated['tax_amount'] ?? 0;
            $discountAmount = $validated['discount_amount'] ?? 0;
            $totalAmount = $subtotal + $taxAmount - $discountAmount;

            // Create sale
            $sale = Sale::create([
                'pos_device_id' => $validated['pos_device_id'],
                'branch_id' => $validated['branch_id'],
                'user_id' => $user->id,
                'sale_number' => 'SALE-' . date('Ymd') . '-' . str_pad(Sale::count() + 1, 4, '0', STR_PAD_LEFT),
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'discount_amount' => $discountAmount,
                'total_amount' => $totalAmount,
                'payment_status' => 'completed',
                'sale_date' => now(),
                'notes' => $validated['notes']
            ]);

            // Create sale items
            foreach ($validated['items'] as $item) {
                $product = Product::findOrFail($item['product_id']);
                
                $sale->saleItems()->create([
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'discount_amount' => $item['discount_amount'] ?? 0,
                    'total_price' => ($item['unit_price'] * $item['quantity']) - ($item['discount_amount'] ?? 0)
                ]);

                // No product stock updates; inventory is handled via Inventory Items and recipes.
            }

            // Create payment record
            $sale->payments()->create([
                'method' => $validated['payment_method'],
                'amount' => $totalAmount,
                'status' => 'completed',
                'transaction_date' => now()
            ]);

            // Deduct inventory using recipes (on completed sale)
            app(\App\Services\RecipeStockDeductionService::class)->deductForSale($sale);

            DB::commit();

            return redirect()->route('admin.sales.show', $sale)
                ->with('success', 'Sale created successfully.');

        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()
                ->with('error', 'Failed to create sale: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Sale $sale)
    {
        $user = Auth::user();

        // Check access
        if (!$user->isMasterAdmin() && $sale->branch->company_id !== $user->company_id) {
            abort(403, 'Access denied.');
        }

        // Only allow editing of pending sales
        if ($sale->payment_status === 'completed') {
            return redirect()->route('admin.sales.show', $sale)
                ->with('error', 'Cannot edit completed sales.');
        }

        // Get available branches
        $branchesQuery = Branch::where('is_active', true);
        if (!$user->isMasterAdmin()) {
            $branchesQuery->where('company_id', $user->company_id);
        }
        $branches = $branchesQuery->get();

        // Get available POS devices
        $posDevicesQuery = PosDevice::where('is_active', true);
        if (!$user->isMasterAdmin()) {
            $posDevicesQuery->whereHas('branch', function($q) use ($user) {
                $q->where('company_id', $user->company_id);
            });
        }
        $posDevices = $posDevicesQuery->get();

        // Get products
        $productsQuery = Product::with(['group.division.category.brand'])
            ->where('is_active', true);

        if (!$user->isMasterAdmin()) {
            $productsQuery->whereHas('group.division.category.brand', function($q) use ($user) {
                $q->where('company_id', $user->company_id);
            });
        }

        $products = $productsQuery->orderBy('name')->get();

        $sale->load(['saleItems.product', 'payments']);

        return view('admin.sales.edit', compact('sale', 'branches', 'posDevices', 'products'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Sale $sale)
    {
        $user = Auth::user();

        // Check access
        if (!$user->isMasterAdmin() && $sale->branch->company_id !== $user->company_id) {
            abort(403, 'Access denied.');
        }

        // Only allow editing of pending sales
        if ($sale->payment_status === 'completed') {
            return redirect()->route('admin.sales.show', $sale)
                ->with('error', 'Cannot edit completed sales.');
        }

        $validated = $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'pos_device_id' => 'required|exists:pos_devices,id',
            'tax_amount' => 'nullable|numeric|min:0',
            'discount_amount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'payment_status' => 'required|in:pending,completed,cancelled'
        ]);

        // Check access to branch
        $branch = Branch::findOrFail($validated['branch_id']);
        if (!$user->isMasterAdmin() && $branch->company_id !== $user->company_id) {
            abort(403, 'Access denied.');
        }

        $sale->update($validated);

        return redirect()->route('admin.sales.show', $sale)
            ->with('success', 'Sale updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Sale $sale)
    {
        $user = Auth::user();

        // Check access
        if (!$user->isMasterAdmin() && $sale->branch->company_id !== $user->company_id) {
            abort(403, 'Access denied.');
        }

        // Only allow deletion of pending sales
        if ($sale->payment_status === 'completed') {
            return redirect()->route('admin.sales.index')
                ->with('error', 'Cannot delete completed sales.');
        }

        DB::beginTransaction();
        try {
            // No product stock to restore; recipe-based inventory restore happens below.

            // Restore inventory from recipes
            app(\App\Services\RecipeStockDeductionService::class)->restoreForSale($sale);

            // Delete related records
            $sale->saleItems()->delete();
            $sale->payments()->delete();
            $sale->delete();

            DB::commit();

            return redirect()->route('admin.sales.index')
                ->with('success', 'Sale deleted successfully.');

        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->route('admin.sales.index')
                ->with('error', 'Failed to delete sale: ' . $e->getMessage());
        }
    }
}
