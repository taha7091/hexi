<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Screen;
use App\Models\ScreenGroup;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ScreenSyncController extends Controller
{
    /**
     * Get all screens for a company
     */
    public function getScreens(Request $request)
    {
        $user = Auth::user();
        $companyId = $user->company_id;

        $screens = Screen::with([
                'screenItems' => function ($query) {
                    $query->where('is_active', true);
                },
                'screenItems.product:id,name,price,barcode,category_id',
                'screenItems.screenGroup:id,name,color'
            ])
            ->forCompany($companyId)
            ->active()
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'screens' => $screens->map(function ($screen) {
                return [
                    'id' => $screen->id,
                    'name' => $screen->name,
                    'description' => $screen->description,
                    'grid_rows' => $screen->grid_rows,
                    'grid_columns' => $screen->grid_columns,
                    'background_color' => $screen->background_color,
                    'is_default' => $screen->is_default,
                    'layout_grid' => $screen->getLayoutGrid(),
                    'items' => $screen->screenItems->map(function ($item) {
                        return [
                            'id' => $item->id,
                            'type' => $item->type,
                            'display_name' => $item->display_name,
                            'background_color' => $item->getBackgroundColor(),
                            'text_color' => $item->text_color,
                            'grid_x' => $item->grid_x,
                            'grid_y' => $item->grid_y,
                            'width' => $item->width,
                            'height' => $item->height,
                            'product' => $item->product ? [
                                'id' => $item->product->id,
                                'name' => $item->product->name,
                                'price' => $item->product->price,
                                'barcode' => $item->product->barcode,
                                'category_id' => $item->product->category_id
                            ] : null,
                            'screen_group' => $item->screenGroup ? [
                                'id' => $item->screenGroup->id,
                                'name' => $item->screenGroup->name,
                                'color' => $item->screenGroup->color
                            ] : null,
                            'custom_properties' => $item->custom_properties
                        ];
                    })
                ];
            })
        ]);
    }

    /**
     * Get default screen for a company
     */
    public function getDefaultScreen(Request $request)
    {
        $user = Auth::user();
        $companyId = $user->company_id;

        $screen = Screen::getDefaultForCompany($companyId);

        // Fallback: if no default screen is set, return the most recently updated active screen
        $usedFallback = false;
        if (!$screen) {
            $screen = Screen::forCompany($companyId)
                ->active()
                ->orderByDesc('updated_at')
                ->first();
            $usedFallback = (bool) $screen;
        }

        if (!$screen) {
            return response()->json([
                'success' => false,
                'message' => 'No screens found for this company'
            ], 404);
        }

        $screen->load([
            'screenItems' => function ($query) {
                $query->where('is_active', true);
            },
            'screenItems.product:id,name,price,barcode,category_id',
            'screenItems.screenGroup:id,name,color'
        ]);

        return response()->json([
            'success' => true,
            'fallback_used' => $usedFallback,
            'screen' => [
                'id' => $screen->id,
                'name' => $screen->name,
                'description' => $screen->description,
                'grid_rows' => $screen->grid_rows,
                'grid_columns' => $screen->grid_columns,
                'background_color' => $screen->background_color,
                'is_default' => $screen->is_default,
                'layout_grid' => $screen->getLayoutGrid(),
                'items' => $screen->screenItems->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'type' => $item->type,
                        'display_name' => $item->display_name,
                        'background_color' => $item->getBackgroundColor(),
                        'text_color' => $item->text_color,
                        'grid_x' => $item->grid_x,
                        'grid_y' => $item->grid_y,
                        'width' => $item->width,
                        'height' => $item->height,
                        'product' => $item->product ? [
                            'id' => $item->product->id,
                            'name' => $item->product->name,
                            'price' => $item->product->price,
                            'barcode' => $item->product->barcode,
                            'category_id' => $item->product->category_id
                        ] : null,
                        'screen_group' => $item->screenGroup ? [
                            'id' => $item->screenGroup->id,
                            'name' => $item->screenGroup->name,
                            'color' => $item->screenGroup->color
                        ] : null,
                        'custom_properties' => $item->custom_properties
                    ];
                })
            ]
        ]);
    }

    /**
     * Get screen groups for a company
     */
    public function getScreenGroups(Request $request)
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
                    'sort_order' => $group->sort_order
                ];
            })
        ]);
    }
    /**
     * Get active products (sales items) by group for the authenticated user's company
     */
    public function getItemsByGroup(Request $request, $groupId)
    {
        $user = Auth::user();
        $companyId = $user->company_id;

        $products = Product::active()
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
     * Get screen by ID
     */
    public function getScreen(Request $request, $screenId)
    {
        $user = Auth::user();
        $companyId = $user->company_id;

        $screen = Screen::with([
                'screenItems' => function ($query) {
                    $query->where('is_active', true);
                },
                'screenItems.product:id,name,price,barcode,category_id',
                'screenItems.screenGroup:id,name,color'
            ])
            ->forCompany($companyId)
            ->active()
            ->find($screenId);

        if (!$screen) {
            return response()->json([
                'success' => false,
                'message' => 'Screen not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'screen' => [
                'id' => $screen->id,
                'name' => $screen->name,
                'description' => $screen->description,
                'grid_rows' => $screen->grid_rows,
                'grid_columns' => $screen->grid_columns,
                'background_color' => $screen->background_color,
                'is_default' => $screen->is_default,
                'layout_grid' => $screen->getLayoutGrid(),
                'items' => $screen->screenItems->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'type' => $item->type,
                        'display_name' => $item->display_name,
                        'background_color' => $item->getBackgroundColor(),
                        'text_color' => $item->text_color,
                        'grid_x' => $item->grid_x,
                        'grid_y' => $item->grid_y,
                        'width' => $item->width,
                        'height' => $item->height,
                        'product' => $item->product ? [
                            'id' => $item->product->id,
                            'name' => $item->product->name,
                            'price' => $item->product->price,
                            'barcode' => $item->product->barcode,
                            'category_id' => $item->product->category_id
                        ] : null,
                        'screen_group' => $item->screenGroup ? [
                            'id' => $item->screenGroup->id,
                            'name' => $item->screenGroup->name,
                            'color' => $item->screenGroup->color
                        ] : null,
                        'custom_properties' => $item->custom_properties
                    ];
                })
            ]
        ]);
    }

    /**
     * Check for screen updates (for sync)
     */
    public function checkUpdates(Request $request)
    {
        $user = Auth::user();
        $companyId = $user->company_id;
        $lastSync = $request->input('last_sync');

        $query = Screen::forCompany($companyId)->active();

        if ($lastSync) {
            $query->where('updated_at', '>', $lastSync);
        }

        $updatedScreens = $query->pluck('id');

        return response()->json([
            'success' => true,
            'has_updates' => $updatedScreens->count() > 0,
            'updated_screen_ids' => $updatedScreens,
            'server_time' => now()->toISOString()
        ]);
    }

    /**
     * Trigger sync for a specific screen
     */
    public function syncScreen($screenId)
    {
        try {
            $user = Auth::user();
            $companyId = $user->company_id;

            $screen = Screen::with(['screenItems.product', 'screenItems.screenGroup'])
                ->findOrFail($screenId);

            // Check if user has access to this screen's company
            if ($screen->company_id !== $companyId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access to this screen.'
                ], 403);
            }

            // Update the screen's updated_at timestamp to trigger sync
            $screen->touch();

            // Log sync activity (optional)
            \Log::info("Screen sync triggered", [
                'screen_id' => $screenId,
                'screen_name' => $screen->name,
                'company_id' => $companyId,
                'items_count' => $screen->screenItems->count()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Screen sync triggered successfully',
                'screen' => [
                    'id' => $screen->id,
                    'name' => $screen->name,
                    'updated_at' => $screen->updated_at->toISOString(),
                    'items_count' => $screen->screenItems->count()
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error("Screen sync failed", [
                'screen_id' => $screenId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to sync screen: ' . $e->getMessage()
            ], 500);
        }
    }
}
