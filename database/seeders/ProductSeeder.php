<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Group;
use App\Models\Division;
use App\Models\Category;
use App\Models\Brand;
use App\Models\Company;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get the first company
        $company = Company::first();
        
        if (!$company) {
            $this->command->info('No company found. Please run CompanySeeder first.');
            return;
        }

        // Get or create a brand
        $brand = Brand::firstOrCreate([
            'name' => 'Default Brand',
            'company_id' => $company->id
        ], [
            'description' => 'Default brand for testing'
        ]);

        // Get or create categories
        $categories = [
            'APPETIZERS' => 'Appetizers and Starters',
            'ENTREES' => 'Main Course Entrees',
            'DESSERTS' => 'Desserts and Sweets',
            'BEVERAGES' => 'Drinks and Beverages',
            'PIZZA' => 'Pizza Items',
            'PASTA' => 'Pasta Dishes',
            'SALADS' => 'Fresh Salads',
            'SIDES' => 'Side Dishes'
        ];

        foreach ($categories as $categoryName => $categoryDesc) {
            $category = Category::firstOrCreate([
                'name' => $categoryName,
                'brand_id' => $brand->id
            ], [
                'description' => $categoryDesc,
                'is_active' => true
            ]);

            // Create a division for each category
            $division = Division::firstOrCreate([
                'name' => $categoryName . ' Division',
                'category_id' => $category->id
            ], [
                'description' => 'Division for ' . $categoryDesc,
                'is_active' => true
            ]);

            // Create a group for each division
            $group = Group::firstOrCreate([
                'name' => $categoryName . ' Group',
                'division_id' => $division->id
            ], [
                'description' => 'Group for ' . $categoryDesc,
                'is_active' => true
            ]);

            // Create products for each group
            $this->createProductsForGroup($group, $categoryName);
        }

        $this->command->info('Products seeded successfully!');
    }

    private function createProductsForGroup($group, $categoryName)
    {
        $products = [];

        switch ($categoryName) {
            case 'APPETIZERS':
                $products = [
                    ['name' => 'Mozzarella Sticks', 'price' => 8.99, 'sku' => 'APP001'],
                    ['name' => 'Buffalo Wings', 'price' => 12.99, 'sku' => 'APP002'],
                    ['name' => 'Onion Rings', 'price' => 6.99, 'sku' => 'APP003'],
                    ['name' => 'Nachos Supreme', 'price' => 10.99, 'sku' => 'APP004'],
                ];
                break;

            case 'ENTREES':
                $products = [
                    ['name' => 'Grilled Chicken', 'price' => 18.99, 'sku' => 'ENT001'],
                    ['name' => 'Beef Steak', 'price' => 24.99, 'sku' => 'ENT002'],
                    ['name' => 'Fish & Chips', 'price' => 16.99, 'sku' => 'ENT003'],
                    ['name' => 'BBQ Ribs', 'price' => 22.99, 'sku' => 'ENT004'],
                ];
                break;

            case 'PIZZA':
                $products = [
                    ['name' => 'Margherita Pizza', 'price' => 14.99, 'sku' => 'PIZ001'],
                    ['name' => 'Pepperoni Pizza', 'price' => 16.99, 'sku' => 'PIZ002'],
                    ['name' => 'Supreme Pizza', 'price' => 19.99, 'sku' => 'PIZ003'],
                    ['name' => 'Hawaiian Pizza', 'price' => 17.99, 'sku' => 'PIZ004'],
                ];
                break;

            case 'PASTA':
                $products = [
                    ['name' => 'Spaghetti Carbonara', 'price' => 15.99, 'sku' => 'PAS001'],
                    ['name' => 'Fettuccine Alfredo', 'price' => 14.99, 'sku' => 'PAS002'],
                    ['name' => 'Penne Arrabbiata', 'price' => 13.99, 'sku' => 'PAS003'],
                    ['name' => 'Lasagna', 'price' => 17.99, 'sku' => 'PAS004'],
                ];
                break;

            case 'SALADS':
                $products = [
                    ['name' => 'Caesar Salad', 'price' => 11.99, 'sku' => 'SAL001'],
                    ['name' => 'Greek Salad', 'price' => 12.99, 'sku' => 'SAL002'],
                    ['name' => 'Garden Salad', 'price' => 9.99, 'sku' => 'SAL003'],
                    ['name' => 'Cobb Salad', 'price' => 13.99, 'sku' => 'SAL004'],
                ];
                break;

            case 'DESSERTS':
                $products = [
                    ['name' => 'Chocolate Cake', 'price' => 7.99, 'sku' => 'DES001'],
                    ['name' => 'Tiramisu', 'price' => 8.99, 'sku' => 'DES002'],
                    ['name' => 'Ice Cream Sundae', 'price' => 5.99, 'sku' => 'DES003'],
                    ['name' => 'Cheesecake', 'price' => 8.99, 'sku' => 'DES004'],
                ];
                break;

            case 'BEVERAGES':
                $products = [
                    ['name' => 'Coca Cola', 'price' => 2.99, 'sku' => 'BEV001'],
                    ['name' => 'Orange Juice', 'price' => 3.99, 'sku' => 'BEV002'],
                    ['name' => 'Coffee', 'price' => 2.49, 'sku' => 'BEV003'],
                    ['name' => 'Iced Tea', 'price' => 2.99, 'sku' => 'BEV004'],
                ];
                break;

            case 'SIDES':
                $products = [
                    ['name' => 'French Fries', 'price' => 4.99, 'sku' => 'SID001'],
                    ['name' => 'Garlic Bread', 'price' => 5.99, 'sku' => 'SID002'],
                    ['name' => 'Coleslaw', 'price' => 3.99, 'sku' => 'SID003'],
                    ['name' => 'Mashed Potatoes', 'price' => 4.99, 'sku' => 'SID004'],
                ];
                break;

            default:
                $products = [
                    ['name' => $categoryName . ' Item 1', 'price' => 9.99, 'sku' => 'GEN001'],
                    ['name' => $categoryName . ' Item 2', 'price' => 12.99, 'sku' => 'GEN002'],
                ];
                break;
        }

        foreach ($products as $productData) {
            Product::firstOrCreate([
                'sku' => $productData['sku']
            ], [
                'name' => $productData['name'],
                'group_id' => $group->id,
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