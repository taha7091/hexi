<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InventoryAdjustment;
use App\Models\InventoryItem;
use App\Models\InventoryLocation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\User;

class InventoryAdjustmentController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        $query = InventoryAdjustment::with(['inventoryItem', 'location', 'user'])
            ->where('company_id', $user->company_id)
            ->latest();

        if ($request->filled('location_id')) {
            $query->where('location_id', $request->integer('location_id'));
        }

        if ($request->filled('item')) {
            $term = $request->string('item');
            $query->whereHas('inventoryItem', function ($q) use ($term) {
                $q->where('name', 'like', '%' . $term . '%')
                  ->orWhere('code', 'like', '%' . $term . '%');
            });
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date('date_from'));
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date('date_to'));
        }

        // Type filter: default to 'adjustments' (exclude purchases & sales deductions)
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
        } // type 'all' shows everything

        $adjustments = $query->paginate(20)->withQueryString();
        $locations = InventoryLocation::where('company_id', $user->company_id)
            ->orderBy('name')
            ->get();

        return view('admin.inventory.adjustments.index', compact('adjustments', 'locations', 'type'));
    }

    public function create()
    {
        $user = Auth::user();
        $locations = InventoryLocation::where('company_id', $user->company_id)
            ->orderBy('name')
            ->get();
        return view('admin.inventory.adjustments.create', compact('locations'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'inventory_item_id' => 'required|integer|exists:inventory_items,id',
            'location_id' => 'required|integer|exists:inventory_locations,id',
            'adjustment_type' => 'required|in:add,subtract,set',
            'quantity' => 'required|numeric|min:0',
            'reason' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:1000',
        ]);

        $item = InventoryItem::where('company_id', $user->company_id)
            ->findOrFail($validated['inventory_item_id']);
        $location = InventoryLocation::where('company_id', $user->company_id)
            ->findOrFail($validated['location_id']);

        DB::beginTransaction();
        try {
            $before = (float) $item->current_stock;
            $qty = (float) $validated['quantity'];

            switch ($validated['adjustment_type']) {
                case 'add':
                    $after = round($before + $qty, 6);
                    break;
                case 'subtract':
                    $after = round($before - $qty, 6);
                    break;
                case 'set':
                    $after = round($qty, 6);
                    break;
                default:
                    $after = $before;
            }

            $delta = round($after - $before, 6);

            $item->update(['current_stock' => $after]);

            $adjustment = InventoryAdjustment::create([
                'company_id' => $user->company_id,
                'user_id' => $user->id,
                'inventory_item_id' => $item->id,
                'location_id' => $location->id,
                'adjustment_type' => $validated['adjustment_type'],
                'quantity_before' => $before,
                'quantity_after' => $after,
                'adjustment_amount' => $delta,
                'reason' => $validated['reason'] ?? null,
                'notes' => $validated['notes'] ?? null,
            ]);

            DB::commit();

            return redirect()
                ->route('admin.inventory-adjustments.show', $adjustment)
                ->with('success', 'Inventory adjusted successfully.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()
                ->with('error', 'Failed to adjust inventory: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function show(InventoryAdjustment $inventory_adjustment)
    {
        $user = Auth::user();
        if ($inventory_adjustment->company_id !== $user->company_id && !$user->isMasterAdmin()) {
            abort(403);
        }
        $inventory_adjustment->load(['inventoryItem', 'location', 'user']);
        return view('admin.inventory.adjustments.show', compact('inventory_adjustment'));
    }

    public function count(Request $request)
    {
        $user = Auth::user();

        // Preview a specific stock count batch
        if ($request->filled('batch')) {
            $batch = $request->query('batch');
            $adjustments = InventoryAdjustment::with(['inventoryItem','location','user'])
                ->where('company_id', $user->company_id)
                ->where('reason', 'stock_count')
                ->where('notes', 'like', '%Batch ' . $batch . '%')
                ->orderBy('inventory_item_id')
                ->get();
            if ($adjustments->isEmpty()) {
                return redirect()->route('admin.inventory-adjustments.count')
                    ->with('error', 'Stock count batch not found or empty.');
            }
            return view('admin.inventory.adjustments.stock-count', [
                'batch' => $batch,
                'batchAdjustments' => $adjustments,
            ]);
        }

        // History of stock count batches (default landing)
        if (!$request->boolean('new')) {
            $rows = InventoryAdjustment::select(['id','inventory_item_id','location_id','user_id','created_at','notes'])
                ->where('company_id', $user->company_id)
                ->where('reason', 'stock_count')
                ->orderByDesc('created_at')
                ->limit(500)
                ->get();

            $groups = [];
            foreach ($rows as $row) {
                $notes = (string)($row->notes ?? '');
                $code = null;
                if (preg_match('/Batch\s+(SC-[A-Za-z0-9\-]+)/', $notes, $m)) {
                    $code = $m[1];
                }
                if ($code) {
                    if (!isset($groups[$code])) {
                        $groups[$code] = [
                            'code' => $code,
                            'count' => 0,
                            'created_at' => $row->created_at,
                            'location_id' => $row->location_id,
                            'user_id' => $row->user_id,
                        ];
                    }
                    $groups[$code]['count']++;
                    if ($row->created_at > $groups[$code]['created_at']) {
                        $groups[$code]['created_at'] = $row->created_at;
                    }
                }
            }

            $groupCollection = collect($groups)->sortByDesc(fn($g) => $g['created_at'])->values();
            $locationIds = $groupCollection->pluck('location_id')->filter()->unique()->values();
            $userIds = $groupCollection->pluck('user_id')->filter()->unique()->values();
            $locationsMap = InventoryLocation::where('company_id', $user->company_id)
                ->whereIn('id', $locationIds)->get()->keyBy('id');
            $usersMap = User::whereIn('id', $userIds)->get()->keyBy('id');

            return view('admin.inventory.adjustments.stock-count', [
                'groups' => $groupCollection,
                'locationsMap' => $locationsMap,
                'usersMap' => $usersMap,
            ]);
        }

        // New stock count grid
        $locations = InventoryLocation::where('company_id', $user->company_id)->orderBy('name')->get();
        $query = InventoryItem::where('company_id', $user->company_id)->with('stockUnit');
        if ($request->filled('search')) {
            $search = $request->string('search');
            $query->where(function($q) use ($search){
                $q->where('name','like','%'.$search.'%')
                  ->orWhere('code','like','%'.$search.'%')
                  ->orWhere('barcode','like','%'.$search.'%');
            });
        }
        $items = $query->orderBy('name')->paginate(50)->withQueryString();

        return view('admin.inventory.adjustments.stock-count', [
            'items' => $items,
            'locations' => $locations,
        ]);
    }

    public function countApply(Request $request)
    {
        $user = Auth::user();
        $validated = $request->validate([
            'location_id' => 'required|integer|exists:inventory_locations,id',
            'new_qty' => 'required|array',
            'new_qty.*' => 'nullable|numeric|min:0',
            'reason' => 'nullable|string|max:255',
        ]);

        $location = InventoryLocation::where('company_id', $user->company_id)->findOrFail($validated['location_id']);

        DB::beginTransaction();
        try {
            $ids = array_keys($validated['new_qty']);
            $items = InventoryItem::where('company_id', $user->company_id)->whereIn('id', $ids)->get();
            $created = [];
            $batchCode = 'SC-' . now()->format('Ymd-His') . '-' . ($user->id ?? '0') . '-' . Str::upper(Str::random(4));
            foreach ($items as $item) {
                $new = $validated['new_qty'][$item->id] ?? null;
                if ($new === null || $new === '') continue;
                $new = round((float)$new, 6);
                $before = round((float)$item->current_stock, 6);
                if ($new === $before) continue;
                $after = $new;
                $delta = round($after - $before, 6);

                $item->update(['current_stock' => $after]);

                $adj = InventoryAdjustment::create([
                    'company_id' => $user->company_id,
                    'user_id' => $user->id,
                    'inventory_item_id' => $item->id,
                    'location_id' => $location->id,
                    'adjustment_type' => 'set',
                    'quantity_before' => $before,
                    'quantity_after' => $after,
                    'adjustment_amount' => $delta,
                    'reason' => $validated['reason'] ?? 'stock_count',
                    'notes' => 'Stock count applied (Batch ' . $batchCode . ')',
                ]);
                $created[] = $adj->id;
            }
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to apply stock count: '.$e->getMessage());
        }

        return redirect()->route('admin.inventory-adjustments.count', ['batch' => $batchCode])
            ->with('success', 'Stock count applied successfully.')
            ->with('created_adjustments', $created);
    }
}

