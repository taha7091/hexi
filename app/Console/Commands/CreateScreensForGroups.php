<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Group;
use App\Models\Screen;

class CreateScreensForGroups extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'screens:create-for-groups';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create screens for all existing groups';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $groups = Group::all();
        $createdCount = 0;

        foreach ($groups as $group) {
            // Check if screens already exist for this group
            if ($group->screens()->count() > 0) {
                $this->info("Screens already exist for group: {$group->name}");
                continue;
            }

            $this->createScreensForGroup($group);
            $createdCount++;
            $this->info("Created screens for group: {$group->name}");
        }

        $this->info("Created screens for {$createdCount} groups");
    }

    private function createScreensForGroup(Group $group)
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
