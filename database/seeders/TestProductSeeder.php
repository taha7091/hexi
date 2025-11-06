<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Company;
use App\Models\Brand;
use App\Models\Branch;
use App\Models\Category;
use App\Models\Division;
use App\Models\Group;
use App\Models\Product;
use App\Models\User;

class TestProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Find or create a test company
        $company = Company::firstOrCreate([
            'name' => 'Test Restaurant'
        ], [
            'license_key' => 'TEST-LICENSE-KEY-123',
            'license_expiry' => now()->addYear(),
            'status' => 'active',
            'pos_limit' => 10,
            'max_devices' => 5
        ]);

        // Create a brand
        $brand = Brand::firstOrCreate([
            'company_id' => $company->id,
            'name' => 'Main Restaurant'
        ], [
            'description' => 'Main restaurant brand'
        ]);

        // Create a branch
        $branch = Branch::firstOrCreate([
            'brand_id' => $brand->id,
            'name' => 'Main Branch'
        ], [
            'address' => '123 Main St',
            'phone' => '555-0123'
        ]);

        // Create categories
        $categories = [
            'Food' => 'Food items',
            'Beverages' => 'Drinks and beverages',
            'Desserts' => 'Sweet treats'
        ];

        foreach ($categories as $catName => $catDesc) {
            $category = Category::firstOrCreate([
                'brand_id' => $brand->id,
                'name' => $catName
            ], [
                'description' => $catDesc,
                'is_active' => true
            ]);

            // Create divisions for each category
            $divisions = [
                'Food' => ['Hot Food', 'Cold Food'],
                'Beverages' => ['Hot Drinks', 'Cold Drinks'],
                'Desserts' => ['Cakes', 'Ice Cream']
            ];

            foreach ($divisions[$catName] as $divName) {
                $division = Division::firstOrCreate([
                    'category_id' => $category->id,
                    'name' => $divName
                ], [
                    'description' => $divName . ' division',
                    'is_active' => true
                ]);

                // Create groups for each division
                $groups = [
                    'Hot Food' => ['Appetizers', 'Entrees', 'Pizza'],
                    'Cold Food' => ['Salads', 'Sandwiches'],
                    'Hot Drinks' => ['Coffee', 'Tea'],
                    'Cold Drinks' => ['Sodas', 'Juices'],
                    'Cakes' => ['Chocolate Cake', 'Cheesecake'],
                    'Ice Cream' => ['Gelato', 'Sorbet']
                ];

                if (isset($groups[$divName])) {
                    foreach ($groups[$divName] as $groupName) {
                        $group = Group::firstOrCreate([
                            'division_id' => $division->id,
                            'name' => $groupName
                        ], [
                            'description' => $groupName . ' group',
                            'is_active' => true
                        ]);

                        // Create products for each group
                        $this->createProductsForGroup($group, $groupName);
                    }
                }
            }
        }

        // Create a test user with PIN
        User::firstOrCreate([
            'email' => 'cashier@test.com'
        ], [
            'name' => 'Test Cashier',
            'password' => bcrypt('password'),
            'pin' => '123456',
            'role' => 'cashier',
            'company_id' => $company->id,
            'branch_id' => $branch->id
        ]);

        $this->command->info('Test products and data created successfully!');
    }

    private function createProductsForGroup($group, $groupName)
    {
        $products = [
            'Appetizers' => [
                ['name' => 'Mozzarella Sticks', 'price' => 8.99],
                ['name' => 'Buffalo Wings', 'price' => 12.99],
                ['name' => 'Onion Rings', 'price' => 6.99]
            ],
            'Entrees' => [
                ['name' => 'Grilled Chicken', 'price' => 18.99],
                ['name' => 'Beef Steak', 'price' => 24.99],
                ['name' => 'Fish & Chips', 'price' => 16.99]
            ],
            'Pizza' => [
                ['name' => 'Margherita Pizza', 'price' => 15.99],
                ['name' => 'Pepperoni Pizza', 'price' => 17.99],
                ['name' => 'Supreme Pizza', 'price' => 19.99]
            ],
            'Salads' => [
                ['name' => 'Caesar Salad', 'price' => 11.99],
                ['name' => 'Greek Salad', 'price' => 10.99],
                ['name' => 'Garden Salad', 'price' => 9.99]
            ],
            'Sandwiches' => [
                ['name' => 'Club Sandwich', 'price' => 12.99],
                ['name' => 'BLT Sandwich', 'price' => 10.99],
                ['name' => 'Grilled Cheese', 'price' => 8.99]
            ],
            'Coffee' => [
                ['name' => 'Espresso', 'price' => 3.99],
                ['name' => 'Cappuccino', 'price' => 4.99],
                ['name' => 'Latte', 'price' => 5.99]
            ],
            'Tea' => [
                ['name' => 'Green Tea', 'price' => 2.99],
                ['name' => 'Earl Grey', 'price' => 3.49],
                ['name' => 'Chamomile Tea', 'price' => 3.49]
            ],
            'Sodas' => [
                ['name' => 'Coca Cola', 'price' => 2.99],
                ['name' => 'Pepsi', 'price' => 2.99],
                ['name' => 'Sprite', 'price' => 2.99]
            ],
            'Juices' => [
                ['name' => 'Orange Juice', 'price' => 3.99],
                ['name' => 'Apple Juice', 'price' => 3.99],
                ['name' => 'Cranberry Juice', 'price' => 4.49]
            ],
            'Chocolate Cake' => [
                ['name' => 'Dark Chocolate Cake', 'price' => 7.99],
                ['name' => 'Milk Chocolate Cake', 'price' => 7.99]
            ],
            'Cheesecake' => [
                ['name' => 'New York Cheesecake', 'price' => 8.99],
                ['name' => 'Strawberry Cheesecake', 'price' => 9.99]
            ],
            'Gelato' => [
                ['name' => 'Vanilla Gelato', 'price' => 5.99],
                ['name' => 'Chocolate Gelato', 'price' => 5.99],
                ['name' => 'Pistachio Gelato', 'price' => 6.99]
            ],
            'Sorbet' => [
                ['name' => 'Lemon Sorbet', 'price' => 4.99],
                ['name' => 'Berry Sorbet', 'price' => 5.99]
            ]
        ];

        if (isset($products[$groupName])) {
            foreach ($products[$groupName] as $productData) {
                Product::firstOrCreate([
                    'group_id' => $group->id,
                    'name' => $productData['name']
                ], [
                    'sku' => strtoupper(str_replace(' ', '_', $productData['name'])),
                    'price' => $productData['price'],
                    'cost_price' => $productData['price'] * 0.6, // 40% markup
                    'stock_quantity' => rand(10, 100),
                    'min_stock_level' => 5,
                    'is_active' => true,
                    'description' => 'Delicious ' . $productData['name']
                ]);
            }
        }
    }
}