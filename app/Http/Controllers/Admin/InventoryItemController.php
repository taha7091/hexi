<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InventoryItem;
use App\Models\InventoryAdjustment;
use App\Models\InvCategory;
use App\Models\InvDivision;
use App\Models\InvGroup;
use App\Models\InventoryUnit;
use App\Models\Supplier;
use App\Models\InventoryLocation;
use App\Models\Brand;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InventoryItemController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = InventoryItem::with(['category.brand','division','group','supplier','location','stockUnit'])
            ->withCount(['usedInProducts as used_in_count']);
        if (!$user->isMasterAdmin()) {
            $query->where('company_id', $user->company_id);
        }
        if ($request->filled('search')) {
            $query->where(function($q) use ($request){
                $q->where('name','like','%'.$request->search.'%')
                  ->orWhere('code','like','%'.$request->search.'%')
                  ->orWhere('barcode','like','%'.$request->search.'%');
            });
        }
        if ($request->filled('inv_group_id')) {
            $query->where('inv_group_id', $request->inv_group_id);
        }
        $items = $query->orderBy('name')->paginate(20)->withQueryString();

        $brands = $user->isMasterAdmin() ? Brand::all() : Brand::where('company_id', $user->company_id)->get();
        $categories = InvCategory::when(!$user->isMasterAdmin(), function($q) use ($user){
            $q->whereHas('brand', function($qq) use ($user){ $qq->where('company_id',$user->company_id); });
        })->get();
        $divisions = InvDivision::when(!$user->isMasterAdmin(), function($q) use ($user){
            $q->whereHas('category.brand', function($qq) use ($user){ $qq->where('company_id',$user->company_id); });
        })->get();
        $groups = InvGroup::when(!$user->isMasterAdmin(), function($q) use ($user){
            $q->whereHas('division.category.brand', function($qq) use ($user){ $qq->where('company_id',$user->company_id); });
        })->get();

        return view('admin.inventory.items.index', compact('items','brands','categories','divisions','groups'));
    }

    public function create()
    {
        $user = Auth::user();
        $brands = $user->isMasterAdmin() ? Brand::all() : Brand::where('company_id', $user->company_id)->get();
        $categories = InvCategory::when(!$user->isMasterAdmin(), function($q) use ($user){
            $q->whereHas('brand', function($qq) use ($user){ $qq->where('company_id',$user->company_id); });
        })->get();
        $divisions = InvDivision::when(!$user->isMasterAdmin(), function($q) use ($user){
            $q->whereHas('category.brand', function($qq) use ($user){ $qq->where('company_id',$user->company_id); });
        })->get();
        $groups = InvGroup::when(!$user->isMasterAdmin(), function($q) use ($user){
            $q->whereHas('division.category.brand', function($qq) use ($user){ $qq->where('company_id',$user->company_id); });
        })->get();
        $units = InventoryUnit::when(!$user->isMasterAdmin(), function($q) use ($user){ $q->where('company_id',$user->company_id); })->get();
        $suppliers = Supplier::when(!$user->isMasterAdmin(), function($q) use ($user){ $q->where('company_id',$user->company_id); })->get();
        $locations = InventoryLocation::when(!$user->isMasterAdmin(), function($q) use ($user){ $q->where('company_id',$user->company_id); })->get();
        return view('admin.inventory.items.create', compact('brands','categories','divisions','groups','units','suppliers','locations'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'brand_id' => 'nullable|exists:brands,id',
            'inv_category_id' => 'required|exists:inv_categories,id',
            'inv_division_id' => 'required|exists:inv_divisions,id',
            'inv_group_id' => 'required|exists:inv_groups,id',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'location_id' => 'nullable|exists:inventory_locations,id',
            'code' => 'nullable|string|max:100',
            'name' => 'required|string|max:255',
            'barcode' => 'nullable|string|max:100',
            'buying_unit_id' => 'required|exists:inventory_units,id',
            'stock_unit_id' => 'required|exists:inventory_units,id',
            'usage_unit_id' => 'required|exists:inventory_units,id',
            'buy_to_stock_factor' => 'required|numeric|min:0.000001',
            'stock_to_usage_factor' => 'required|numeric|min:0.000001',
            'cost_price' => 'nullable|numeric|min:0',
            'current_stock' => 'nullable|numeric|min:0',
            'minimum_stock' => 'nullable|numeric|min:0',
            'is_active' => 'boolean',
            'notes' => 'nullable|string',
        ]);
        $data = $request->only([
            'brand_id','inv_category_id','inv_division_id','inv_group_id','supplier_id','location_id',
            'code','name','barcode','buying_unit_id','stock_unit_id','usage_unit_id','buy_to_stock_factor',
            'stock_to_usage_factor','cost_price','current_stock','minimum_stock','is_active','notes'
        ]);
        $data['company_id'] = Auth::user()->company_id;
        InventoryItem::create($data);
        return redirect()->route('admin.inventory-items.index')->with('success','Inventory item created');
    }

    public function edit(InventoryItem $inventory_item)
    {
        $item = $inventory_item;
        $user = Auth::user();
        if (!$user->isMasterAdmin() && $item->company_id !== $user->company_id) abort(403);
        $brands = $user->isMasterAdmin() ? Brand::all() : Brand::where('company_id', $user->company_id)->get();
        $categories = InvCategory::when(!$user->isMasterAdmin(), function($q) use ($user){
            $q->whereHas('brand', function($qq) use ($user){ $qq->where('company_id',$user->company_id); });
        })->get();
        $divisions = InvDivision::when(!$user->isMasterAdmin(), function($q) use ($user){
            $q->whereHas('category.brand', function($qq) use ($user){ $qq->where('company_id',$user->company_id); });
        })->get();
        $groups = InvGroup::when(!$user->isMasterAdmin(), function($q) use ($user){
            $q->whereHas('division.category.brand', function($qq) use ($user){ $qq->where('company_id',$user->company_id); });
        })->get();
        $units = InventoryUnit::when(!$user->isMasterAdmin(), function($q) use ($user){ $q->where('company_id',$user->company_id); })->get();
        $suppliers = Supplier::when(!$user->isMasterAdmin(), function($q) use ($user){ $q->where('company_id',$user->company_id); })->get();
        $locations = InventoryLocation::when(!$user->isMasterAdmin(), function($q) use ($user){ $q->where('company_id',$user->company_id); })->get();
        return view('admin.inventory.items.create', compact('item','brands','categories','divisions','groups','units','suppliers','locations'));
    }

    public function update(Request $request, InventoryItem $inventory_item)
    {
        $request->validate([
            'brand_id' => 'nullable|exists:brands,id',
            'inv_category_id' => 'required|exists:inv_categories,id',
            'inv_division_id' => 'required|exists:inv_divisions,id',
            'inv_group_id' => 'required|exists:inv_groups,id',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'location_id' => 'nullable|exists:inventory_locations,id',
            'code' => 'nullable|string|max:100',
            'name' => 'required|string|max:255',
            'barcode' => 'nullable|string|max:100',
            'buying_unit_id' => 'required|exists:inventory_units,id',
            'stock_unit_id' => 'required|exists:inventory_units,id',
            'usage_unit_id' => 'required|exists:inventory_units,id',
            'buy_to_stock_factor' => 'required|numeric|min:0.000001',
            'stock_to_usage_factor' => 'required|numeric|min:0.000001',
            'cost_price' => 'nullable|numeric|min:0',
            'current_stock' => 'nullable|numeric|min:0',
            'minimum_stock' => 'nullable|numeric|min:0',
            'is_active' => 'boolean',
            'notes' => 'nullable|string',
        ]);
        $inventory_item->update($request->only([
            'brand_id','inv_category_id','inv_division_id','inv_group_id','supplier_id','location_id',
            'code','name','barcode','buying_unit_id','stock_unit_id','usage_unit_id','buy_to_stock_factor',
            'stock_to_usage_factor','cost_price','current_stock','minimum_stock','is_active','notes'
        ]));
        return redirect()->route('admin.inventory-items.index')->with('success','Inventory item updated');
    }

    public function destroy(InventoryItem $inventory_item)
    {
        $inventory_item->delete();
        return redirect()->route('admin.inventory-items.index')->with('success','Inventory item deleted');
    }

    public function history(InventoryItem $inventory_item, Request $request)
    {
        $user = Auth::user();
        if (!$user->isMasterAdmin() && $inventory_item->company_id !== $user->company_id) {
            abort(403);
        }

        $query = InventoryAdjustment::with(['location','user'])
            ->where('company_id', $user->company_id)
            ->where('inventory_item_id', $inventory_item->id)
            ->latest();

        if ($request->filled('location_id')) {
            $query->where('location_id', $request->integer('location_id'));
        }
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date('date_from'));
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date('date_to'));
        }

        // Default to adjustments only in item history as requested
        $type = $request->get('type', 'adjustments');
        if ($type === 'purchases') {
            $query->where('reason', 'purchase');
        } elseif ($type === 'adjustments') {
            $query->where(function($q){
                $q->whereNull('reason')
                  ->orWhereNotIn('reason', ['purchase','sale_deduction','sale_delete_restore']);
            });
        } elseif ($type === 'deductions') {
            $query->whereIn('reason', ['sale_deduction','sale_delete_restore']);
        }

        $adjustments = $query->paginate(20)->withQueryString();
        $locations = InventoryLocation::where('company_id', $user->company_id)->orderBy('name')->get();

        return view('admin.inventory.items.history', compact('inventory_item','adjustments','locations','type'));
    }

    public function usedIn(InventoryItem $inventory_item, Request $request)
    {
        $user = Auth::user();
        if (!$user->isMasterAdmin() && $inventory_item->company_id !== $user->company_id) {
            abort(403);
        }

        $products = Product::query()
            ->with(['group.division.category.brand'])
            ->whereHas('recipeItems', function($q) use ($inventory_item) {
                $q->where('inventory_item_id', $inventory_item->id);
            })
            ->whereHas('group.division.category.brand', function($qq) use ($user) {
                if (!$user->isMasterAdmin()) {
                    $qq->where('company_id', $user->company_id);
                }
            })
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('admin.inventory.items.used-in', [
            'inventory_item' => $inventory_item,
            'products' => $products,
        ]);
    }
}

