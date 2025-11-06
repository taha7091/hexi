<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CategoryController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if ($user->isMasterAdmin()) {
            $categories = Category::with(['brand', 'divisions', 'products'])->paginate(15);
            $brands = Brand::all();
        } else {
            // Company users can only see categories from their company's brands
            $categories = Category::with(['brand', 'divisions', 'products'])
                ->whereHas('brand', function($q) use ($user) {
                    $q->where('company_id', $user->company_id);
                })
                ->paginate(15);
            $brands = Brand::where('company_id', $user->company_id)->get();
        }

        return view('admin.categories.index', compact('categories', 'brands'));
    }

    public function create()
    {
        $user = Auth::user();

        if ($user->isMasterAdmin()) {
            $brands = Brand::all();
        } else {
            $brands = Brand::where('company_id', $user->company_id)->get();
        }

        return view('admin.categories.create', compact('brands'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'brand_id' => 'required|exists:brands,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'color' => 'nullable|string|max:7',
            'icon' => 'nullable|string|max:50',
            'is_active' => 'boolean'
        ]);

        $user = Auth::user();

        // Check if user has access to this brand
        if (!$user->isMasterAdmin()) {
            $brand = Brand::find($request->brand_id);
            if (!$brand || $brand->company_id !== $user->company_id) {
                return redirect()->back()->with('error', 'You do not have access to this brand.');
            }
        }

        Category::create($request->all());

        return redirect()->route('admin.categories.index')
            ->with('success', 'Category created successfully.');
    }

    public function show(Category $category)
    {
        $user = Auth::user();

        // Check access
        if (!$user->isMasterAdmin() && $category->brand->company_id !== $user->company_id) {
            abort(403);
        }

        $category->load(['brand', 'divisions.groups.products', 'products']);

        return view('admin.categories.show', compact('category'));
    }

    public function edit(Category $category)
    {
        $user = Auth::user();

        // Check access
        if (!$user->isMasterAdmin() && $category->brand->company_id !== $user->company_id) {
            abort(403);
        }

        if ($user->isMasterAdmin()) {
            $brands = Brand::all();
        } else {
            $brands = Brand::where('company_id', $user->company_id)->get();
        }

        return view('admin.categories.edit', compact('category', 'brands'));
    }

    public function update(Request $request, Category $category)
    {
        $request->validate([
            'brand_id' => 'required|exists:brands,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'color' => 'nullable|string|max:7',
            'icon' => 'nullable|string|max:50',
            'is_active' => 'boolean'
        ]);

        $user = Auth::user();

        // Check access
        if (!$user->isMasterAdmin() && $category->brand->company_id !== $user->company_id) {
            abort(403);
        }

        $category->update($request->all());

        return redirect()->route('admin.categories.index')
            ->with('success', 'Category updated successfully.');
    }

    public function destroy(Category $category)
    {
        $user = Auth::user();

        // Check access
        if (!$user->isMasterAdmin() && $category->brand->company_id !== $user->company_id) {
            abort(403);
        }

        // Check if category has divisions or products
        if ($category->divisions()->count() > 0 || $category->products()->count() > 0) {
            return redirect()->back()
                ->with('error', 'Cannot delete category that has divisions or products.');
        }

        $category->delete();

        return redirect()->route('admin.categories.index')
            ->with('success', 'Category deleted successfully.');
    }
}
