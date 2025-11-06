<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InventoryUnit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InventoryUnitController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $units = InventoryUnit::when(!$user->isMasterAdmin(), function($q) use ($user) {
            $q->where('company_id', $user->company_id);
        })->orderBy('name')->paginate(20);
        return view('admin.inventory.units.index', compact('units'));
    }

    public function create()
    {
        return view('admin.inventory.units.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'symbol' => 'nullable|string|max:20',
            'is_active' => 'boolean',
        ]);
        $data = $request->only(['name','symbol','is_active']);
        $data['company_id'] = Auth::user()->company_id;
        InventoryUnit::create($data);
        return redirect()->route('admin.inventory-units.index')->with('success', 'Unit created');
    }

    public function edit(InventoryUnit $inventory_unit)
    {
        $unit = $inventory_unit;
        return view('admin.inventory.units.create', compact('unit'));
    }

    public function update(Request $request, InventoryUnit $inventory_unit)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'symbol' => 'nullable|string|max:20',
            'is_active' => 'boolean',
        ]);
        $inventory_unit->update($request->only(['name','symbol','is_active']));
        return redirect()->route('admin.inventory-units.index')->with('success', 'Unit updated');
    }

    public function destroy(InventoryUnit $inventory_unit)
    {
        $inventory_unit->delete();
        return redirect()->route('admin.inventory-units.index')->with('success', 'Unit deleted');
    }
}

