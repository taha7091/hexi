<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Product;
use App\Models\RecipeItem;
use App\Models\InventoryItem;

class RecipeController extends Controller
{
    protected function authorizeProduct(Product $product)
    {
        $user = Auth::user();
        if (!$user->isMasterAdmin() && $product->group->division->category->brand->company_id !== $user->company_id) {
            abort(403, 'Access denied.');
        }
    }

    public function edit(Product $product)
    {
        $this->authorizeProduct($product);

        $product->load(['group.division.category.brand']);
        $recipeItems = RecipeItem::with(['inventoryItem.usageUnit'])
            ->where('product_id', $product->id)
            ->orderBy('id')
            ->get();

        $brandId = $product->group->division->category->brand->id;
        // Limit inventory items to the same brand (and active)
        $inventoryItems = InventoryItem::with('usageUnit')
            ->where('brand_id', $brandId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('admin.recipes.edit', compact('product', 'recipeItems', 'inventoryItems'));
    }

    public function store(Request $request, Product $product)
    {
        $this->authorizeProduct($product);

        $data = $request->validate([
            'inventory_item_id' => 'required|exists:inventory_items,id',
            'usage_quantity' => 'required|numeric|min:0.0001'
        ]);

        $inventoryItem = InventoryItem::with('usageUnit')->findOrFail($data['inventory_item_id']);
        $brandId = $product->group->division->category->brand->id;
        if ($inventoryItem->brand_id !== $brandId) {
            abort(403, 'Inventory item not in the same brand.');
        }

        $recipeItem = RecipeItem::firstOrNew([
            'product_id' => $product->id,
            'inventory_item_id' => $inventoryItem->id,
        ]);
        $recipeItem->usage_quantity = $data['usage_quantity'];
        $recipeItem->save();

        return redirect()->route('admin.products.recipes.edit', $product)
            ->with('success', 'Recipe item saved.');
    }

    public function update(Request $request, Product $product, RecipeItem $recipeItem)
    {
        $this->authorizeProduct($product);
        if ($recipeItem->product_id !== $product->id) {
            abort(404);
        }

        $data = $request->validate([
            'usage_quantity' => 'required|numeric|min:0.0001'
        ]);

        $recipeItem->update(['usage_quantity' => $data['usage_quantity']]);

        return redirect()->route('admin.products.recipes.edit', $product)
            ->with('success', 'Recipe item updated.');
    }

    public function destroy(Product $product, RecipeItem $recipeItem)
    {
        $this->authorizeProduct($product);
        if ($recipeItem->product_id !== $product->id) {
            abort(404);
        }

        $recipeItem->delete();

        return redirect()->route('admin.products.recipes.edit', $product)
            ->with('success', 'Recipe item removed.');
    }

    // Standalone Recipes page: Quick Add + browse with filters
    public function index(Request $request)
    {
        $user = Auth::user();
        $q = trim($request->input('q', ''));
        $status = $request->input('status', 'all'); // all | with | without

        // Counts (scoped by company/brand hierarchy)
        $base = Product::query()
            ->whereHas('group.division.category.brand', function($qq) use ($user) {
                if (!$user->isMasterAdmin()) {
                    $qq->where('company_id', $user->company_id);
                }
            });
        $allCount = (clone $base)->count();
        $withCount = (clone $base)->has('recipeItems')->count();
        $withoutCount = (clone $base)->doesntHave('recipeItems')->count();

        // Listing
        $query = Product::query()
            ->withCount('recipeItems')
            ->whereHas('group.division.category.brand', function($qq) use ($user) {
                if (!$user->isMasterAdmin()) {
                    $qq->where('company_id', $user->company_id);
                }
            })
            ->when($q !== '', function($qq) use ($q) {
                $qq->where(function($w) use ($q) {
                    $w->where('name', 'like', "%{$q}%")
                      ->orWhere('sku', 'like', "%{$q}%")
                      ->orWhere('barcode', 'like', "%{$q}%");
                });
            });

        if ($status === 'with') {
            $query->has('recipeItems');
        } elseif ($status === 'without') {
            $query->doesntHave('recipeItems');
        }

        $products = $query->orderBy('name')->paginate(20)->withQueryString();

        return view('admin.recipes.index', compact('q','status','allCount','withCount','withoutCount','products'));
    }

    // AJAX: search products by name/sku/barcode, include brand_id for dependent inventory search
    public function searchProducts(Request $request)
    {
        $user = Auth::user();
        $q = trim($request->input('q', ''));
        $limit = (int)($request->input('limit', 20));

        $items = Product::query()
            ->with(['group.division.category.brand:id'])
            ->whereHas('group.division.category.brand', function($qq) use ($user) {
                if (!$user->isMasterAdmin()) {
                    $qq->where('company_id', $user->company_id);
                }
            })
            ->when($q !== '', function($qq) use ($q) {
                $qq->where(function($w) use ($q) {
                    $w->where('name', 'like', "%{$q}%")
                      ->orWhere('sku', 'like', "%{$q}%")
                      ->orWhere('barcode', 'like', "%{$q}%");
                });
            })
            ->orderBy('name')
            ->limit($limit)
            ->get(['id','name','sku','barcode','group_id']);

        $results = $items->map(function($p){
            $label = $p->name;
            if ($p->sku) { $label .= " (".$p->sku.")"; }
            $brandId = optional($p->group->division->category->brand)->id;
            return ['id' => $p->id, 'text' => $label, 'brand_id' => $brandId];
        });

        return response()->json(['results' => $results]);
    }

    // AJAX: search inventory items (optionally restricted to brand)
    public function searchInventoryItems(Request $request)
    {
        $user = Auth::user();
        $q = trim($request->input('q', ''));
        $limit = (int)($request->input('limit', 20));
        $brandId = $request->input('brand_id');

        $items = InventoryItem::with('usageUnit')
            ->when($brandId, function($qq) use ($brandId) {
                $qq->where('brand_id', $brandId);
            }, function($qq) use ($user) {
                if (!$user->isMasterAdmin()) {
                    $qq->where('company_id', $user->company_id);
                }
            })
            ->where('is_active', true)
            ->when($q !== '', function($qq) use ($q) {
                $qq->where(function($w) use ($q) {
                    $w->where('name', 'like', "%{$q}%")
                      ->orWhere('barcode', 'like', "%{$q}%")
                      ->orWhere('code', 'like', "%{$q}%");
                });
            })
            ->orderBy('name')
            ->limit($limit)
            ->get();

        $results = $items->map(function($i){
            $unit = $i->usageUnit->symbol ?? $i->usageUnit->name ?? '';
            $label = $i->name . ($unit ? (" (".$unit.")") : '');
            return ['id' => $i->id, 'text' => $label];
        });

        return response()->json(['results' => $results]);
    }

    // Quick Add recipe line without navigating to product page
    public function quickAdd(Request $request)
    {
        $data = $request->validate([
            'product_id' => 'required|exists:products,id',
            'inventory_item_id' => 'required|exists:inventory_items,id',
            'usage_quantity' => 'required|numeric|min:0.0001',
        ]);

        $user = Auth::user();
        $product = Product::with(['group.division.category.brand'])->findOrFail($data['product_id']);
        if (!$user->isMasterAdmin() && $product->group->division->category->brand->company_id !== $user->company_id) {
            abort(403, 'Access denied.');
        }

        $inventoryItem = InventoryItem::findOrFail($data['inventory_item_id']);
        $productBrandId = $product->group->division->category->brand->id;
        if ((int)$inventoryItem->brand_id !== (int)$productBrandId) {
            $msg = 'Selected inventory item is not in the same brand as the product.';
            if ($request->expectsJson()) {
                return response()->json(['ok' => false, 'message' => $msg], 422);
            }
            return back()->withErrors(['inventory_item_id' => $msg])->withInput();
        }

        $recipeItem = RecipeItem::firstOrNew([
            'product_id' => $product->id,
            'inventory_item_id' => $inventoryItem->id,
        ]);
        $recipeItem->usage_quantity = $data['usage_quantity'];
        $recipeItem->save();

        if ($request->expectsJson()) {
            return response()->json([
                'ok' => true,
                'message' => 'Recipe item saved.',
                'product_id' => $product->id,
                'redirect' => route('admin.products.recipes.edit', $product),
            ]);
        }

        return back()->with('success', 'Recipe item saved.')->with('recent_product_id', $product->id);
    }

}

