<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PosLayout;
use App\Models\Group;
use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PosLayoutController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if ($user->isMasterAdmin()) {
            $layouts = PosLayout::with(['brand'])->paginate(15);
            $brands = Brand::all();
        } else {
            $layouts = PosLayout::with(['brand'])
                ->whereHas('brand', function($q) use ($user) {
                    $q->where('company_id', $user->company_id);
                })
                ->paginate(15);
            $brands = Brand::where('company_id', $user->company_id)->get();
        }

        return view('admin.pos-layouts.index', compact('layouts', 'brands'));
    }

    public function create()
    {
        $user = Auth::user();

        if ($user->isMasterAdmin()) {
            $brands = Brand::all();
        } else {
            $brands = Brand::where('company_id', $user->company_id)->get();
        }

        return view('admin.pos-layouts.create', compact('brands'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'brand_id' => 'required|exists:brands,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_default' => 'boolean'
        ]);

        $user = Auth::user();

        // Check access
        if (!$user->isMasterAdmin()) {
            $brand = Brand::find($request->brand_id);
            if (!$brand || $brand->company_id !== $user->company_id) {
                return redirect()->back()->with('error', 'You do not have access to this brand.');
            }
        }

        // If this is set as default, unset other defaults for this brand
        if ($request->is_default) {
            PosLayout::where('brand_id', $request->brand_id)
                ->update(['is_default' => false]);
        }

        // Create layout with default empty layout data
        $layout = PosLayout::create([
            'brand_id' => $request->brand_id,
            'name' => $request->name,
            'description' => $request->description,
            'is_default' => $request->boolean('is_default'),
            'layout_data' => [
                'groups' => [],
                'screen_width' => 1024,
                'screen_height' => 768,
                'grid_size' => 20
            ]
        ]);

        return redirect()->route('admin.pos-layouts.show', $layout)
            ->with('success', 'POS Layout created successfully. You can now arrange your groups.');
    }

    public function show(PosLayout $posLayout)
    {
        $user = Auth::user();

        // Check access
        if (!$user->isMasterAdmin() && $posLayout->brand->company_id !== $user->company_id) {
            abort(403);
        }

        $posLayout->load(['brand']);

        // Get all groups for this brand
        $groups = Group::whereHas('division.category', function($query) use ($posLayout) {
            $query->where('brand_id', $posLayout->brand_id);
        })->with(['division.category', 'products'])->get();

        return view('admin.pos-layouts.show', compact('posLayout', 'groups'));
    }

    public function edit(PosLayout $posLayout)
    {
        $user = Auth::user();

        // Check access
        if (!$user->isMasterAdmin() && $posLayout->brand->company_id !== $user->company_id) {
            abort(403);
        }

        if ($user->isMasterAdmin()) {
            $brands = Brand::all();
        } else {
            $brands = Brand::where('company_id', $user->company_id)->get();
        }

        return view('admin.pos-layouts.edit', compact('posLayout', 'brands'));
    }

    public function update(Request $request, PosLayout $posLayout)
    {
        $request->validate([
            'brand_id' => 'required|exists:brands,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_default' => 'boolean'
        ]);

        $user = Auth::user();

        // Check access
        if (!$user->isMasterAdmin() && $posLayout->brand->company_id !== $user->company_id) {
            abort(403);
        }

        // If this is set as default, unset other defaults for this brand
        if ($request->is_default) {
            PosLayout::where('brand_id', $request->brand_id)
                ->where('id', '!=', $posLayout->id)
                ->update(['is_default' => false]);
        }

        $posLayout->update($request->all());

        return redirect()->route('admin.pos-layouts.index')
            ->with('success', 'POS Layout updated successfully.');
    }

    public function destroy(PosLayout $posLayout)
    {
        $user = Auth::user();

        // Check access
        if (!$user->isMasterAdmin() && $posLayout->brand->company_id !== $user->company_id) {
            abort(403);
        }

        // Don't allow deletion of default layout
        if ($posLayout->is_default) {
            return redirect()->back()
                ->with('error', 'Cannot delete the default layout. Set another layout as default first.');
        }

        $posLayout->delete();

        return redirect()->route('admin.pos-layouts.index')
            ->with('success', 'POS Layout deleted successfully.');
    }

    public function updatePositions(Request $request, PosLayout $posLayout)
    {
        $request->validate([
            'groups' => 'required|array',
            'groups.*.id' => 'required|exists:groups,id',
            'groups.*.x' => 'required|numeric',
            'groups.*.y' => 'required|numeric',
            'groups.*.width' => 'required|numeric',
            'groups.*.height' => 'required|numeric'
        ]);

        $user = Auth::user();

        // Check access
        if (!$user->isMasterAdmin() && $posLayout->brand->company_id !== $user->company_id) {
            abort(403);
        }

        foreach ($request->groups as $groupData) {
            Group::where('id', $groupData['id'])
                ->update([
                    'pos_x' => $groupData['x'],
                    'pos_y' => $groupData['y'],
                    'width' => $groupData['width'],
                    'height' => $groupData['height']
                ]);
        }

        return response()->json(['success' => true]);
    }

    public function getGroups(PosLayout $posLayout)
    {
        $user = Auth::user();

        // Check access
        if (!$user->isMasterAdmin() && $posLayout->brand->company_id !== $user->company_id) {
            abort(403);
        }

        $groups = Group::whereHas('division.category', function($query) use ($posLayout) {
            $query->where('brand_id', $posLayout->brand_id);
        })->with(['division.category', 'products'])->get();

        return response()->json($groups);
    }
}
