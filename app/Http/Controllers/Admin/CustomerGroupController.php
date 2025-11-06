<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CustomerGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CustomerGroupController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $query = CustomerGroup::query();
        if (!$user->isMasterAdmin()) {
            $query->where('company_id', $user->company_id);
        }
        $groups = $query->orderBy('name')->paginate(20);
        return view('admin.customer-groups.index', compact('groups'));
    }

    public function create()
    {
        return view('admin.customer-groups.create');
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);
        $validated['company_id'] = $user->company_id;
        $validated['is_active'] = $validated['is_active'] ?? true;
        CustomerGroup::create($validated);
        return redirect()->route('admin.customer-groups.index')->with('success', 'Customer group created successfully.');
    }

    public function edit(CustomerGroup $customer_group)
    {
        $user = Auth::user();
        if (!$user->isMasterAdmin() && $customer_group->company_id !== $user->company_id) {
            abort(403, 'Access denied.');
        }
        return view('admin.customer-groups.create', ['group' => $customer_group]);
    }

    public function update(Request $request, CustomerGroup $customer_group)
    {
        $user = Auth::user();
        if (!$user->isMasterAdmin() && $customer_group->company_id !== $user->company_id) {
            abort(403, 'Access denied.');
        }
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);
        $customer_group->update($validated);
        return redirect()->route('admin.customer-groups.index')->with('success', 'Customer group updated successfully.');
    }

    public function destroy(CustomerGroup $customer_group)
    {
        $user = Auth::user();
        if (!$user->isMasterAdmin() && $customer_group->company_id !== $user->company_id) {
            abort(403, 'Access denied.');
        }
        $customer_group->delete();
        return redirect()->route('admin.customer-groups.index')->with('success', 'Customer group deleted successfully.');
    }
}

