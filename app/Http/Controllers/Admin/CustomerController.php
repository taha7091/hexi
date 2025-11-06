<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\CustomerGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        $query = Customer::with('group');
        if (!$user->isMasterAdmin()) {
            $query->where('company_id', $user->company_id);
        }

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%$search%")
                  ->orWhere('email', 'like', "%$search%")
                  ->orWhere('phone', 'like', "%$search%")
                  ->orWhere('code', 'like', "%$search%");
            });
        }

        if ($groupId = $request->input('group_id')) {
            $query->where('customer_group_id', $groupId);
        }

        $customers = $query->orderBy('name')->paginate(20);

        // Load groups for filter
        $groupsQuery = CustomerGroup::query();
        if (!$user->isMasterAdmin()) {
            $groupsQuery->where('company_id', $user->company_id);
        }
        $groups = $groupsQuery->orderBy('name')->get();

        return view('admin.customers.index', compact('customers', 'groups'));
    }

    public function create()
    {
        $user = Auth::user();
        $groups = CustomerGroup::where('company_id', $user->company_id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
        return view('admin.customers.create', compact('groups'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $validated = $request->validate([
            'customer_group_id' => 'nullable|exists:customer_groups,id',
            'code' => 'nullable|string|max:100',
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'balance' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        // Ensure group belongs to this company
        if (!empty($validated['customer_group_id'])) {
            $group = CustomerGroup::findOrFail($validated['customer_group_id']);
            if (!$user->isMasterAdmin() && $group->company_id !== $user->company_id) {
                abort(403, 'Access denied.');
            }
        }

        $validated['company_id'] = $user->company_id;
        $validated['is_active'] = $validated['is_active'] ?? true;
        $validated['balance'] = $validated['balance'] ?? 0;

        $customer = Customer::create($validated);

        return redirect()->route('admin.customers.index')
            ->with('success', 'Customer created successfully.');
    }

    public function show(Customer $customer)
    {
        $user = Auth::user();
        if (!$user->isMasterAdmin() && $customer->company_id !== $user->company_id) {
            abort(403, 'Access denied.');
        }
        $customer->load('group');
        return view('admin.customers.show', compact('customer'));
    }

    public function edit(Customer $customer)
    {
        $user = Auth::user();
        if (!$user->isMasterAdmin() && $customer->company_id !== $user->company_id) {
            abort(403, 'Access denied.');
        }
        $groups = CustomerGroup::where('company_id', $user->company_id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
        // Reuse the create view for editing to avoid duplicate rendering
        return view('admin.customers.create', compact('customer', 'groups'));
    }

    public function update(Request $request, Customer $customer)
    {
        $user = Auth::user();
        if (!$user->isMasterAdmin() && $customer->company_id !== $user->company_id) {
            abort(403, 'Access denied.');
        }

        $validated = $request->validate([
            'customer_group_id' => 'nullable|exists:customer_groups,id',
            'code' => 'nullable|string|max:100',
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'balance' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        if (!empty($validated['customer_group_id'])) {
            $group = CustomerGroup::findOrFail($validated['customer_group_id']);
            if (!$user->isMasterAdmin() && $group->company_id !== $user->company_id) {
                abort(403, 'Access denied.');
            }
        }

        $customer->update($validated);

        return redirect()->route('admin.customers.index')
            ->with('success', 'Customer updated successfully.');
    }

    public function destroy(Customer $customer)
    {
        $user = Auth::user();
        if (!$user->isMasterAdmin() && $customer->company_id !== $user->company_id) {
            abort(403, 'Access denied.');
        }

        $customer->delete();

        return redirect()->route('admin.customers.index')
            ->with('success', 'Customer deleted successfully.');
    }
}

