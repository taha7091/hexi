<?php

namespace App\Observers;

use App\Models\Group;
use App\Models\Screen;

class GroupObserver
{
    /**
     * Handle the Group "created" event.
     */
    public function created(Group $group): void
    {
        // Automatically create screens for the new group
        $this->createDefaultScreens($group);
    }

    /**
     * Handle the Group "updated" event.
     */
    public function updated(Group $group): void
    {
        // Update screen names if group name changed
        if ($group->wasChanged('name')) {
            $group->screens()->update([
                'name' => $group->name . ' Screen'
            ]);
        }
    }

    /**
     * Handle the Group "deleted" event.
     */
    public function deleted(Group $group): void
    {
        // Screens will be automatically deleted due to cascade constraint
    }

    /**
     * Handle the Group "restored" event.
     */
    public function restored(Group $group): void
    {
        // Recreate screens if group is restored
        $this->createDefaultScreens($group);
    }

    /**
     * Handle the Group "force deleted" event.
     */
    public function forceDeleted(Group $group): void
    {
        //
    }

    /**
     * Create default screens for a group
     */
    private function createDefaultScreens(Group $group): void
    {
        $screens = [
            [
                'name' => $group->name . ' - Grid View',
                'description' => 'Grid layout for ' . $group->name . ' products',
                'screen_type' => 'grid',
                'background_color' => $group->color ?? '#ffffff',
                'sort_order' => 1,
                'layout_data' => [
                    'columns' => 4,
                    'rows' => 3,
                    'item_width' => 120,
                    'item_height' => 100,
                    'spacing' => 10
                ]
            ],
            [
                'name' => $group->name . ' - List View',
                'description' => 'List layout for ' . $group->name . ' products',
                'screen_type' => 'list',
                'background_color' => $group->color ?? '#ffffff',
                'sort_order' => 2,
                'layout_data' => [
                    'show_images' => true,
                    'show_prices' => true,
                    'show_stock' => true,
                    'item_height' => 60
                ]
            ],
            [
                'name' => $group->name . ' - Category View',
                'description' => 'Category-based layout for ' . $group->name . ' products',
                'screen_type' => 'category',
                'background_color' => $group->color ?? '#ffffff',
                'sort_order' => 3,
                'layout_data' => [
                    'group_by_category' => true,
                    'show_category_headers' => true,
                    'collapsible_categories' => true
                ]
            ]
        ];

        foreach ($screens as $screenData) {
            Screen::create(array_merge($screenData, [
                'group_id' => $group->id
            ]));
        }
    }
}
