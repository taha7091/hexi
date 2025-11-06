<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Group;
use App\Models\Division;
use Illuminate\Support\Facades\Auth;

class GroupController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();

        $query = Group::with(['division.category.brand.company', 'products']);

        // Filter by user access
        if (!$user->isMasterAdmin()) {
            $query->whereHas('division.category.brand', function($q) use ($user) {
                $q->where('company_id', $user->company_id);
            });
        }

        $groups = $query->orderBy('sort_order')->orderBy('name')->paginate(20);

        // Get divisions for filter
        $divisionsQuery = Division::with(['category.brand']);
        if (!$user->isMasterAdmin()) {
            $divisionsQuery->whereHas('category.brand', function($q) use ($user) {
                $q->where('company_id', $user->company_id);
            });
        }
        $divisions = $divisionsQuery->orderBy('name')->get();

        return view('admin.groups.index', compact('groups', 'divisions'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $user = Auth::user();

        $query = Division::with(['category.brand.company']);

        // Filter divisions by user access
        if (!$user->isMasterAdmin()) {
            $query->whereHas('category.brand', function($q) use ($user) {
                $q->where('company_id', $user->company_id);
            });
        }

        $divisions = $query->orderBy('name')->get();

        return view('admin.groups.create', compact('divisions'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'division_id' => 'required|exists:divisions,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'color' => 'nullable|string|max:7',
            'icon' => 'nullable|string|max:255',
            'sort_order' => 'nullable|integer|min:0',
            'pos_x' => 'nullable|integer|min:0',
            'pos_y' => 'nullable|integer|min:0',
            'width' => 'nullable|integer|min:50',
            'height' => 'nullable|integer|min:50',
            'is_active' => 'boolean'
        ]);

        $user = Auth::user();

        // Check if user has access to the division
        $division = Division::findOrFail($validated['division_id']);
        if (!$user->isMasterAdmin() && $division->category->brand->company_id !== $user->company_id) {
            abort(403, 'Access denied.');
        }

        $validated['is_active'] = $validated['is_active'] ?? true;
        $group = Group::create($validated);

        return redirect()->route('admin.groups.index')
            ->with('success', 'Group created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Group $group)
    {
        $user = Auth::user();

        // Check access
        if (!$user->isMasterAdmin() && $group->division->category->brand->company_id !== $user->company_id) {
            abort(403, 'Access denied.');
        }

        $group->load(['division.category.brand.company', 'products']);

        return view('admin.groups.show', compact('group'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Group $group)
    {
        $user = Auth::user();

        // Check access
        if (!$user->isMasterAdmin() && $group->division->category->brand->company_id !== $user->company_id) {
            abort(403, 'Access denied.');
        }

        $query = Division::with(['category.brand.company']);

        // Filter divisions by user access
        if (!$user->isMasterAdmin()) {
            $query->whereHas('category.brand', function($q) use ($user) {
                $q->where('company_id', $user->company_id);
            });
        }

        $divisions = $query->orderBy('name')->get();

        return view('admin.groups.edit', compact('group', 'divisions'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Group $group)
    {
        $validated = $request->validate([
            'division_id' => 'required|exists:divisions,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'color' => 'nullable|string|max:7',
            'icon' => 'nullable|string|max:255',
            'sort_order' => 'nullable|integer|min:0',
            'pos_x' => 'nullable|integer|min:0',
            'pos_y' => 'nullable|integer|min:0',
            'width' => 'nullable|integer|min:50',
            'height' => 'nullable|integer|min:50',
            'is_active' => 'boolean'
        ]);

        $user = Auth::user();

        // Check access to current group
        if (!$user->isMasterAdmin() && $group->division->category->brand->company_id !== $user->company_id) {
            abort(403, 'Access denied.');
        }

        // Check access to new division
        $division = Division::findOrFail($validated['division_id']);
        if (!$user->isMasterAdmin() && $division->category->brand->company_id !== $user->company_id) {
            abort(403, 'Access denied.');
        }

        $validated['is_active'] = $validated['is_active'] ?? false;
        $group->update($validated);

        return redirect()->route('admin.groups.index')
            ->with('success', 'Group updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Group $group)
    {
        $user = Auth::user();

        // Check access
        if (!$user->isMasterAdmin() && $group->division->category->brand->company_id !== $user->company_id) {
            abort(403, 'Access denied.');
        }

        // Check if group has products
        if ($group->products()->count() > 0) {
            return redirect()->route('admin.groups.index')
                ->with('error', 'Cannot delete group that has products.');
        }

        $group->delete();

        return redirect()->route('admin.groups.index')
            ->with('success', 'Group deleted successfully.');
    }
}
