<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Group;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();

        $query = Product::with(['group.division.category.brand.company']);

        // Filter by user access
        if (!$user->isMasterAdmin()) {
            $query->whereHas('group.division.category.brand', function($q) use ($user) {
                $q->where('company_id', $user->company_id);
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

        return view('admin.products.index', compact('products', 'groups'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $user = Auth::user();

        $query = Group::with(['division.category.brand.company']);

        // Filter groups by user access
        if (!$user->isMasterAdmin()) {
            $query->whereHas('division.category.brand', function($q) use ($user) {
                $q->where('company_id', $user->company_id);
            });
        }

        $groups = $query->orderBy('name')->get();

        return view('admin.products.create', compact('groups'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'group_id' => 'required|exists:groups,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'cost_price' => 'nullable|numeric|min:0',
            'sku' => 'required|string|max:255|unique:products,sku',
            'barcode' => 'nullable|string|max:255',
            'stock_quantity' => 'nullable|integer|min:0',
            'min_stock_level' => 'nullable|integer|min:0',
            'color' => 'nullable|string|max:7',
            'sort_order' => 'nullable|integer|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'is_active' => 'boolean'
        ]);

        $user = Auth::user();

        // Check if user has access to the group
        $group = Group::findOrFail($validated['group_id']);
        if (!$user->isMasterAdmin() && $group->division->category->brand->company_id !== $user->company_id) {
            abort(403, 'Access denied.');
        }

        // Set related IDs
        $validated['category_id'] = $group->division->category_id;
        $validated['division_id'] = $group->division_id;

        // Handle image upload
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('products', 'public');
            $validated['image_url'] = Storage::url($imagePath);
        }

        $validated['is_active'] = $validated['is_active'] ?? true;
        $product = Product::create($validated);

        return redirect()->route('admin.products.index')
            ->with('success', 'Product created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        $user = Auth::user();

        // Check access
        if (!$user->isMasterAdmin() && $product->group->division->category->brand->company_id !== $user->company_id) {
            abort(403, 'Access denied.');
        }

        $product->load(['group.division.category.brand.company']);

        return view('admin.products.show', compact('product'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Product $product)
    {
        $user = Auth::user();

        // Check access
        if (!$user->isMasterAdmin() && $product->group->division->category->brand->company_id !== $user->company_id) {
            abort(403, 'Access denied.');
        }

        $query = Group::with(['division.category.brand.company']);

        // Filter groups by user access
        if (!$user->isMasterAdmin()) {
            $query->whereHas('division.category.brand', function($q) use ($user) {
                $q->where('company_id', $user->company_id);
            });
        }

        $groups = $query->orderBy('name')->get();

        return view('admin.products.edit', compact('product', 'groups'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'group_id' => 'required|exists:groups,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'cost_price' => 'nullable|numeric|min:0',
            'sku' => 'required|string|max:255|unique:products,sku,' . $product->id,
            'barcode' => 'nullable|string|max:255',
            'stock_quantity' => 'nullable|integer|min:0',
            'min_stock_level' => 'nullable|integer|min:0',
            'color' => 'nullable|string|max:7',
            'sort_order' => 'nullable|integer|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'is_active' => 'boolean'
        ]);

        $user = Auth::user();

        // Check access to current product
        if (!$user->isMasterAdmin() && $product->group->division->category->brand->company_id !== $user->company_id) {
            abort(403, 'Access denied.');
        }

        // Check access to new group
        $group = Group::findOrFail($validated['group_id']);
        if (!$user->isMasterAdmin() && $group->division->category->brand->company_id !== $user->company_id) {
            abort(403, 'Access denied.');
        }

        // Set related IDs
        $validated['category_id'] = $group->division->category_id;
        $validated['division_id'] = $group->division_id;

        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($product->image_url) {
                $oldImagePath = str_replace('/storage/', '', $product->image_url);
                Storage::disk('public')->delete($oldImagePath);
            }

            $imagePath = $request->file('image')->store('products', 'public');
            $validated['image_url'] = Storage::url($imagePath);
        }

        $validated['is_active'] = $validated['is_active'] ?? false;
        $product->update($validated);

        return redirect()->route('admin.products.index')
            ->with('success', 'Product updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        $user = Auth::user();

        // Check access
        if (!$user->isMasterAdmin() && $product->group->division->category->brand->company_id !== $user->company_id) {
            abort(403, 'Access denied.');
        }

        // Delete image if exists
        if ($product->image_url) {
            $imagePath = str_replace('/storage/', '', $product->image_url);
            Storage::disk('public')->delete($imagePath);
        }

        $product->delete();

        return redirect()->route('admin.products.index')
            ->with('success', 'Product deleted successfully.');
    }

    /**
     * Update product stock
     */
    public function updateStock(Request $request, Product $product)
    {
        $validated = $request->validate([
            'stock_adjustment' => 'required|integer',
            'adjustment_type' => 'required|in:add,subtract,set',
            'reason' => 'nullable|string|max:255'
        ]);

        $user = Auth::user();

        // Check access
        if (!$user->isMasterAdmin() && $product->group->division->category->brand->company_id !== $user->company_id) {
            abort(403, 'Access denied.');
        }

        $currentStock = $product->stock_quantity ?? 0;
        $adjustment = $validated['stock_adjustment'];

        switch ($validated['adjustment_type']) {
            case 'add':
                $newStock = $currentStock + $adjustment;
                break;
            case 'subtract':
                $newStock = max(0, $currentStock - $adjustment);
                break;
            case 'set':
                $newStock = max(0, $adjustment);
                break;
        }

        $product->update(['stock_quantity' => $newStock]);

        return redirect()->route('admin.products.show', $product)
            ->with('success', 'Stock updated successfully.');
    }
}
