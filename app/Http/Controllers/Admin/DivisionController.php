<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Division;
use App\Models\Category;
use Illuminate\Support\Facades\Auth;

class DivisionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();

        $query = Division::with(['category.brand.company']);

        // Filter by user access
        if (!$user->isMasterAdmin()) {
            $query->whereHas('category.brand', function($q) use ($user) {
                $q->where('company_id', $user->company_id);
            });
        }

        $divisions = $query->orderBy('sort_order')->orderBy('name')->paginate(20);

        // Get categories for filter dropdown
        $categoriesQuery = Category::with(['brand']);
        if (!$user->isMasterAdmin()) {
            $categoriesQuery->whereHas('brand', function($q) use ($user) {
                $q->where('company_id', $user->company_id);
            });
        }
        $categories = $categoriesQuery->orderBy('name')->get();

        return view('admin.divisions.index', compact('divisions', 'categories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $user = Auth::user();

        $query = Category::with(['brand.company']);

        // Filter categories by user access
        if (!$user->isMasterAdmin()) {
            $query->whereHas('brand', function($q) use ($user) {
                $q->where('company_id', $user->company_id);
            });
        }

        $categories = $query->orderBy('name')->get();

        return view('admin.divisions.create', compact('categories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'color' => 'nullable|string|max:7',
            'icon' => 'nullable|string|max:255',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean'
        ]);

        $user = Auth::user();

        // Check if user has access to the category
        $category = Category::findOrFail($validated['category_id']);
        if (!$user->isMasterAdmin() && $category->brand->company_id !== $user->company_id) {
            abort(403, 'Access denied.');
        }

        $validated['is_active'] = $validated['is_active'] ?? true;
        $division = Division::create($validated);

        return redirect()->route('admin.divisions.index')
            ->with('success', 'Division created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Division $division)
    {
        $user = Auth::user();

        // Check access
        if (!$user->isMasterAdmin() && $division->category->brand->company_id !== $user->company_id) {
            abort(403, 'Access denied.');
        }

        $division->load(['category.brand.company', 'groups.products']);

        return view('admin.divisions.show', compact('division'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Division $division)
    {
        $user = Auth::user();

        // Check access
        if (!$user->isMasterAdmin() && $division->category->brand->company_id !== $user->company_id) {
            abort(403, 'Access denied.');
        }

        $query = Category::with(['brand.company']);

        // Filter categories by user access
        if (!$user->isMasterAdmin()) {
            $query->whereHas('brand', function($q) use ($user) {
                $q->where('company_id', $user->company_id);
            });
        }

        $categories = $query->orderBy('name')->get();

        return view('admin.divisions.edit', compact('division', 'categories'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Division $division)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'color' => 'nullable|string|max:7',
            'icon' => 'nullable|string|max:255',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean'
        ]);

        $user = Auth::user();

        // Check access to current division
        if (!$user->isMasterAdmin() && $division->category->brand->company_id !== $user->company_id) {
            abort(403, 'Access denied.');
        }

        // Check access to new category
        $category = Category::findOrFail($validated['category_id']);
        if (!$user->isMasterAdmin() && $category->brand->company_id !== $user->company_id) {
            abort(403, 'Access denied.');
        }

        $validated['is_active'] = $validated['is_active'] ?? false;
        $division->update($validated);

        return redirect()->route('admin.divisions.index')
            ->with('success', 'Division updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Division $division)
    {
        $user = Auth::user();

        // Check access
        if (!$user->isMasterAdmin() && $division->category->brand->company_id !== $user->company_id) {
            abort(403, 'Access denied.');
        }

        // Check if division has groups
        if ($division->groups()->count() > 0) {
            return redirect()->route('admin.divisions.index')
                ->with('error', 'Cannot delete division that has groups.');
        }

        $division->delete();

        return redirect()->route('admin.divisions.index')
            ->with('success', 'Division deleted successfully.');
    }
}
