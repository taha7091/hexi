<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\InventoryPurchase;
use App\Models\InventoryPurchaseItem;
use App\Models\InventoryItem;
use App\Models\InventoryAdjustment;
use App\Models\Supplier;
use App\Models\InventoryLocation;

class InventoryPurchaseController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        $purchases = InventoryPurchase::with(['supplier','location'])
            ->where('company_id', $user->company_id)
            ->orderByDesc('id')
            ->paginate(20);

        return view('admin.inventory.purchases.index', compact('purchases'));
    }

    public function create()
    {
        $user = Auth::user();
        $suppliers = Supplier::where('company_id', $user->company_id)->orderBy('name')->get();
        $locations = InventoryLocation::where('company_id', $user->company_id)->orderBy('name')->get();
        return view('admin.inventory.purchases.create', compact('suppliers','locations'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'supplier_id' => ['required','integer','exists:suppliers,id'],
            'location_id' => ['required','integer','exists:inventory_locations,id'],
            'invoice_number' => ['required','string','max:255'],
            'invoice_date' => ['nullable','date'],
            'tax_amount' => ['nullable','numeric','min:0'],
            'items' => ['required','array','min:1'],
            'items.*.inventory_item_id' => ['required','integer','exists:inventory_items,id'],
            'items.*.quantity_buying' => ['required','numeric','min:0.000001'],
            'items.*.unit_cost' => ['nullable','numeric','min:0'],
        ]);

        return DB::transaction(function() use ($user, $validated) {
            $subtotal = 0.0;
            foreach ($validated['items'] as $it) {
                $qty = (float)$it['quantity_buying'];
                $cost = (float)($it['unit_cost'] ?? 0);
                $subtotal += $qty * $cost;
            }
            $tax = (float)($validated['tax_amount'] ?? 0);
            $total = $subtotal + $tax;

            $purchase = InventoryPurchase::create([
                'company_id' => $user->company_id,
                'supplier_id' => $validated['supplier_id'],
                'location_id' => $validated['location_id'],
                'invoice_number' => $validated['invoice_number'],
                'invoice_date' => $validated['invoice_date'] ?? now(),
                'subtotal' => round($subtotal, 4),
                'tax_amount' => round($tax, 4),
                'total_amount' => round($total, 4),
                'status' => 'received',
                'notes' => request('notes'),
            ]);

            foreach ($validated['items'] as $it) {
                $qty = (float)$it['quantity_buying'];
                $cost = (float)($it['unit_cost'] ?? 0);
                $line = $qty * $cost;

                InventoryPurchaseItem::create([
                    'inventory_purchase_id' => $purchase->id,
                    'inventory_item_id' => $it['inventory_item_id'],
                    'quantity_buying' => $qty,
                    'unit_cost' => round($cost, 4),
                    'line_total' => round($line, 4),
                ]);

                // Stock update: buying -> stock using buy_to_stock_factor
                $inv = InventoryItem::where('company_id', $user->company_id)
                    ->findOrFail($it['inventory_item_id']);

                $factor = (float)($inv->buy_to_stock_factor ?? 1.0);
                if ($factor <= 0) { $factor = 1.0; }
                $addStockUnits = round($qty * $factor, 6);

                $before = (float)$inv->current_stock;
                $after = round($before + $addStockUnits, 6);
                $inv->update(['current_stock' => $after]);

                InventoryAdjustment::create([
                    'company_id' => $user->company_id,
                    'user_id' => $user->id,
                    'inventory_item_id' => $inv->id,
                    'location_id' => $purchase->location_id,
                    'adjustment_type' => 'add',
                    'quantity_before' => $before,
                    'quantity_after' => $after,
                    'adjustment_amount' => $addStockUnits,
                    'reason' => 'purchase',
                    'notes' => 'Invoice '.$purchase->invoice_number,
                ]);
            }

            return redirect()->route('admin.inventory-purchases.show', $purchase->id)
                ->with('success', 'Purchase saved and inventory updated.');
        });
    }

    public function show(InventoryPurchase $inventory_purchase)
    {
        $user = Auth::user();
        abort_unless($inventory_purchase->company_id === $user->company_id, 403);
        $purchase = $inventory_purchase->load(['supplier','location','items.inventoryItem']);
        return view('admin.inventory.purchases.show', compact('purchase'));
    }
}

