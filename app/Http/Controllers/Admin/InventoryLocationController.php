<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InventoryLocation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InventoryLocationController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $locations = InventoryLocation::when(!$user->isMasterAdmin(), function($q) use ($user) {
            $q->where('company_id', $user->company_id);
        })->orderBy('name')->paginate(20);
        return view('admin.inventory.locations.index', compact('locations'));
    }

    public function create()
    {
        return view('admin.inventory.locations.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'nullable|string|max:100',
            'description' => 'nullable|string|max:500',
            'is_active' => 'boolean',
        ]);
        $data = $request->only(['name','type','description','is_active']);
        $data['company_id'] = Auth::user()->company_id;
        InventoryLocation::create($data);
        return redirect()->route('admin.inventory-locations.index')->with('success','Location created');
    }

    public function edit(InventoryLocation $inventory_location)
    {
        $location = $inventory_location;
        return view('admin.inventory.locations.create', compact('location'));
    }

    public function update(Request $request, InventoryLocation $inventory_location)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'nullable|string|max:100',
            'description' => 'nullable|string|max:500',
            'is_active' => 'boolean',
        ]);
        $inventory_location->update($request->only(['name','type','description','is_active']));
        return redirect()->route('admin.inventory-locations.index')->with('success','Location updated');
    }

    public function destroy(InventoryLocation $inventory_location)
    {
        $inventory_location->delete();
        return redirect()->route('admin.inventory-locations.index')->with('success','Location deleted');
    }
}

