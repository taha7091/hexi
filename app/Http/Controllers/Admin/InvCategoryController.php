<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InvCategory;
use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InvCategoryController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        if ($user->isMasterAdmin()) {
            $categories = InvCategory::with('brand')->paginate(20);
            $brands = Brand::all();
        } else {
            $categories = InvCategory::with('brand')
                ->whereHas('brand', function($q) use ($user){ $q->where('company_id', $user->company_id); })
                ->paginate(20);
            $brands = Brand::where('company_id', $user->company_id)->get();
        }
        return view('admin.inventory.inv-categories.index', compact('categories','brands'));
    }

    public function create()
    {
        $user = Auth::user();
        $brands = $user->isMasterAdmin() ? Brand::all() : Brand::where('company_id', $user->company_id)->get();
        return view('admin.inventory.inv-categories.create', compact('brands'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'brand_id' => 'required|exists:brands,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'color' => 'nullable|string|max:7',
            'icon' => 'nullable|string|max:50',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer',
        ]);
        $user = Auth::user();
        if (!$user->isMasterAdmin()) {
            $brand = Brand::findOrFail($request->brand_id);
            if ($brand->company_id !== $user->company_id) abort(403);
        }
        InvCategory::create($request->only(['brand_id','name','description','color','icon','is_active','sort_order']));
        return redirect()->route('admin.inv-categories.index')->with('success','Inv Category created');
    }

    public function edit(InvCategory $inv_category)
    {
        $user = Auth::user();
        if (!$user->isMasterAdmin() && $inv_category->brand->company_id !== $user->company_id) abort(403);
        $brands = $user->isMasterAdmin() ? Brand::all() : Brand::where('company_id', $user->company_id)->get();
        $category = $inv_category;
        return view('admin.inventory.inv-categories.create', compact('category','brands'));
    }

    public function update(Request $request, InvCategory $inv_category)
    {
        $request->validate([
            'brand_id' => 'required|exists:brands,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'color' => 'nullable|string|max:7',
            'icon' => 'nullable|string|max:50',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer',
        ]);
        $user = Auth::user();
        if (!$user->isMasterAdmin() && $inv_category->brand->company_id !== $user->company_id) abort(403);
        $inv_category->update($request->only(['brand_id','name','description','color','icon','is_active','sort_order']));
        return redirect()->route('admin.inv-categories.index')->with('success','Inv Category updated');
    }

    public function destroy(InvCategory $inv_category)
    {
        $user = Auth::user();
        if (!$user->isMasterAdmin() && $inv_category->brand->company_id !== $user->company_id) abort(403);
        if ($inv_category->divisions()->count() > 0) {
            return back()->with('error','Cannot delete category with divisions');
        }
        $inv_category->delete();
        return redirect()->route('admin.inv-categories.index')->with('success','Inv Category deleted');
    }
}

