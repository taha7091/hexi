<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\CashierPrivilege;
use App\Models\User;
use App\Http\Controllers\Controller;

class CashierPrivilegeController extends Controller
{
    /**
     * Display cashier privileges management page
     */
    public function index()
    {
        $user = Auth::user();

        // Only managers and admins can manage cashier privileges
        if (!in_array($user->role, ['manager', 'admin', 'master_admin'])) {
            abort(403, 'Access denied. Manager privileges required.');
        }

        // Get cashiers in the same company
        $cashiers = User::where('company_id', $user->company_id)
                       ->where('role', 'cashier')
                       ->with('cashierPrivileges')
                       ->get();

        return view('admin.cashier-privileges.index', compact('cashiers'));
    }

    /**
     * Show form to edit cashier privileges
     */
    public function edit(User $cashier)
    {
        $user = Auth::user();

        // Check access
        if (!in_array($user->role, ['manager', 'admin', 'master_admin'])) {
            abort(403, 'Access denied. Manager privileges required.');
        }

        if ($cashier->company_id !== $user->company_id) {
            abort(403, 'You can only manage cashiers from your own company.');
        }

        if ($cashier->role !== 'cashier') {
            abort(403, 'You can only manage cashier privileges.');
        }

        // Get or create privileges
        $privileges = $cashier->cashierPrivileges ?? new CashierPrivilege(CashierPrivilege::getDefaultPrivileges());

        return view('admin.cashier-privileges.edit', compact('cashier', 'privileges'));
    }

    /**
     * Update cashier privileges
     */
    public function update(Request $request, User $cashier)
    {
        $user = Auth::user();

        // Check access
        if (!in_array($user->role, ['manager', 'admin', 'master_admin'])) {
            abort(403, 'Access denied. Manager privileges required.');
        }

        if ($cashier->company_id !== $user->company_id) {
            abort(403, 'You can only manage cashiers from your own company.');
        }

        if ($cashier->role !== 'cashier') {
            abort(403, 'You can only manage cashier privileges.');
        }

        $validated = $request->validate([
            'can_view_older_sales' => 'boolean',
            'can_process_refunds' => 'boolean',
            'can_apply_discounts' => 'boolean',
            'can_void_transactions' => 'boolean',
            'can_modify_prices' => 'boolean',
            'can_press_end_of_day' => 'boolean',
            'can_view_daily_reports' => 'boolean',
            'can_view_cash_drawer' => 'boolean',
            'can_add_products' => 'boolean',
            'can_edit_products' => 'boolean',
            'can_manage_inventory' => 'boolean',
            'can_access_settings' => 'boolean',
            'can_backup_data' => 'boolean',
        ]);

        // Convert null values to false for checkboxes
        foreach ($validated as $key => $value) {
            $validated[$key] = $value ?? false;
        }

        // Update or create privileges
        CashierPrivilege::updateOrCreate(
            [
                'company_id' => $user->company_id,
                'cashier_id' => $cashier->id,
            ],
            array_merge($validated, [
                'managed_by' => $user->id,
            ])
        );

        return redirect()->route('admin.cashier-privileges.index')
                        ->with('success', 'Cashier privileges updated successfully for ' . $cashier->name . '!');
    }

    public function getUserPrivileges(Request $request)
    {
        $user = $request->user();
        $privileges = $user->cashierPrivileges;

        if (!$privileges) {
            // If no specific privileges are set, use role-based defaults
            $privileges = new CashierPrivilege(CashierPrivilege::getDefaultPrivileges());
        }

        return response()->json(['privileges' => $privileges]);
    }
}