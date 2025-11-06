<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Division;
use App\Models\Category;

class DivisionController extends Controller
{
    /**
     * Get divisions with access control
     */
    public function index(Request $request)
    {
       
    $user = Auth::user();

    if ($user->isMasterAdmin()) {
        $divisions = Division::with(['category.brand.company', 'groups'])->get();
        $categories = Category::with('brand.company')->get();
    } else {
        $divisions = Division::whereHas('category.brand', function($q) use ($user) {
            $q->where('company_id', $user->company_id);
        })->with(['category.brand', 'groups'])->get();

        $categories = Category::whereHas('brand', function($q) use ($user) {
            $q->where('company_id', $user->company_id);
        })->with('brand')->get();
    }

    return view('admin.divisions.index', compact('divisions', 'categories'));

        // Filter by category if specified
        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        return response()->json([
            'success' => true,
            'data' => $query->get()
        ]);
    }

    /**
     * Create new division
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'description' => 'nullable|string',
            'is_active' => 'boolean'
        ]);

        $user = Auth::user();

        // Check if user can create divisions for this category
        if (!$user->isMasterAdmin()) {
            $category = Category::find($validated['category_id']);
            if ($category->brand->company_id !== $user->company_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'You can only create divisions for your company categories.'
                ], 403);
            }
        }

        $validated['is_active'] = $validated['is_active'] ?? true;
        $division = Division::create($validated);

        return response()->json([
            'success' => true,
            'data' => $division->load('category.brand'),
            'message' => 'Division created successfully.'
        ], 201);
    }

    /**
     * Show specific division
     */
    public function show(Division $division)
    {
        $user = Auth::user();

        // Check access
        if (!$user->isMasterAdmin() && $division->category->brand->company_id !== $user->company_id) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied.'
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => $division->load(['category.brand.company', 'groups.products'])
        ]);
    }

    /**
     * Update division
     */
    public function update(Request $request, Division $division)
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'category_id' => 'sometimes|required|exists:categories,id',
            'description' => 'nullable|string',
            'is_active' => 'boolean'
        ]);

        $user = Auth::user();

        // Check access
        if (!$user->isMasterAdmin() && $division->category->brand->company_id !== $user->company_id) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied.'
            ], 403);
        }

        $division->update($validated);

        return response()->json([
            'success' => true,
            'data' => $division->load('category.brand'),
            'message' => 'Division updated successfully.'
        ]);
    }

    /**
     * Delete division
     */
    public function destroy(Division $division)
    {
        $user = Auth::user();

        // Check access
        if (!$user->isMasterAdmin() && $division->category->brand->company_id !== $user->company_id) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied.'
            ], 403);
        }

        // Check if division has groups
        if ($division->groups()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete division with existing groups.'
            ], 400);
        }

        $division->delete();

        return response()->json([
            'success' => true,
            'message' => 'Division deleted successfully.'
        ]);
    }
    public function create()
{
    $user = Auth::user();

    if ($user->isMasterAdmin()) {
        $categories = Category::with('brand.company')->get();
    } else {
        $categories = Category::whereHas('brand', function($q) use ($user) {
            $q->where('company_id', $user->company_id);
        })->with('brand')->get();
    }

    return view('admin.divisions.create', compact('categories'));
}
}
