<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PurchaseOrder;

class PurchaseOrderController extends Controller
{
    public function index()
    {
        return PurchaseOrder::all();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'order_number' => 'required|string|max:255|unique:purchase_orders',
            'supplier_id' => 'required|integer', // Adjust to actual supplier model/validation
            'order_date' => 'required|date',
            'total_amount' => 'required|numeric|min:0',
            // other fields as necessary
        ]);

        return PurchaseOrder::create($validated);
    }

    public function show(PurchaseOrder $purchaseOrder)
    {
        return $purchaseOrder;
    }

    public function update(Request $request, PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->update($request->all());
        return $purchaseOrder;
    }

    public function destroy(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->delete();
        return response()->noContent();
    }
}
