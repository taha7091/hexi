<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CategoryController extends Controller
{
    /**
     * Display a listing of categories.
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        if ($user->isMasterAdmin()) {
            $query = Category::with(['brand.company', 'divisions']);
            $brands = Brand::with('company')->get();
        } else {
            $query = Category::whereHas('brand', function ($q) use ($user) {
                $q->where('company_id', $user->company_id);
            })->with(['brand', 'divisions']);

            $brands = Brand::where('company_id', $user->company_id)->get();
        }

        // Apply brand filter if provided
        if ($request->has('brand_id')) {
            $query->where('brand_id', $request->brand_id);
        }

        $categories = $query->get();

        // Return JSON for API or AJAX
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'data' => $categories
            ]);
        }

        // Return web view
        return view('admin.categories.index', compact('categories', 'brands'));
    }

    /**
     * Store a new category.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'brand_id'    => 'required|exists:brands,id',
            'description' => 'nullable|string',
            'is_active'   => 'boolean'
        ]);

        $user = Auth::user();

        $brand = Brand::findOrFail($validated['brand_id']);

        if (!$user->isMasterAdmin() && $brand->company_id !== $user->company_id) {
            return response()->json([
                'success' => false,
                'message' => 'You can only create categories for your company\'s brands.'
            ], 403);
        }

        $validated['is_active'] = $validated['is_active'] ?? true;

        $category = Category::create($validated);

        return response()->json([
            'success' => true,
            'data'    => $category->load('brand'),
            'message' => 'Category created successfully.'
        ], 201);
    }

    /**
     * Display the specified category.
     */
    public function show(Category $category)
    {
        $user = Auth::user();

        if (!$user->isMasterAdmin() && $category->brand->company_id !== $user->company_id) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied.'
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data'    => $category->load(['brand.company', 'divisions.groups.products'])
        ]);
    }

    /**
     * Update the specified category.
     */
    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name'        => 'sometimes|required|string|max:255',
            'brand_id'    => 'sometimes|required|exists:brands,id',
            'description' => 'nullable|string',
            'is_active'   => 'boolean'
        ]);

        $user = Auth::user();

        if (!$user->isMasterAdmin() && $category->brand->company_id !== $user->company_id) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied.'
            ], 403);
        }

        // If brand_id is being updated, validate ownership
        if (isset($validated['brand_id'])) {
            $brand = Brand::findOrFail($validated['brand_id']);
            if (!$user->isMasterAdmin() && $brand->company_id !== $user->company_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'You can only assign your company\'s brand.'
                ], 403);
            }
        }

        $category->update($validated);

        return response()->json([
            'success' => true,
            'data'    => $category->load('brand'),
            'message' => 'Category updated successfully.'
        ]);
    }

    /**
     * Remove the specified category.
     */
    public function destroy(Category $category)
    {
        $user = Auth::user();

        if (!$user->isMasterAdmin() && $category->brand->company_id !== $user->company_id) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied.'
            ], 403);
        }

        if ($category->divisions()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete category with existing divisions.'
            ], 400);
        }

        $category->delete();

        return response()->json([
            'success' => true,
            'message' => 'Category deleted successfully.'
        ]);
    }
}
