<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Screen;
use App\Models\ScreenItem;
use App\Models\ScreenGroup;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ScreenController extends Controller
{
    /**
     * Display a listing of screens
     */
    public function index()
    {
        $user = Auth::user();
        $companyId = $user->isMasterAdmin() ? request('company_id') : $user->company_id;

        $screens = Screen::with(['screenItems.product', 'screenItems.screenGroup'])
            ->when($companyId, function ($query) use ($companyId) {
                return $query->forCompany($companyId);
            })
            ->active()
            ->orderBy('name')
            ->paginate(10);

        return view('admin.screens.index', compact('screens', 'companyId'));
    }

    /**
     * Show the form for creating a new screen
     */
    public function create()
    {
        $user = Auth::user();
        $companyId = $user->isMasterAdmin() ? request('company_id') : $user->company_id;

        return view('admin.screens.create', compact('companyId'));
    }

    /**
     * Store a newly created screen
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        $companyId = $user->isMasterAdmin() ? $request->company_id : $user->company_id;

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'grid_rows' => 'required|integer|min:4|max:20',
            'grid_columns' => 'required|integer|min:4|max:20',
            'background_color' => 'required|string|max:7',
            'is_default' => 'boolean'
        ]);

        $screen = Screen::create([
            'name' => $request->name,
            'description' => $request->description,
            'company_id' => $companyId,
            'grid_rows' => $request->grid_rows,
            'grid_columns' => $request->grid_columns,
            'background_color' => $request->background_color,
            'is_active' => true,
            'is_default' => $request->boolean('is_default', false)
        ]);

        if ($request->boolean('is_default')) {
            $screen->setAsDefault();
        }

        return redirect()->route('admin.screens.show', $screen)
            ->with('success', 'Screen created successfully!');
    }

    /**
     * Display the specified screen
     */
    public function show(Screen $screen)
    {
        // Check if user has access to this screen's company
        if (Auth::user()->isAdmin() && $screen->company_id !== Auth::user()->company_id) {
            abort(403, 'Unauthorized access to this screen.');
        }

        $screen->load(['screenItems.product', 'screenItems.screenGroup']);

        $products = Product::whereHas('group.division.category.brand', function($query) use ($screen) {
                $query->where('company_id', $screen->company_id);
            })
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $screenGroups = ScreenGroup::where('company_id', $screen->company_id)
            ->active()
            ->ordered()
            ->get();

        return view('admin.screens.show', compact('screen', 'products', 'screenGroups'));
    }

    /**
     * Show the form for editing the specified screen
     */
    public function edit(Screen $screen)
    {
        // Check if user has access to this screen's company
        if (Auth::user()->isAdmin() && $screen->company_id !== Auth::user()->company_id) {
            abort(403, 'Unauthorized access to this screen.');
        }

        return view('admin.screens.edit', compact('screen'));
    }

    /**
     * Update the specified screen
     */
    public function update(Request $request, Screen $screen)
    {
        // Check if user has access to this screen's company
        if (Auth::user()->isAdmin() && $screen->company_id !== Auth::user()->company_id) {
            abort(403, 'Unauthorized access to this screen.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'grid_rows' => 'required|integer|min:4|max:20',
            'grid_columns' => 'required|integer|min:4|max:20',
            'background_color' => 'required|string|max:7',
            'is_default' => 'boolean'
        ]);

        $screen->update([
            'name' => $request->name,
            'description' => $request->description,
            'grid_rows' => $request->grid_rows,
            'grid_columns' => $request->grid_columns,
            'background_color' => $request->background_color,
        ]);

        if ($request->boolean('is_default')) {
            $screen->setAsDefault();
        }

        return redirect()->route('admin.screens.show', $screen)
            ->with('success', 'Screen updated successfully!');
    }

    /**
     * Remove the specified screen
     */
    public function destroy(Screen $screen)
    {
        // Check if user has access to this screen's company
        if (Auth::user()->isAdmin() && $screen->company_id !== Auth::user()->company_id) {
            abort(403, 'Unauthorized access to this screen.');
        }

        $screen->update(['is_active' => false]);

        return redirect()->route('admin.screens.index')
            ->with('success', 'Screen deleted successfully!');
    }

    /**
     * Add item to screen
     */
    public function addItem(Request $request, Screen $screen)
    {
        // Check if user has access to this screen's company
        if (Auth::user()->isAdmin() && $screen->company_id !== Auth::user()->company_id) {
            abort(403, 'Unauthorized access to this screen.');
        }

        $request->validate([
            'type' => 'required|in:product,group,modifier,action',
            'product_id' => 'nullable|exists:products,id',
            'screen_group_id' => 'nullable|exists:screen_groups,id',
            'display_name' => 'required|string|max:255',
            'background_color' => 'required|string|max:7',
            'text_color' => 'required|string|max:7',
            'grid_x' => 'required|integer|min:0',
            'grid_y' => 'required|integer|min:0',
            'width' => 'required|integer|min:1|max:4',
            'height' => 'required|integer|min:1|max:4'
        ]);

        // Check if position is available
        $overlapping = ScreenItem::where('screen_id', $screen->id)
            ->where('is_active', true)
            ->get()
            ->filter(function ($item) use ($request) {
                return $item->overlapsWithPosition(
                    $request->grid_x,
                    $request->grid_y,
                    $request->width,
                    $request->height
                );
            });

        if ($overlapping->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Position is already occupied'
            ], 422);
        }

        $screenItem = ScreenItem::create([
            'screen_id' => $screen->id,
            'product_id' => $request->product_id,
            'screen_group_id' => $request->screen_group_id,
            'type' => $request->type,
            'display_name' => $request->display_name,
            'background_color' => $request->background_color,
            'text_color' => $request->text_color,
            'grid_x' => $request->grid_x,
            'grid_y' => $request->grid_y,
            'width' => $request->width,
            'height' => $request->height,
            'is_active' => true
        ]);

        return response()->json([
            'success' => true,
            'item' => $screenItem->load(['product', 'screenGroup'])
        ]);
    }

    /**
     * Update screen item
     */
    public function updateItem(Request $request, Screen $screen, ScreenItem $item)
    {
        // Check if user has access to this screen's company
        if (Auth::user()->isAdmin() && $screen->company_id !== Auth::user()->company_id) {
            abort(403, 'Unauthorized access to this screen.');
        }

        $request->validate([
            'display_name' => 'required|string|max:255',
            'background_color' => 'required|string|max:7',
            'text_color' => 'required|string|max:7',
            'grid_x' => 'required|integer|min:0',
            'grid_y' => 'required|integer|min:0',
            'width' => 'required|integer|min:1|max:4',
            'height' => 'required|integer|min:1|max:4'
        ]);

        // Check if new position is available (excluding current item)
        $overlapping = ScreenItem::where('screen_id', $screen->id)
            ->where('id', '!=', $item->id)
            ->where('is_active', true)
            ->get()
            ->filter(function ($otherItem) use ($request) {
                return $otherItem->overlapsWithPosition(
                    $request->grid_x,
                    $request->grid_y,
                    $request->width,
                    $request->height
                );
            });

        if ($overlapping->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Position is already occupied'
            ], 422);
        }

        $item->update([
            'display_name' => $request->display_name,
            'background_color' => $request->background_color,
            'text_color' => $request->text_color,
            'grid_x' => $request->grid_x,
            'grid_y' => $request->grid_y,
            'width' => $request->width,
            'height' => $request->height
        ]);

        return response()->json([
            'success' => true,
            'item' => $item->load(['product', 'screenGroup'])
        ]);
    }

    /**
     * Remove item from screen
     */
    public function removeItem(Screen $screen, ScreenItem $item)
    {
        // Check if user has access to this screen's company
        if (Auth::user()->isAdmin() && $screen->company_id !== Auth::user()->company_id) {
            abort(403, 'Unauthorized access to this screen.');
        }

        $item->update(['is_active' => false]);

        return response()->json(['success' => true]);
    }

    /**
     * Clear all items from screen
     */
    public function clearScreen(Screen $screen)
    {
        // Check if user has access to this screen's company
        if (Auth::user()->isAdmin() && $screen->company_id !== Auth::user()->company_id) {
            abort(403, 'Unauthorized access to this screen.');
        }

        ScreenItem::where('screen_id', $screen->id)->update(['is_active' => false]);

        return response()->json(['success' => true]);
    }

    /**
     * Duplicate screen
     */
    public function duplicate(Screen $screen)
    {
        // Check if user has access to this screen's company
        if (Auth::user()->isAdmin() && $screen->company_id !== Auth::user()->company_id) {
            abort(403, 'Unauthorized access to this screen.');
        }

        DB::transaction(function () use ($screen) {
            $newScreen = $screen->replicate();
            $newScreen->name = $screen->name . ' (Copy)';
            $newScreen->is_default = false;
            $newScreen->save();

            foreach ($screen->screenItems as $item) {
                $newItem = $item->replicate();
                $newItem->screen_id = $newScreen->id;
                $newItem->save();
            }

            return $newScreen;
        });

        return redirect()->route('admin.screens.index')
            ->with('success', 'Screen duplicated successfully!');
    }
    public function designer(Screen $screen)
{
    $products = Product::all(); // or filter by company_id if needed
    return view('admin.screens.designer', compact('screen', 'products'));
}

public function addItem1(Request $request, Screen $screen)
{
    $validated = $request->validate([
        'product_id' => 'required|exists:products,id',
        'x' => 'required|integer',
        'y' => 'required|integer',
    ]);

    ScreenItem::updateOrCreate(
        [
            'screen_id' => $screen->id,
            'grid_x' => $validated['x'],
            'grid_y' => $validated['y'],
        ],
        [
            'product_id' => $validated['product_id'],
            'display_name' => Product::find($validated['product_id'])->name,
        ]
    );

    return response()->json(['success' => true]);
}

public function clear(Screen $screen)
{
    $screen->screenItems()->delete();
    return response()->json(['success' => true]);
}




    /**
     * JSON: Screen groups for current company (web guard)
     */
    public function groupsJson()
    {
        $user = Auth::user();
        $companyId = $user->company_id;

        $screenGroups = ScreenGroup::forCompany($companyId)
            ->active()
            ->ordered()
            ->get();

        return response()->json([
            'success' => true,
            'screen_groups' => $screenGroups->map(function ($group) {
                return [
                    'id' => $group->id,
                    'name' => $group->name,
                    'color' => $group->color,
                    'description' => $group->description,
                    'sort_order' => $group->sort_order,
                ];
            })
        ]);
    }

    /**
     * JSON: Active products by group for current company (web guard)
     */
    public function itemsByGroupJson($groupId)
    {
        $user = Auth::user();
        $companyId = $user->company_id;

        $products = Product::where('is_active', true)
            ->where('group_id', $groupId)
            ->whereHas('group.division.category.brand', function ($q) use ($companyId) {
                $q->where('company_id', $companyId);
            })
            ->orderBy('name')
            ->select('id', 'name', 'price', 'barcode')
            ->get();

        return response()->json([
            'success' => true,
            'items' => $products->map(function ($p) {
                return [
                    'id' => $p->id,
                    'name' => $p->name,
                    'price' => $p->price,
                    'barcode' => $p->barcode,
                ];
            })
        ]);
    }

    /**
     * Web sync: touch screen to trigger POS sync (web guard)
     */
    public function sync(Screen $screen)
    {
        $user = Auth::user();
        if (!$user->isMasterAdmin() && $screen->company_id !== $user->company_id) {
            abort(403, 'Unauthorized access to this screen.');
        }

        $screen->touch();

        return response()->json([
            'success' => true,
            'message' => 'Screen sync triggered'
        ]);
    }

}
