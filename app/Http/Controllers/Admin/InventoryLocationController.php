<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InventoryAdjustment;
use App\Models\InventoryItem;
use App\Models\InventoryLocation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class InventoryAdjustmentController extends Controller
{
    /**
     * Display a listing of inventory adjustments.
     */
    public function index()
    {
        $user = Auth::user();
        $adjustments = InventoryAdjustment::with(['inventoryItem', 'location', 'user'])
            ->when(!$user->isMasterAdmin(), function($q) use ($user) {
                $q->whereHas('location', function($subQ) use ($user) {
                    $subQ->where('company_id', $user->company_id);
                });
            })
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.inventory.adjustments.index', compact('adjustments'));
    }

    /**
     * Show the form for creating a new adjustment.
     */
    public function create()
    {
        $user = Auth::user();
        $locations = InventoryLocation::when(!$user->isMasterAdmin(), function($q) use ($user) {
            $q->where('company_id', $user->company_id);
        })->orderBy('name')->get();

        $items = InventoryItem::orderBy('name')->get(); // Or filter by company if needed

        return view('admin.inventory.adjustments.create', compact('locations', 'items'));
    }

    /**
     * Store a newly created adjustment.
     */
    public function store(Request $request)
    {
        $request->validate([
            'inventory_item_id' => 'required|exists:inventory_items,id',
            'location_id' => 'required|exists:inventory_locations,id',
            'adjustment_amount' => 'required|numeric',
            'reason' => 'nullable|string|max:500',
        ]);

        $user = Auth::user();
        $item = InventoryItem::findOrFail($request->inventory_item_id);
        $location = InventoryLocation::findOrFail($request->location_id);

        // Ensure location belongs to user's company
        if (!$user->isMasterAdmin() && $location->company_id !== $user->company_id) {
            abort(403, 'Unauthorized');
        }

        $currentStock = $item->stocks()->where('location_id', $request->location_id)->first()->quantity ?? 0;
        $newStock = $currentStock + $request->adjustment_amount;

        InventoryAdjustment::create([
            'inventory_item_id' => $request->inventory_item_id,
            'location_id' => $request->location_id,
            'quantity_before' => $currentStock,
            'adjustment_amount' => $request->adjustment_amount,
            'quantity_after' => $newStock,
            'user_id' => $user->id,
            'reason' => $request->reason,
        ]);

        // Update stock
        $item->stocks()->where('location_id', $request->location_id)->update(['quantity' => $newStock]);

        return redirect()->route('admin.inventory-adjustments.index')->with('success', 'Adjustment created');
    }

    /**
     * Display the specified adjustment.
     */
    public function show(InventoryAdjustment $inventory_adjustment)
    {
        $adjustment = $inventory_adjustment->load(['inventoryItem', 'location', 'user']);
        return view('admin.inventory.adjustments.show', compact('adjustment'));
    }

    /**
     * Show the form for editing the specified adjustment.
     */
    public function edit(InventoryAdjustment $inventory_adjustment)
    {
        $adjustment = $inventory_adjustment;
        $user = Auth::user();
        $locations = InventoryLocation::when(!$user->isMasterAdmin(), function($q) use ($user) {
            $q->where('company_id', $user->company_id);
        })->orderBy('name')->get();

        $items = InventoryItem::orderBy('name')->get();

        return view('admin.inventory.adjustments.edit', compact('adjustment', 'locations', 'items'));
    }

    /**
     * Update the specified adjustment.
     */
    public function update(Request $request, InventoryAdjustment $inventory_adjustment)
    {
        $request->validate([
            'adjustment_amount' => 'required|numeric',
            'reason' => 'nullable|string|max:500',
        ]);

        $user = Auth::user();
        $location = $inventory_adjustment->location;

        // Ensure location belongs to user's company
        if (!$user->isMasterAdmin() && $location->company_id !== $user->company_id) {
            abort(403, 'Unauthorized');
        }

        $currentStock = $inventory_adjustment->quantity_after;
        $newAdjustment = $request->adjustment_amount;
        $newStock = $inventory_adjustment->quantity_before + $newAdjustment;

        $inventory_adjustment->update([
            'adjustment_amount' => $newAdjustment,
            'quantity_after' => $newStock,
            'reason' => $request->reason,
        ]);

        // Update stock
        $inventory_adjustment->inventoryItem->stocks()->where('location_id', $inventory_adjustment->location_id)->update(['quantity' => $newStock]);

        return redirect()->route('admin.inventory-adjustments.index')->with('success', 'Adjustment updated');
    }

    /**
     * Remove the specified adjustment.
     */
    public function destroy(InventoryAdjustment $inventory_adjustment)
    {
        $user = Auth::user();
        $location = $inventory_adjustment->location;

        // Ensure location belongs to user's company
        if (!$user->isMasterAdmin() && $location->company_id !== $user->company_id) {
            abort(403, 'Unauthorized');
        }

        $inventory_adjustment->delete();

        return redirect()->route('admin.inventory-adjustments.index')->with('success', 'Adjustment deleted');
    }

    /**
     * Handle stock count views and creation.
     */
    public function count(Request $request)
    {
        $user = Auth::user();

        // Fetch locations with company filtering (same as InventoryLocationController)
        $locations = InventoryLocation::when(!$user->isMasterAdmin(), function($q) use ($user) {
            $q->where('company_id', $user->company_id);
        })->orderBy('name')->get();

        if ($request->has('batch')) {
            // Batch preview
            $batchCode = $request->batch;
            $batchAdjustments = InventoryAdjustment::where('batch_code', $batchCode)
                ->with(['inventoryItem', 'location', 'user'])
                ->orderBy('created_at', 'desc')
                ->get();
            return view('admin.inventory.adjustments.count', compact('batchAdjustments', 'locations'));
        }

        if ($request->has('new') && $request->location_id) {
            // New stock count: Filter items by selected location
            $locationId = $request->location_id;
            $query = InventoryItem::with(['stockUnit', 'stocks' => function($q) use ($locationId) {
                $q->where('location_id', $locationId);
            }])
            ->whereHas('stocks', function($q) use ($locationId) {
                $q->where('location_id', $locationId);
            });

            if ($request->search) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('code', 'like', "%{$search}%")
                      ->orWhere('barcode', 'like', "%{$search}%");
                });
            }

            $items = $query->paginate(50);
            return view('admin.inventory.adjustments.count', compact('items', 'locations'));
        }

        // Default: Show groups/history
        $groups = InventoryAdjustment::select('batch_code', 'location_id', 'user_id', 'created_at', DB::raw('count(*) as count'))
            ->groupBy('batch_code', 'location_id', 'user_id', 'created_at')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $usersMap = collect(); // Populate with User::find($groups->pluck('user_id')) if needed
        $locationsMap = $locations->keyBy('id');

        return view('admin.inventory.adjustments.count', compact('groups', 'usersMap', 'locationsMap', 'locations'));
    }

    /**
     * Apply stock count adjustments.
     */
    public function countApply(Request $request)
    {
        $request->validate([
            'location_id' => 'required|exists:inventory_locations,id',
            'new_qty' => 'required|array',
            'new_qty.*' => 'numeric|min:0',
        ]);

        $locationId = $request->location_id;
        $user = Auth::user();
        $location = InventoryLocation::findOrFail($locationId);

        // Ensure location belongs to user's company
        if (!$user->isMasterAdmin() && $location->company_id !== $user->company_id) {
            abort(403, 'Unauthorized');
        }

        $batchCode = 'SC-' . now()->format('YmdHis') . '-' . strtoupper(substr(md5(uniqid()), 0, 6));

        $adjustments = [];
        foreach ($request->new_qty as $itemId => $newQty) {
            $item = InventoryItem::findOrFail($itemId);
            $currentStock = $item->stocks()->where('location_id', $locationId)->first()->quantity ?? 0;
            $adjustmentAmount = $newQty - $currentStock;

            if ($adjustmentAmount != 0) {
                $adjustment = InventoryAdjustment::create([
                    'inventory_item_id' => $itemId,
                    'location_id' => $locationId,
                    'quantity_before' => $currentStock,
                    'adjustment_amount' => $adjustmentAmount,
                    'quantity_after' => $newQty,
                    'batch_code' => $batchCode,
                    'user_id' => $user->id,
                    'reason' => 'Stock Count Adjustment',
                ]);
                $adjustments[] = $adjustment->id;

                // Update stock
                $item->stocks()->where('location_id', $locationId)->update(['quantity' => $newQty]);
            }
        }

        return redirect()->route('admin.inventory-adjustments.count')->with('created_adjustments', $adjustments);
    }
}
