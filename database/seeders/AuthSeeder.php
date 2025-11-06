<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\Company;
use App\Models\Brand;
use App\Models\Branch;
use App\Models\User;

class AuthSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Master Admin User (Force ID to be 1) - No company association
        $masterAdmin = new User([
            'company_id' => null, // Master admin is not tied to any company
            'branch_id' => null,
            'role' => 'master_admin',
            'name' => 'Master Administrator',
            'email' => 'admin@erp-system.com',
            'password' => Hash::make('master123')
        ]);
        $masterAdmin->id = 1;
        $masterAdmin->save();

        // Create Additional Master Admin with password 123123
        User::create([
            'company_id' => null,
            'branch_id' => null,
            'role' => 'master_admin',
            'name' => 'Master taha ',
            'email' => 'mastertaha@example.com',
            'password' => Hash::make('123123')
        ]);

        // Create Company 1 - First Licensed Company
        $company1 = Company::create([
            'name' => 'ABC Retail Store',
            'license_key' => 'ABC-LICENSE-2024',
            'license_expiry' => now()->addYear(),
            'pos_limit' => 5
        ]);

        // Create Brand for Company 1
        $brand1 = Brand::create([
            'company_id' => $company1->id,
            'name' => 'ABC Fashion'
        ]);

        // Create Branch for Company 1
        $branch1 = Branch::create([
            'company_id' => $company1->id,
            'brand_id' => $brand1->id,
            'name' => 'Main Store'
        ]);

        // Create Company 1 Admin
        User::create([
            'company_id' => $company1->id,
            'branch_id' => $branch1->id,
            'role' => 'admin',
            'name' => 'ABC Admin',
            'email' => 'admin@abc-retail.com',
            'password' => Hash::make('admin123'),
            'pin' => '9999'
        ]);

        // Create Company 1 Manager
        User::create([
            'company_id' => $company1->id,
            'branch_id' => $branch1->id,
            'role' => 'manager',
            'name' => 'Store Manager',
            'email' => 'manager@abc-retail.com',
            'password' => Hash::make('manager123'),
            'pin' => '5678'
        ]);

        // Create Company 1 Cashier
        User::create([
            'company_id' => $company1->id,
            'branch_id' => $branch1->id,
            'role' => 'cashier',
            'name' => 'Cashier',
            'email' => 'cashier@abc-retail.com',
            'password' => Hash::make('cashier123'),
            'pin' => '1234'
        ]);

        // Create Company 2 - Second Licensed Company
        $company2 = Company::create([
            'name' => 'XYZ Electronics',
            'license_key' => 'XYZ-LICENSE-2024',
            'license_expiry' => now()->addMonths(6),
            'pos_limit' => 3
        ]);

        // Create Brand for Company 2
        $brand2 = Brand::create([
            'company_id' => $company2->id,
            'name' => 'XYZ Tech'
        ]);

        // Create Branch for Company 2
        $branch2 = Branch::create([
            'company_id' => $company2->id,
            'brand_id' => $brand2->id,
            'name' => 'Electronics Store'
        ]);

        // Create Company 2 Admin
        User::create([
            'company_id' => $company2->id,
            'branch_id' => $branch2->id,
            'role' => 'admin',
            'name' => 'XYZ Admin',
            'email' => 'admin@xyz-electronics.com',
            'password' => Hash::make('admin123'),
            'pin' => '8888'
        ]);

        // Create Company 2 Manager
        User::create([
            'company_id' => $company2->id,
            'branch_id' => $branch2->id,
            'role' => 'manager',
            'name' => 'Electronics Manager',
            'email' => 'manager@xyz-electronics.com',
            'password' => Hash::make('manager123'),
            'pin' => '7777'
        ]);

        // Create Company 2 Cashier
        User::create([
            'company_id' => $company2->id,
            'branch_id' => $branch2->id,
            'role' => 'cashier',
            'name' => 'Electronics Cashier',
            'email' => 'cashier@xyz-electronics.com',
            'password' => Hash::make('cashier123'),
            'pin' => '6666'
        ]);

        $this->command->info('Authentication seeder completed successfully!');
        $this->command->info('=== MASTER ADMINS ===');
        $this->command->info('Master Admin: admin@erp-system.com / master123 (No Company ID needed)');
        $this->command->info('Master Admin 123: masteradmin@example.com / 123123 (No Company ID needed)');
        $this->command->info('=== COMPANY USERS ===');
        $this->command->info('Company 1 - ABC Retail Store:');
        $this->command->info('  Admin: admin@abc-retail.com / admin123 / PIN: 9999');
        $this->command->info('  Manager: manager@abc-retail.com / manager123 / PIN: 5678');
        $this->command->info('  Cashier: cashier@abc-retail.com / cashier123 / PIN: 1234');
        $this->command->info('Company 2 - XYZ Electronics:');
        $this->command->info('  Admin: admin@xyz-electronics.com / admin123 / PIN: 8888');
        $this->command->info('  Manager: manager@xyz-electronics.com / manager123 / PIN: 7777');
        $this->command->info('  Cashier: cashier@xyz-electronics.com / cashier123 / PIN: 6666');
    }
}
