<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ScreenGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ScreenGroupController extends Controller
{
    /**
     * Display a listing of screen groups
     */
    public function index()
    {
        $user = Auth::user();
        $companyId = $user->isMasterAdmin() ? request('company_id') : $user->company_id;

        $screenGroups = ScreenGroup::when($companyId, function ($query) use ($companyId) {
                return $query->forCompany($companyId);
            })
            ->active()
            ->ordered()
            ->paginate(10);

        return view('admin.screen-groups.index', compact('screenGroups', 'companyId'));
    }

    /**
     * Show the form for creating a new screen group
     */
    public function create()
    {
        $user = Auth::user();
        $companyId = $user->isMasterAdmin() ? request('company_id') : $user->company_id;

        return view('admin.screen-groups.create', compact('companyId'));
    }

    /**
     * Store a newly created screen group
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        $companyId = $user->isMasterAdmin() ? $request->company_id : $user->company_id;

        $request->validate([
            'name' => 'required|string|max:255',
            'color' => 'required|string|max:7',
            'description' => 'nullable|string',
            'sort_order' => 'nullable|integer|min:0'
        ]);

        ScreenGroup::create([
            'name' => $request->name,
            'color' => $request->color,
            'description' => $request->description,
            'company_id' => $companyId,
            'sort_order' => $request->sort_order ?? 0,
            'is_active' => true
        ]);

        return redirect()->route('admin.screen-groups.index')
            ->with('success', 'Screen group created successfully!');
    }

    /**
     * Display the specified screen group
     */
    public function show(ScreenGroup $screenGroup)
    {
        // Check if user has access to this screen group's company
        if (Auth::user()->isAdmin() && $screenGroup->company_id !== Auth::user()->company_id) {
            abort(403, 'Unauthorized access to this screen group.');
        }

        $screenGroup->load('screenItems.screen');

        return view('admin.screen-groups.show', compact('screenGroup'));
    }

    /**
     * Show the form for editing the specified screen group
     */
    public function edit(ScreenGroup $screenGroup)
    {
        // Check if user has access to this screen group's company
        if (Auth::user()->isAdmin() && $screenGroup->company_id !== Auth::user()->company_id) {
            abort(403, 'Unauthorized access to this screen group.');
        }

        return view('admin.screen-groups.edit', compact('screenGroup'));
    }

    /**
     * Update the specified screen group
     */
    public function update(Request $request, ScreenGroup $screenGroup)
    {
        // Check if user has access to this screen group's company
        if (Auth::user()->isAdmin() && $screenGroup->company_id !== Auth::user()->company_id) {
            abort(403, 'Unauthorized access to this screen group.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'color' => 'required|string|max:7',
            'description' => 'nullable|string',
            'sort_order' => 'nullable|integer|min:0'
        ]);

        $screenGroup->update([
            'name' => $request->name,
            'color' => $request->color,
            'description' => $request->description,
            'sort_order' => $request->sort_order ?? 0
        ]);

        return redirect()->route('admin.screen-groups.show', $screenGroup)
            ->with('success', 'Screen group updated successfully!');
    }

    /**
     * Remove the specified screen group
     */
    public function destroy(ScreenGroup $screenGroup)
    {
        // Check if user has access to this screen group's company
        if (Auth::user()->isAdmin() && $screenGroup->company_id !== Auth::user()->company_id) {
            abort(403, 'Unauthorized access to this screen group.');
        }

        $screenGroup->update(['is_active' => false]);

        return redirect()->route('admin.screen-groups.index')
            ->with('success', 'Screen group deleted successfully!');
    }
}
