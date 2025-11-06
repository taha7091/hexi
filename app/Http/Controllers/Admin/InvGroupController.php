<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InvGroup;
use App\Models\InvDivision;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InvGroupController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $groups = InvGroup::with('division.category.brand')
            ->when(!$user->isMasterAdmin(), function($q) use ($user){
                $q->whereHas('division.category.brand', function($qq) use ($user){ $qq->where('company_id', $user->company_id); });
            })
            ->paginate(20);
        $divisions = InvDivision::when(!$user->isMasterAdmin(), function($q) use ($user){
            $q->whereHas('category.brand', function($qq) use ($user){ $qq->where('company_id',$user->company_id); });
        })->get();
        return view('admin.inventory.inv-groups.index', compact('groups','divisions'));
    }

    public function create()
    {
        $user = Auth::user();
        $divisions = InvDivision::when(!$user->isMasterAdmin(), function($q) use ($user){
            $q->whereHas('category.brand', function($qq) use ($user){ $qq->where('company_id',$user->company_id); });
        })->get();
        return view('admin.inventory.inv-groups.create', compact('divisions'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'inv_division_id' => 'required|exists:inv_divisions,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'color' => 'nullable|string|max:7',
            'icon' => 'nullable|string|max:50',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer',
        ]);
        InvGroup::create($request->only(['inv_division_id','name','description','color','icon','is_active','sort_order']));
        return redirect()->route('admin.inv-groups.index')->with('success','Inv Group created');
    }

    public function edit(InvGroup $inv_group)
    {
        $group = $inv_group;
        $user = Auth::user();
        if (!$user->isMasterAdmin() && optional($group->division->category->brand)->company_id !== $user->company_id) abort(403);
        $divisions = InvDivision::when(!$user->isMasterAdmin(), function($q) use ($user){
            $q->whereHas('category.brand', function($qq) use ($user){ $qq->where('company_id',$user->company_id); });
        })->get();
        return view('admin.inventory.inv-groups.create', compact('group','divisions'));
    }

    public function update(Request $request, InvGroup $inv_group)
    {
        $request->validate([
            'inv_division_id' => 'required|exists:inv_divisions,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'color' => 'nullable|string|max:7',
            'icon' => 'nullable|string|max:50',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer',
        ]);
        $inv_group->update($request->only(['inv_division_id','name','description','color','icon','is_active','sort_order']));
        return redirect()->route('admin.inv-groups.index')->with('success','Inv Group updated');
    }

    public function destroy(InvGroup $inv_group)
    {
        if ($inv_group->items()->count() > 0) return back()->with('error','Cannot delete group with inventory items');
        $inv_group->delete();
        return redirect()->route('admin.inv-groups.index')->with('success','Inv Group deleted');
    }
}

