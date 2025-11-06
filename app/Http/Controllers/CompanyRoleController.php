<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\CompanyRole;
use App\Models\RolePrivilege;

class CompanyRoleController extends Controller
{
    /**
     * Display role setup page
     */
    public function index()
    {
        $user = Auth::user();

        // Only company admins can manage roles
        if (!$user->isAdmin() && !$user->isMasterAdmin()) {
            abort(403, 'Access denied. Admin privileges required.');
        }

        // Get roles for the company
        $roles = CompanyRole::where('company_id', $user->company_id)
                           ->with(['creator', 'privileges'])
                           ->orderBy('name')
                           ->get();

        return view('admin.roles.index', compact('roles'));
    }

    /**
     * Show form to create new role
     */
    public function create()
    {
        $user = Auth::user();

        if (!$user->isAdmin() && !$user->isMasterAdmin()) {
            abort(403, 'Access denied. Admin privileges required.');
        }

        return view('admin.roles.create');
    }

    /**
     * Store new role
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        if (!$user->isAdmin() && !$user->isMasterAdmin()) {
            abort(403, 'Access denied. Admin privileges required.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'color' => 'nullable|string|max:7',
            'icon' => 'nullable|string|max:10',
        ]);

        // Check if role name already exists in this company
        $existingRole = CompanyRole::where('company_id', $user->company_id)
                                  ->where('name', $validated['name'])
                                  ->first();

        if ($existingRole) {
            return redirect()->back()
                           ->withErrors(['name' => 'A role with this name already exists in your company.'])
                           ->withInput();
        }

        // Create the role
        $role = CompanyRole::create([
            'company_id' => $user->company_id,
            'name' => $validated['name'],
            'description' => $validated['description'] ?? '',
            'color' => $validated['color'] ?? '#007bff',
            'icon' => $validated['icon'] ?? '👤',
            'created_by' => $user->id,
        ]);

        // Create default privileges for the role
        RolePrivilege::create(array_merge(
            ['company_role_id' => $role->id],
            CompanyRole::getDefaultPrivileges()
        ));

        return redirect()->route('admin.roles.index')
                        ->with('success', 'Role "' . $role->name . '" created successfully!');
    }

    /**
     * Show role privileges configuration
     */
    public function privileges(CompanyRole $role)
    {
        $user = Auth::user();

        if (!$user->isAdmin() && !$user->isMasterAdmin()) {
            abort(403, 'Access denied. Admin privileges required.');
        }

        if ($role->company_id !== $user->company_id) {
            abort(403, 'You can only manage roles from your own company.');
        }

        // Get or create privileges
        $privileges = $role->privileges ?? new RolePrivilege(CompanyRole::getDefaultPrivileges());

        return view('admin.roles.privileges', compact('role', 'privileges'));
    }

    /**
     * Update role privileges
     */
    public function updatePrivileges(Request $request, CompanyRole $role)
    {
        $user = Auth::user();

        if (!$user->isAdmin() && !$user->isMasterAdmin()) {
            abort(403, 'Access denied. Admin privileges required.');
        }

        if ($role->company_id !== $user->company_id) {
            abort(403, 'You can only manage roles from your own company.');
        }

        // Get all privilege fields
        $privilegeFields = array_keys(CompanyRole::getDefaultPrivileges());

        $validated = [];
        foreach ($privilegeFields as $field) {
            $validated[$field] = $request->has($field) ? true : false;
        }

        // Update or create privileges
        RolePrivilege::updateOrCreate(
            ['company_role_id' => $role->id],
            $validated
        );

        // Update sidebar visible sections if provided
        if ($request->has('visible_sections')) {
            $sections = $request->input('visible_sections');
            if (!is_array($sections)) {
                $sections = [];
            }
            // Sanitize and de-duplicate
            $sections = array_values(array_unique(array_filter($sections, function ($v) {
                return is_string($v) && $v !== '';
            })));
            $role->visible_sections = $sections;
            $role->save();
        }

        return redirect()->route('admin.roles.index')
                        ->with('success', 'Privileges updated successfully for role "' . $role->name . '"!');
    }

    /**
     * Delete role
     */
    public function destroy(CompanyRole $role)
    {
        $user = Auth::user();

        if (!$user->isAdmin() && !$user->isMasterAdmin()) {
            abort(403, 'Access denied. Admin privileges required.');
        }

        if ($role->company_id !== $user->company_id) {
            abort(403, 'You can only manage roles from your own company.');
        }

        // Check if role is being used by any users
        if ($role->users()->count() > 0) {
            return redirect()->back()
                           ->with('error', 'Cannot delete role "' . $role->name . '" because it is assigned to ' . $role->users()->count() . ' user(s).');
        }

        $roleName = $role->name;
        $role->delete();

        return redirect()->route('admin.roles.index')
                        ->with('success', 'Role "' . $roleName . '" deleted successfully!');
    }
}
