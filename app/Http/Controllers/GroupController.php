<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Group;
use App\Models\Division;

class GroupController extends Controller
{
    /**
     * Get groups with access control
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // Master admin sees all groups
        if ($user->isMasterAdmin()) {
            $query = Group::with(['division.category.brand.company', 'products']);
        } else {
            // Regular users see only their company's groups
            $query = Group::whereHas('division.category.brand', function($q) use ($user) {
                $q->where('company_id', $user->company_id);
            })->with(['division.category.brand', 'products']);
        }

        // Filter by division if specified
        if ($request->has('division_id')) {
            $query->where('division_id', $request->division_id);
        }

        return response()->json([
            'success' => true,
            'data' => $query->get()
        ]);
    }

    /**
     * Create new group
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'division_id' => 'required|exists:divisions,id',
            'description' => 'nullable|string',
            'is_active' => 'boolean'
        ]);

        $user = Auth::user();

        // Check if user can create groups for this division
        if (!$user->isMasterAdmin()) {
            $division = Division::find($validated['division_id']);
            if ($division->category->brand->company_id !== $user->company_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'You can only create groups for your company divisions.'
                ], 403);
            }
        }

        $validated['is_active'] = $validated['is_active'] ?? true;
        $group = Group::create($validated);

        return response()->json([
            'success' => true,
            'data' => $group->load('division.category.brand'),
            'message' => 'Group created successfully.'
        ], 201);
    }

    /**
     * Show specific group
     */
    public function show(Group $group)
    {
        $user = Auth::user();

        // Check access
        if (!$user->isMasterAdmin() && $group->division->category->brand->company_id !== $user->company_id) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied.'
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => $group->load(['division.category.brand.company', 'products'])
        ]);
    }

    /**
     * Update group
     */
    public function update(Request $request, Group $group)
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'division_id' => 'sometimes|required|exists:divisions,id',
            'description' => 'nullable|string',
            'is_active' => 'boolean'
        ]);

        $user = Auth::user();

        // Check access
        if (!$user->isMasterAdmin() && $group->division->category->brand->company_id !== $user->company_id) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied.'
            ], 403);
        }

        $group->update($validated);

        return response()->json([
            'success' => true,
            'data' => $group->load('division.category.brand'),
            'message' => 'Group updated successfully.'
        ]);
    }

    /**
     * Delete group
     */
    public function destroy(Group $group)
    {
        $user = Auth::user();

        // Check access
        if (!$user->isMasterAdmin() && $group->division->category->brand->company_id !== $user->company_id) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied.'
            ], 403);
        }

        // Check if group has products
        if ($group->products()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete group with existing products.'
            ], 400);
        }

        $group->delete();

        return response()->json([
            'success' => true,
            'message' => 'Group deleted successfully.'
        ]);
    }
}
