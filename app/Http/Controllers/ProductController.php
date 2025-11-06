<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Product;
use App\Models\Group;

class ProductController extends Controller
{
    /**
     * Get products with access control
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // Master admin sees all products
        if ($user->isMasterAdmin()) {
            $query = Product::with(['group.division.category.brand.company']);
        } else {
            // Regular users see only their company's products
            $query = Product::whereHas('group.division.category.brand', function($q) use ($user) {
                $q->where('company_id', $user->company_id);
            })->with(['group.division.category.brand']);
        }

        // Apply filters
        if ($request->has('group_id')) {
            $query->where('group_id', $request->group_id);
        }

        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Pagination
        $perPage = $request->get('per_page', 15);
        $products = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $products
        ]);
    }

    /**
     * Create new product
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'group_id' => 'required|exists:groups,id',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'cost_price' => 'nullable|numeric|min:0',
            'sku' => 'nullable|string|max:255|unique:products,sku',
            'barcode' => 'nullable|string|max:255|unique:products,barcode',
            'stock_quantity' => 'nullable|integer|min:0',
            'min_stock_level' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
            'image_url' => 'nullable|url'
        ]);

        $user = Auth::user();

        // Check if user can create products for this group
        if (!$user->isMasterAdmin()) {
            $group = Group::find($validated['group_id']);
            if ($group->division->category->brand->company_id !== $user->company_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'You can only create products for your company groups.'
                ], 403);
            }
        }

        $validated['is_active'] = $validated['is_active'] ?? true;
        $product = Product::create($validated);

        return response()->json([
            'success' => true,
            'data' => $product->load('group.division.category.brand'),
            'message' => 'Product created successfully.'
        ], 201);
    }

    /**
     * Show specific product
     */
    public function show(Product $product)
    {
        $user = Auth::user();

        // Check access
        if (!$user->isMasterAdmin() && $product->group->division->category->brand->company_id !== $user->company_id) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied.'
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => $product->load(['group.division.category.brand.company'])
        ]);
    }

    /**
     * Update product
     */
    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'group_id' => 'sometimes|required|exists:groups,id',
            'description' => 'nullable|string',
            'price' => 'sometimes|required|numeric|min:0',
            'cost_price' => 'nullable|numeric|min:0',
            'sku' => 'nullable|string|max:255|unique:products,sku,' . $product->id,
            'barcode' => 'nullable|string|max:255|unique:products,barcode,' . $product->id,
            'stock_quantity' => 'nullable|integer|min:0',
            'min_stock_level' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
            'image_url' => 'nullable|url'
        ]);

        $user = Auth::user();

        // Check access
        if (!$user->isMasterAdmin() && $product->group->division->category->brand->company_id !== $user->company_id) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied.'
            ], 403);
        }

        $product->update($validated);

        return response()->json([
            'success' => true,
            'data' => $product->load('group.division.category.brand'),
            'message' => 'Product updated successfully.'
        ]);
    }

    /**
     * Delete product
     */
    public function destroy(Product $product)
    {
        $user = Auth::user();

        // Check access
        if (!$user->isMasterAdmin() && $product->group->division->category->brand->company_id !== $user->company_id) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied.'
            ], 403);
        }

        $product->delete();

        return response()->json([
            'success' => true,
            'message' => 'Product deleted successfully.'
        ]);
    }

    /**
     * Update stock quantity
     */
    public function updateStock(Request $request, Product $product)
    {
        $validated = $request->validate([
            'quantity' => 'required|integer',
            'type' => 'required|in:add,subtract,set',
            'reason' => 'nullable|string'
        ]);

        $user = Auth::user();

        // Check access
        if (!$user->isMasterAdmin() && $product->group->division->category->brand->company_id !== $user->company_id) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied.'
            ], 403);
        }

        $oldQuantity = $product->stock_quantity ?? 0;

        switch ($validated['type']) {
            case 'add':
                $newQuantity = $oldQuantity + $validated['quantity'];
                break;
            case 'subtract':
                $newQuantity = max(0, $oldQuantity - $validated['quantity']);
                break;
            case 'set':
                $newQuantity = max(0, $validated['quantity']);
                break;
        }

        $product->update(['stock_quantity' => $newQuantity]);

        return response()->json([
            'success' => true,
            'data' => [
                'product' => $product,
                'old_quantity' => $oldQuantity,
                'new_quantity' => $newQuantity
            ],
            'message' => 'Stock updated successfully.'
        ]);
    }

    /**
     * Get inventory for POS app sync
     */
    public function getInventory(Request $request)
    {
        $user = Auth::user();

        // Build query with relationships
        $query = Product::with(['group.division.category.brand'])
            ->where('is_active', true);

        // Filter by user access if not master admin (only if user is authenticated)
        if ($user && !$user->isMasterAdmin()) {
            $query->whereHas('group.division.category.brand', function($q) use ($user) {
                $q->where('company_id', $user->company_id);
            });
        }

        // Get products with essential fields for POS
        $products = $query->select([
            'id',
            'name',
            'sku',
            'price',
            'cost_price',
            'stock_quantity',
            'min_stock_level',
            'barcode',
            'color',
            'group_id'
        ])->get();

        return response()->json([
            'success' => true,
            'data' => $products,
            'count' => $products->count()
        ]);
    }

    /**
     * Sync products for POS app
     * Returns products with full details for offline POS operation
     */
    public function syncProducts(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Authentication required'
            ], 401);
        }

        // Build query with relationships
        $query = Product::with(['group.division.category.brand'])
            ->where('is_active', true);

        // Filter by user's company if not master admin
        if (!$user->isMasterAdmin()) {
            $query->whereHas('group.division.category.brand', function($q) use ($user) {
                $q->where('company_id', $user->company_id);
            });
        }

        // Get all active products for the user's company
        $products = $query->get()->map(function($product) {
            return [
                'id' => $product->id,
                'name' => $product->name,
                'sku' => $product->sku,
                'price' => $product->price,
                'cost_price' => $product->cost_price,
                'stock_quantity' => $product->stock_quantity ?? 0,
                'min_stock_level' => $product->min_stock_level,
                'barcode' => $product->barcode,
                'color' => $product->color,
                'description' => $product->description,
                'image_url' => $product->image_url,
                'group_id' => $product->group_id,
                'group' => $product->group ? [
                    'id' => $product->group->id,
                    'name' => $product->group->name
                ] : null,
                'category' => $product->group && $product->group->division && $product->group->division->category ?
                    $product->group->division->category->name : 'Uncategorized',
                'is_active' => $product->is_active,
                'lastUpdated' => $product->updated_at->toISOString()
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'products' => $products,
                'count' => $products->count(),
                'synced_at' => now()->toISOString()
            ],
            'message' => 'Products synced successfully'
        ]);
    }
}
