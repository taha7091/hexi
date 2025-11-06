<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InvDivision;
use App\Models\InvCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InvDivisionController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $divisions = InvDivision::with('category.brand')
            ->when(!$user->isMasterAdmin(), function($q) use ($user){
                $q->whereHas('category.brand', function($qq) use ($user){ $qq->where('company_id', $user->company_id); });
            })
            ->paginate(20);
        $categories = InvCategory::when(!$user->isMasterAdmin(), function($q) use ($user){
            $q->whereHas('brand', function($qq) use ($user){ $qq->where('company_id',$user->company_id); });
        })->get();
        return view('admin.inventory.inv-divisions.index', compact('divisions','categories'));
    }

    public function create()
    {
        $user = Auth::user();
        $categories = InvCategory::when(!$user->isMasterAdmin(), function($q) use ($user){
            $q->whereHas('brand', function($qq) use ($user){ $qq->where('company_id',$user->company_id); });
        })->get();
        return view('admin.inventory.inv-divisions.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'inv_category_id' => 'required|exists:inv_categories,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'color' => 'nullable|string|max:7',
            'icon' => 'nullable|string|max:50',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer',
        ]);
        InvDivision::create($request->only(['inv_category_id','name','description','color','icon','is_active','sort_order']));
        return redirect()->route('admin.inv-divisions.index')->with('success','Inv Division created');
    }

    public function edit(InvDivision $inv_division)
    {
        $division = $inv_division;
        $user = Auth::user();
        if (!$user->isMasterAdmin() && optional($division->category->brand)->company_id !== $user->company_id) abort(403);
        $categories = InvCategory::when(!$user->isMasterAdmin(), function($q) use ($user){
            $q->whereHas('brand', function($qq) use ($user){ $qq->where('company_id',$user->company_id); });
        })->get();
        return view('admin.inventory.inv-divisions.create', compact('division','categories'));
    }

    public function update(Request $request, InvDivision $inv_division)
    {
        $request->validate([
            'inv_category_id' => 'required|exists:inv_categories,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'color' => 'nullable|string|max:7',
            'icon' => 'nullable|string|max:50',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer',
        ]);
        $inv_division->update($request->only(['inv_category_id','name','description','color','icon','is_active','sort_order']));
        return redirect()->route('admin.inv-divisions.index')->with('success','Inv Division updated');
    }

    public function destroy(InvDivision $inv_division)
    {
        if ($inv_division->groups()->count() > 0) return back()->with('error','Cannot delete division with groups');
        $inv_division->delete();
        return redirect()->route('admin.inv-divisions.index')->with('success','Inv Division deleted');
    }
}

