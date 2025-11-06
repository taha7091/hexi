<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InventorySupplierController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $suppliers = Supplier::when(!$user->isMasterAdmin(), function($q) use ($user) {
            $q->where('company_id', $user->company_id);
        })->orderBy('name')->paginate(20);
        return view('admin.inventory.suppliers.index', compact('suppliers'));
    }

    public function create()
    {
        return view('admin.inventory.suppliers.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:100',
            'address' => 'nullable|string|max:500',
            'is_active' => 'boolean',
        ]);
        $data = $request->only(['name','contact_person','email','phone','address','is_active']);
        $data['company_id'] = Auth::user()->company_id;
        Supplier::create($data);
        return redirect()->route('admin.inventory-suppliers.index')->with('success','Supplier created');
    }

    public function edit(Supplier $inventory_supplier)
    {
        $supplier = $inventory_supplier;
        return view('admin.inventory.suppliers.create', compact('supplier'));
    }

    public function update(Request $request, Supplier $inventory_supplier)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:100',
            'address' => 'nullable|string|max:500',
            'is_active' => 'boolean',
        ]);
        $inventory_supplier->update($request->only(['name','contact_person','email','phone','address','is_active']));
        return redirect()->route('admin.inventory-suppliers.index')->with('success','Supplier updated');
    }

    public function destroy(Supplier $inventory_supplier)
    {
        $inventory_supplier->delete();
        return redirect()->route('admin.inventory-suppliers.index')->with('success','Supplier deleted');
    }
}

