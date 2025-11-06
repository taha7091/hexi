<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Group;
use App\Models\StockAdjustment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class InventoryController extends Controller
{
    /**
     * Display inventory overview
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        $query = Product::with(['group.division.category.brand']);
        
        // Filter by user access
        if (!$user->isMasterAdmin()) {
            $query->whereHas('group.division.category.brand', function($q) use ($user) {
                $q->where('company_id', $user->company_id);
            });
        }

        // Apply filters
        if ($request->filled('group_id')) {
            $query->where('group_id', $request->group_id);
        }

        if ($request->filled('stock_status')) {
            switch ($request->stock_status) {
                case 'low':
                    $query->whereRaw('stock_quantity <= min_stock_level AND stock_quantity > 0');
                    break;
                case 'out':
                    $query->where('stock_quantity', 0);
                    break;
                case 'normal':
                    $query->whereRaw('stock_quantity > min_stock_level OR min_stock_level IS NULL');
                    break;
            }
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%")
                  ->orWhere('barcode', 'like', "%{$search}%");
            });
        }

        $products = $query->orderBy('name')->paginate(20);

        // Get groups for filter
        $groupsQuery = Group::with(['division.category.brand']);
        if (!$user->isMasterAdmin()) {
            $groupsQuery->whereHas('division.category.brand', function($q) use ($user) {
                $q->where('company_id', $user->company_id);
            });
        }
        $groups = $groupsQuery->orderBy('name')->get();

        // Calculate inventory statistics
        $totalProducts = $query->count();
        $lowStockCount = Product::whereRaw('stock_quantity <= min_stock_level AND stock_quantity > 0')
            ->when(!$user->isMasterAdmin(), function($q) use ($user) {
                $q->whereHas('group.division.category.brand', function($subQ) use ($user) {
                    $subQ->where('company_id', $user->company_id);
                });
            })
            ->count();
        
        $outOfStockCount = Product::where('stock_quantity', 0)
            ->when(!$user->isMasterAdmin(), function($q) use ($user) {
                $q->whereHas('group.division.category.brand', function($subQ) use ($user) {
                    $subQ->where('company_id', $user->company_id);
                });
            })
            ->count();

        $totalValue = Product::selectRaw('SUM(stock_quantity * cost_price) as total_value')
            ->when(!$user->isMasterAdmin(), function($q) use ($user) {
                $q->whereHas('group.division.category.brand', function($subQ) use ($user) {
                    $subQ->where('company_id', $user->company_id);
                });
            })
            ->first()->total_value ?? 0;

        return view('admin.inventory.index', compact(
            'products', 'groups', 'totalProducts', 'lowStockCount', 
            'outOfStockCount', 'totalValue'
        ));
    }

    /**
     * Show stock adjustment form
     */
    public function adjustStock(Product $product)
    {
        $user = Auth::user();

        // Check access
        if (!$user->isMasterAdmin() && $product->group->division->category->brand->company_id !== $user->company_id) {
            abort(403, 'Access denied.');
        }

        return view('admin.inventory.adjust-stock', compact('product'));
    }

    /**
     * Process stock adjustment
     */
    public function processStockAdjustment(Request $request, Product $product)
    {
        $validated = $request->validate([
            'adjustment_type' => 'required|in:add,subtract,set',
            'quantity' => 'required|integer|min:0',
            'reason' => 'required|string|max:255',
            'notes' => 'nullable|string'
        ]);

        $user = Auth::user();

        // Check access
        if (!$user->isMasterAdmin() && $product->group->division->category->brand->company_id !== $user->company_id) {
            abort(403, 'Access denied.');
        }

        $currentStock = $product->stock_quantity ?? 0;
        $adjustment = $validated['quantity'];

        switch ($validated['adjustment_type']) {
            case 'add':
                $newStock = $currentStock + $adjustment;
                $adjustmentAmount = $adjustment;
                break;
            case 'subtract':
                $newStock = max(0, $currentStock - $adjustment);
                $adjustmentAmount = -min($adjustment, $currentStock);
                break;
            case 'set':
                $newStock = $adjustment;
                $adjustmentAmount = $adjustment - $currentStock;
                break;
        }

        DB::beginTransaction();
        try {
            // Update product stock
            $product->update(['stock_quantity' => $newStock]);

            // Create stock adjustment record
            StockAdjustment::create([
                'product_id' => $product->id,
                'user_id' => $user->id,
                'adjustment_type' => $validated['adjustment_type'],
                'quantity_before' => $currentStock,
                'quantity_after' => $newStock,
                'adjustment_amount' => $adjustmentAmount,
                'reason' => $validated['reason'],
                'notes' => $validated['notes']
            ]);

            DB::commit();

            return redirect()->route('admin.inventory.index')
                ->with('success', 'Stock adjusted successfully.');

        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()
                ->with('error', 'Failed to adjust stock: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Show stock adjustment history
     */
    public function adjustmentHistory(Request $request)
    {
        $user = Auth::user();
        
        $query = StockAdjustment::with(['product.group.division.category.brand', 'user']);
        
        // Filter by user access
        if (!$user->isMasterAdmin()) {
            $query->whereHas('product.group.division.category.brand', function($q) use ($user) {
                $q->where('company_id', $user->company_id);
            });
        }

        // Apply filters
        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $adjustments = $query->orderBy('created_at', 'desc')->paginate(20);

        // Get products for filter
        $productsQuery = Product::with(['group.division.category.brand']);
        if (!$user->isMasterAdmin()) {
            $productsQuery->whereHas('group.division.category.brand', function($q) use ($user) {
                $q->where('company_id', $user->company_id);
            });
        }
        $products = $productsQuery->orderBy('name')->get();

        return view('admin.inventory.adjustment-history', compact('adjustments', 'products'));
    }

    /**
     * Generate inventory report
     */
    public function report(Request $request)
    {
        $user = Auth::user();
        
        $query = Product::with(['group.division.category.brand']);
        
        // Filter by user access
        if (!$user->isMasterAdmin()) {
            $query->whereHas('group.division.category.brand', function($q) use ($user) {
                $q->where('company_id', $user->company_id);
            });
        }

        // Apply filters
        if ($request->filled('group_id')) {
            $query->where('group_id', $request->group_id);
        }

        if ($request->filled('brand_id')) {
            $query->whereHas('group.division.category', function($q) use ($request) {
                $q->where('brand_id', $request->brand_id);
            });
        }

        $products = $query->orderBy('name')->get();

        // Get filter options
        $groupsQuery = Group::with(['division.category.brand']);
        if (!$user->isMasterAdmin()) {
            $groupsQuery->whereHas('division.category.brand', function($q) use ($user) {
                $q->where('company_id', $user->company_id);
            });
        }
        $groups = $groupsQuery->orderBy('name')->get();

        return view('admin.inventory.report', compact('products', 'groups'));
    }
}
