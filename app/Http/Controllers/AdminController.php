<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Company;
use App\Models\Brand;
use App\Models\User;

class AdminController extends Controller
{
    // Middleware is applied in routes/web.php

    // Company Management
    public function companiesIndex()
    {
        $companies = Company::with(['brands', 'branches.posDevices', 'users'])
                           ->paginate(15);

        // Get sales count for each company
        foreach ($companies as $company) {
            $companyBranches = \App\Models\Branch::where('company_id', $company->id)->pluck('id');
            $company->sales_count = \App\Models\Sale::whereIn('branch_id', $companyBranches)->count();
            $company->total_sales_amount = \App\Models\Sale::whereIn('branch_id', $companyBranches)->sum('total_amount');
        }

        return view('admin.companies.index', compact('companies'));
    }

    public function companiesCreate()
    {
        return view('admin.companies.create');
    }

    public function companiesStore(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'license_key' => 'required|string|max:255|unique:companies,license_key',
            'license_expiry' => 'required|date|after:today',
            'pos_limit' => 'required|integer|min:1|max:100',
            'status' => 'nullable|in:active,inactive,suspended',
            'contact_email' => 'nullable|email',
            'contact_phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'notes' => 'nullable|string'
        ]);

        $validated['status'] = $validated['status'] ?? 'active';

        $company = Company::create($validated);

        return redirect()->route('admin.companies.show', $company)
                        ->with('success', 'Company created successfully!');
    }

    public function companiesShow(Company $company)
    {
        $company->load(['brands.categories', 'branches.posDevices', 'users.branch']);

        return view('admin.companies.show', compact('company'));
    }

    public function companiesEdit(Company $company)
    {
        return view('admin.companies.edit', compact('company'));
    }

    public function companiesUpdate(Request $request, Company $company)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'license_key' => 'required|string|max:255|unique:companies,license_key,' . $company->id,
            'license_expiry' => 'required|date',
            'pos_limit' => 'required|integer|min:1|max:100',
            'status' => 'nullable|in:active,inactive,suspended',
            'contact_email' => 'nullable|email',
            'contact_phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'notes' => 'nullable|string'
        ]);

        $company->update($validated);

        return redirect()->route('admin.companies.show', $company)
                        ->with('success', 'Company updated successfully!');
    }

    public function companiesDestroy(Company $company)
    {
        // Check if company has any data
        if ($company->brands()->count() > 0 || $company->users()->count() > 0) {
            return redirect()->route('admin.companies.index')
                           ->with('error', 'Cannot delete company with existing brands or users.');
        }

        $companyName = $company->name;
        $company->delete();

        return redirect()->route('admin.companies.index')
                        ->with('success', "Company '{$companyName}' deleted successfully!");
    }

    // Brand Management
    public function brandsIndex(Request $request)
    {
        $query = Brand::with(['company', 'categories', 'branches']);

        if ($request->filled('company_id')) {
            $query->where('company_id', $request->company_id);
        }

        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where('name', 'like', '%' . $searchTerm . '%');
        }

        $brands = $query->paginate(15);
        $companies = Company::all();

        return view('admin.brands.index', compact('brands', 'companies'));
    }

    public function brandsCreate(Request $request)
    {
        $companies = Company::all();
        $selectedCompany = $request->get('company_id');

        return view('admin.brands.create', compact('companies', 'selectedCompany'));
    }

    public function brandsStore(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'company_id' => 'required|exists:companies,id',
            'description' => 'nullable|string'
        ]);

        $brand = Brand::create($validated);

        return redirect()->route('admin.brands.index')
                        ->with('success', 'Brand created successfully!');
    }

    public function brandsShow(Brand $brand)
    {
        $brand->load(['company', 'categories.divisions.groups.products', 'branches.posDevices']);

        return view('admin.brands.show', compact('brand'));
    }

    public function brandsEdit(Brand $brand)
    {
        $companies = Company::all();

        return view('admin.brands.edit', compact('brand', 'companies'));
    }

    public function brandsUpdate(Request $request, Brand $brand)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'company_id' => 'required|exists:companies,id',
            'description' => 'nullable|string'
        ]);

        $brand->update($validated);

        return redirect()->route('admin.brands.show', $brand)
                        ->with('success', 'Brand updated successfully!');
    }

    public function brandsDestroy(Brand $brand)
    {
        // Check if brand has any data
        if ($brand->categories()->count() > 0 || $brand->branches()->count() > 0) {
            return redirect()->route('admin.brands.index')
                           ->with('error', 'Cannot delete brand with existing categories or branches.');
        }

        $brandName = $brand->name;
        $brand->delete();

        return redirect()->route('admin.brands.index')
                        ->with('success', "Brand '{$brandName}' deleted successfully!");
    }

    // User Management
    public function usersIndex(Request $request)
    {
        $currentUser = Auth::user();
        $query = User::with(['company', 'branch', 'companyRole'])
                    ->where('id', '!=', 1); // Exclude master admin

        // Role-based filtering
        if ($currentUser->isMasterAdmin()) {
            // Master admin can see all users
            if ($request->has('company_id')) {
                $query->where('company_id', $request->company_id);
            }
        } elseif ($currentUser->isAdmin()) {
            // Company admin can see all users in their company
            $query->where('company_id', $currentUser->company_id);
        } elseif ($currentUser->isManager()) {
            // Manager can only see cashiers in their company
            $query->where('company_id', $currentUser->company_id)
                  ->where('role', 'cashier');
        } else {
            // Other users can't access user management
            abort(403, 'Unauthorized access to user management.');
        }

        if ($request->has('role')) {
            $query->where('role', $request->role);
        }

        $users = $query->paginate(15);
        $companies = $currentUser->isMasterAdmin() ? Company::all() : collect([$currentUser->company]);

        return view('admin.users.index', compact('users', 'companies'));
    }

    public function usersCreate(Request $request)
    {
        $currentUser = Auth::user();

        // Role-based access control
        if ($currentUser->isMasterAdmin()) {
            $companies = Company::with('branches')->get();
            $allowedRoles = ['admin', 'manager', 'user', 'pos_user', 'cashier'];
        } elseif ($currentUser->isAdmin()) {
            $companies = collect([$currentUser->company->load('branches')]);
            $allowedRoles = ['manager', 'user', 'pos_user', 'cashier'];
        } elseif ($currentUser->isManager()) {
            $companies = collect([$currentUser->company->load('branches')]);
            $allowedRoles = ['cashier']; // Managers can only create cashiers
        } else {
            abort(403, 'Unauthorized access to user creation.');
        }

        $selectedCompany = $request->get('company_id', $currentUser->company_id);

        // Get custom company roles for the selected company
        $companyRoles = collect();
        if ($selectedCompany) {
            $companyRoles = \App\Models\CompanyRole::where('company_id', $selectedCompany)
                                                  ->where('is_active', true)
                                                  ->with('privileges')
                                                  ->get();
        }

        return view('admin.users.create', compact('companies', 'selectedCompany', 'allowedRoles', 'companyRoles'));
    }

    public function usersStore(Request $request)
    {
        $currentUser = Auth::user();

        // Define allowed roles based on current user's role
        $allowedRoles = [];
        if ($currentUser->isMasterAdmin()) {
            $allowedRoles = ['admin', 'manager', 'user', 'pos_user', 'cashier'];
        } elseif ($currentUser->isAdmin()) {
            $allowedRoles = ['manager', 'user', 'pos_user', 'cashier'];
        } elseif ($currentUser->isManager()) {
            $allowedRoles = ['cashier']; // Managers can only create cashiers
        } else {
            abort(403, 'Unauthorized access to user creation.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
            'company_id' => 'required|exists:companies,id',
            'branch_id' => 'nullable|exists:branches,id',
            'role' => 'required|in:' . implode(',', $allowedRoles),
            'company_role_id' => 'nullable|exists:company_roles,id',
            'pin' => 'nullable|string|max:10|regex:/^[0-9]+$/|unique:users,pin'
        ]);

        // Additional validation: ensure company_id matches current user's company (except for master admin)
        if (!$currentUser->isMasterAdmin() && $validated['company_id'] != $currentUser->company_id) {
            abort(403, 'You can only create users for your own company.');
        }

        // Check PIN uniqueness within the same company
        if (!empty($validated['pin'])) {
            $existingPin = User::where('pin', $validated['pin'])
                              ->where('company_id', $validated['company_id'])
                              ->first();

            if ($existingPin) {
                return back()->withErrors(['pin' => 'This PIN is already in use by another user in this company.'])
                            ->withInput();
            }
        }

        $validated['password'] = bcrypt($validated['password']);

        $user = User::create($validated);

        return redirect()->route('admin.users.show', $user)
                        ->with('success', 'User created successfully!' . ($validated['pin'] ? ' PIN: ' . $validated['pin'] : ''));
    }

    public function usersShow(User $user)
    {
        $user->load(['company', 'branch.brand']);

        return view('admin.users.show', compact('user'));
    }

    public function usersEdit(User $user)
    {
        $currentUser = Auth::user();

        // Role-based access control
        if ($currentUser->isMasterAdmin()) {
            $companies = Company::with('branches')->get();
            $allowedRoles = ['admin', 'manager', 'user', 'pos_user', 'cashier'];
        } elseif ($currentUser->isAdmin()) {
            // Company admin can only edit users in their company
            if ($user->company_id != $currentUser->company_id) {
                abort(403, 'You can only edit users from your own company.');
            }
            $companies = collect([$currentUser->company->load('branches')]);
            $allowedRoles = ['manager', 'user', 'pos_user', 'cashier'];
        } elseif ($currentUser->isManager()) {
            // Manager can only edit cashiers in their company
            if ($user->company_id != $currentUser->company_id || $user->role != 'cashier') {
                abort(403, 'You can only edit cashiers from your own company.');
            }
            $companies = collect([$currentUser->company->load('branches')]);
            $allowedRoles = ['cashier'];
        } else {
            abort(403, 'Unauthorized access to user editing.');
        }

        // Get custom company roles for the user's company
        $companyRoles = \App\Models\CompanyRole::where('company_id', $user->company_id)
                                              ->where('is_active', true)
                                              ->with('privileges')
                                              ->get();

        return view('admin.users.edit', compact('user', 'companies', 'allowedRoles', 'companyRoles'));
    }

    public function usersUpdate(Request $request, User $user)
    {
        $currentUser = Auth::user();

        // Define allowed roles based on current user's role
        $allowedRoles = [];
        if ($currentUser->isMasterAdmin()) {
            $allowedRoles = ['admin', 'manager', 'user', 'pos_user', 'cashier'];
        } elseif ($currentUser->isAdmin()) {
            // Company admin can only edit users in their company
            if ($user->company_id != $currentUser->company_id) {
                abort(403, 'You can only edit users from your own company.');
            }
            $allowedRoles = ['manager', 'user', 'pos_user', 'cashier'];
        } elseif ($currentUser->isManager()) {
            // Manager can only edit cashiers in their company
            if ($user->company_id != $currentUser->company_id || $user->role != 'cashier') {
                abort(403, 'You can only edit cashiers from your own company.');
            }
            $allowedRoles = ['cashier'];
        } else {
            abort(403, 'Unauthorized access to user editing.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:6|confirmed',
            'company_id' => 'required|exists:companies,id',
            'branch_id' => 'nullable|exists:branches,id',
            'role' => 'required|in:' . implode(',', $allowedRoles),
            'company_role_id' => 'nullable|exists:company_roles,id',
            'pin' => 'nullable|string|max:10|regex:/^[0-9]+$/'
        ]);

        // Additional validation: ensure company_id matches current user's company (except for master admin)
        if (!$currentUser->isMasterAdmin() && $validated['company_id'] != $currentUser->company_id) {
            abort(403, 'You can only edit users for your own company.');
        }

        // Check PIN uniqueness within the same company (excluding current user)
        if (!empty($validated['pin'])) {
            $existingPin = User::where('pin', $validated['pin'])
                              ->where('company_id', $validated['company_id'])
                              ->where('id', '!=', $user->id)
                              ->first();

            if ($existingPin) {
                return back()->withErrors(['pin' => 'This PIN is already in use by another user in this company.'])
                            ->withInput();
            }
        }

        if (!empty($validated['password'])) {
            $validated['password'] = bcrypt($validated['password']);
        } else {
            unset($validated['password']);
        }

        $user->update($validated);

        return redirect()->route('admin.users.show', $user)
                        ->with('success', 'User updated successfully!' . ($validated['pin'] ? ' PIN: ' . $validated['pin'] : ''));
    }

    public function usersDestroy(User $user)
    {
        // Prevent deleting master admin
        if ($user->id === 1) {
            return redirect()->route('admin.users.index')
                           ->with('error', 'Cannot delete master admin user.');
        }

        $userName = $user->name;
        $user->delete();

        return redirect()->route('admin.users.index')
                        ->with('success', "User '{$userName}' deleted successfully!");
    }

    /**
     * Reset sales for a specific company (Master Admin only)
     */
    public function resetCompanySales(Request $request, $id)
    {
        // Check if user is master admin
        $user = Auth::user();
        if (!$user || $user->role !== 'master_admin') {
            return response()->json([
                'success' => false,
                'error' => 'Access denied. Only master administrators can reset sales data.'
            ], 403);
        }

        $company = Company::findOrFail($id);

        // Validate confirmation
        if ($request->input('confirmation') !== 'DELETE') {
            return response()->json([
                'success' => false,
                'error' => 'Invalid confirmation'
            ], 400);
        }

        try {
            // Get sales for this company through branches
            $companyBranches = \App\Models\Branch::where('company_id', $company->id)->pluck('id');
            $companySales = \App\Models\Sale::whereIn('branch_id', $companyBranches)->pluck('id');

            // Get counts before deletion
            $salesCount = \App\Models\Sale::whereIn('id', $companySales)->count();
            $saleItemsCount = \App\Models\SaleItem::whereIn('sale_id', $companySales)->count();
            $paymentsCount = \App\Models\Payment::whereIn('sale_id', $companySales)->count();

            // Delete in correct order using transaction
            \DB::transaction(function () use ($companySales) {
                \App\Models\Payment::whereIn('sale_id', $companySales)->delete();
                \App\Models\SaleItem::whereIn('sale_id', $companySales)->delete();
                \App\Models\Sale::whereIn('id', $companySales)->delete();
            });

            // Log the action
            \Log::warning('MASTER ADMIN RESET COMPANY SALES VIA WEB', [
                'admin_id' => Auth::id(),
                'admin_name' => Auth::user()->name,
                'company_id' => $company->id,
                'company_name' => $company->name,
                'deleted_sales' => $salesCount,
                'deleted_sale_items' => $saleItemsCount,
                'deleted_payments' => $paymentsCount,
                'timestamp' => now()->toISOString(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            return response()->json([
                'success' => true,
                'message' => "Successfully deleted {$salesCount} sales for {$company->name}",
                'data' => [
                    'deleted_sales' => $salesCount,
                    'deleted_sale_items' => $saleItemsCount,
                    'deleted_payments' => $paymentsCount
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('FAILED TO RESET COMPANY SALES VIA WEB', [
                'admin_id' => Auth::id(),
                'company_id' => $company->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to reset sales: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove all data for a specific company (Master Admin only)
     * WARNING: This will permanently delete ALL data including products, users, brands, sales, categories, groups
     */
    public function removeAllCompanyData(Request $request, $id)
    {
        // Check if user is master admin
        $user = Auth::user();
        if (!$user || $user->role !== 'master_admin') {
            return response()->json([
                'success' => false,
                'error' => 'Access denied. Only master administrators can remove all company data.'
            ], 403);
        }

        $company = Company::findOrFail($id);

        // Validate confirmation
        if ($request->input('confirmation') !== 'REMOVE_ALL_DATA') {
            return response()->json([
                'success' => false,
                'error' => 'Invalid confirmation. Must be "REMOVE_ALL_DATA"'
            ], 400);
        }

        try {
            $deletionStats = [];

            \DB::transaction(function () use ($company, &$deletionStats) {
                // Get all branches for this company
                $companyBranches = \App\Models\Branch::where('company_id', $company->id)->pluck('id');

                // Get all sales for this company
                $companySales = \App\Models\Sale::whereIn('branch_id', $companyBranches)->pluck('id');

                // Get all brands for this company
                $companyBrands = \App\Models\Brand::where('company_id', $company->id)->pluck('id');

                // Get all categories for these brands
                $companyCategories = \App\Models\Category::whereIn('brand_id', $companyBrands)->pluck('id');

                // Get all divisions for these categories
                $companyDivisions = \App\Models\Division::whereIn('category_id', $companyCategories)->pluck('id');

                // Get all groups for these divisions
                $companyGroups = \App\Models\Group::whereIn('division_id', $companyDivisions)->pluck('id');

                // Get all products for these groups
                $companyProducts = \App\Models\Product::whereIn('group_id', $companyGroups)->pluck('id');

                // Get all POS devices for these branches
                $companyPosDevices = \App\Models\PosDevice::whereIn('branch_id', $companyBranches)->pluck('id');

                // Count everything before deletion
                $deletionStats['payments'] = \App\Models\Payment::whereIn('sale_id', $companySales)->count();
                $deletionStats['sale_items'] = \App\Models\SaleItem::whereIn('sale_id', $companySales)->count();
                $deletionStats['sales'] = \App\Models\Sale::whereIn('id', $companySales)->count();
                $deletionStats['pos_devices'] = \App\Models\PosDevice::whereIn('id', $companyPosDevices)->count();
                $deletionStats['products'] = \App\Models\Product::whereIn('id', $companyProducts)->count();
                $deletionStats['groups'] = \App\Models\Group::whereIn('id', $companyGroups)->count();
                $deletionStats['divisions'] = \App\Models\Division::whereIn('id', $companyDivisions)->count();
                $deletionStats['categories'] = \App\Models\Category::whereIn('id', $companyCategories)->count();
                $deletionStats['users'] = \App\Models\User::where('company_id', $company->id)->where('id', '!=', 1)->count(); // Don't count master admin
                $deletionStats['branches'] = \App\Models\Branch::whereIn('id', $companyBranches)->count();
                $deletionStats['brands'] = \App\Models\Brand::whereIn('id', $companyBrands)->count();

                // Delete in correct order (respecting foreign key constraints)

                // 1. Delete sales-related data
                \App\Models\Payment::whereIn('sale_id', $companySales)->delete();
                \App\Models\SaleItem::whereIn('sale_id', $companySales)->delete();
                \App\Models\Sale::whereIn('id', $companySales)->delete();

                // 2. Delete POS devices
                \App\Models\PosDevice::whereIn('id', $companyPosDevices)->delete();

                // 3. Delete products
                \App\Models\Product::whereIn('id', $companyProducts)->delete();

                // 4. Delete groups
                \App\Models\Group::whereIn('id', $companyGroups)->delete();

                // 5. Delete divisions
                \App\Models\Division::whereIn('id', $companyDivisions)->delete();

                // 6. Delete categories
                \App\Models\Category::whereIn('id', $companyCategories)->delete();

                // 7. Delete users (except master admin)
                \App\Models\User::where('company_id', $company->id)->where('id', '!=', 1)->delete();

                // 8. Delete branches
                \App\Models\Branch::whereIn('id', $companyBranches)->delete();

                // 9. Delete brands
                \App\Models\Brand::whereIn('id', $companyBrands)->delete();

                // 10. Delete POS limits
                \App\Models\PosLimit::where('company_id', $company->id)->delete();
            });

            // Log the action
            \Log::warning('MASTER ADMIN REMOVED ALL COMPANY DATA', [
                'admin_id' => $user->id,
                'admin_name' => $user->name,
                'admin_email' => $user->email,
                'company_id' => $company->id,
                'company_name' => $company->name,
                'deletion_stats' => $deletionStats,
                'timestamp' => now()->toISOString(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            return response()->json([
                'success' => true,
                'message' => "All data for company '{$company->name}' has been permanently deleted.",
                'data' => [
                    'company_id' => $company->id,
                    'company_name' => $company->name,
                    'deleted' => $deletionStats,
                    'removed_by' => $user->name,
                    'removed_at' => now()->toISOString()
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('FAILED TO REMOVE ALL COMPANY DATA', [
                'admin_id' => $user->id,
                'company_id' => $company->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to remove company data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Add default brand data (Master Admin only)
     */
    public function addBrandDefaultData(Request $request)
    {
        // Check if user is admin or master admin
        if (!Auth::user()->isAdmin() && !Auth::user()->isMasterAdmin()) {
            return redirect()->back()->with('error', 'Unauthorized. Admin privileges required.');
        }

        $validated = $request->validate([
            'company_id' => 'required|exists:companies,id'
        ]);

        $company = \App\Models\Company::find($validated['company_id']);

        // Check if company already has brands
        if ($company->brands()->count() > 0) {
            return redirect()->back()->with('error', 'Company already has brands. Default data not added.');
        }

        // Create default brand
        $brand = \App\Models\Brand::create([
            'name' => $company->name . ' - Default Brand',
            'company_id' => $company->id,
            'description' => 'Default brand created automatically for ' . $company->name
        ]);

        // Create default categories, divisions, groups, and products
        $this->createDefaultBrandStructure($brand);

        return redirect()->route('admin.brands.index')
                        ->with('success', 'Default brand data created successfully for ' . $company->name . '! Brand includes 4 categories with divisions, groups, and sample products.');
    }

    /**
     * Create default brand structure with categories, divisions, groups, and products
     */
    private function createDefaultBrandStructure($brand)
    {
        // Default categories
        $categories = [
            ['name' => 'Food & Beverages', 'description' => 'Food and drink items'],
            ['name' => 'Electronics', 'description' => 'Electronic devices and accessories'],
            ['name' => 'Clothing', 'description' => 'Apparel and fashion items']
        ];

        foreach ($categories as $index => $categoryData) {
            $category = \App\Models\Category::create([
                'name' => $categoryData['name'],
                'brand_id' => $brand->id,
                'description' => $categoryData['description'],
                'sort_order' => $index + 1,
                'is_active' => true
            ]);

            // Create default divisions for each category
            $this->createDefaultDivisions($category);
        }
    }

    /**
     * Create default divisions for a category
     */
    private function createDefaultDivisions($category)
    {
        $divisionsByCategory = [
            'Food & Beverages' => [
                ['name' => 'Snacks', 'description' => 'Chips, crackers, and snack foods'],
                ['name' => 'Beverages', 'description' => 'Soft drinks, juices, and water'],
                ['name' => 'Dairy', 'description' => 'Milk, cheese, and dairy products']
            ],
            'Electronics' => [
                ['name' => 'Mobile Devices', 'description' => 'Phones, tablets, and accessories'],
                ['name' => 'Computers', 'description' => 'Laptops, desktops, and peripherals'],
                ['name' => 'Audio/Video', 'description' => 'Headphones, speakers, and entertainment']
            ],
            'Clothing' => [
                ['name' => 'Men\'s Wear', 'description' => 'Men\'s clothing and accessories'],
                ['name' => 'Women\'s Wear', 'description' => 'Women\'s clothing and accessories'],
                ['name' => 'Kids Wear', 'description' => 'Children\'s clothing and accessories']
            ],
        
        ];

        $divisions = $divisionsByCategory[$category->name] ?? [
            ['name' => 'General', 'description' => 'General items for ' . $category->name]
        ];

        foreach ($divisions as $index => $divisionData) {
            $division = \App\Models\Division::create([
                'name' => $divisionData['name'],
                'category_id' => $category->id,
                'description' => $divisionData['description'],
                'sort_order' => $index + 1,
                'is_active' => true
            ]);

            // Create default groups for each division
            $this->createDefaultGroups($division);
        }
    }

    /**
     * Create default groups for a division
     */
    private function createDefaultGroups($division)
    {
        $groups = [
            ['name' => 'Standard', 'description' => 'Standard items'],
            ['name' => 'Premium', 'description' => 'Premium quality items']
        ];

        foreach ($groups as $index => $groupData) {
            $group = \App\Models\Group::create([
                'name' => $groupData['name'],
                'division_id' => $division->id,
                'description' => $groupData['description'],
                'sort_order' => $index + 1,
                'is_active' => true
            ]);

            // Create sample products for each group
            $this->createDefaultProducts($group);
        }
    }

    /**
     * Create default products for a group
     */
    private function createDefaultProducts($group)
    {
        $products = [
            [
                'name' => 'Sample Product 1',
                'description' => 'Sample product for ' . $group->division->category->name,
                'price' => 10.00,
                'cost_price' => 6.00,
                'stock_quantity' => 100,
                'barcode' => 'SP001' . $group->id,
                'sku' => 'SKU001' . $group->id
            ],
            [
                'name' => 'Sample Product 2',
                'description' => 'Another sample product for ' . $group->division->category->name,
                'price' => 25.00,
                'cost_price' => 15.00,
                'stock_quantity' => 50,
                'barcode' => 'SP002' . $group->id,
                'sku' => 'SKU002' . $group->id
            ]
        ];

        foreach ($products as $index => $productData) {
            \App\Models\Product::create([
                'name' => $productData['name'],
                'description' => $productData['description'],
                'price' => $productData['price'],
                'cost_price' => $productData['cost_price'],
                'stock_quantity' => $productData['stock_quantity'],
                'barcode' => $productData['barcode'],
                'sku' => $productData['sku'],
                'group_id' => $group->id,
                'category_id' => $group->division->category_id,
                'division_id' => $group->division_id,
                'sort_order' => $index + 1,
                'is_active' => true
            ]);
        }
    }

    /**
     * Create default users (manager and cashier) for a company
     */
    private function createDefaultUsers($company)
    {
        // Create default manager
        $manager = \App\Models\User::create([
            'company_id' => $company->id,
            'name' => $company->name . ' - Manager',
            'email' => 'manager@' . strtolower(str_replace(' ', '', $company->name)) . '.com',
            'password' => \Illuminate\Support\Facades\Hash::make('manager123'),
            'pin' => '9999',
            'role' => 'manager'
        ]);

        // Create default cashier
        $cashier = \App\Models\User::create([
            'company_id' => $company->id,
            'name' => $company->name . ' - Cashier',
            'email' => 'cashier@' . strtolower(str_replace(' ', '', $company->name)) . '.com',
            'password' => \Illuminate\Support\Facades\Hash::make('cashier123'),
            'pin' => '1234',
            'role' => 'cashier'
        ]);

        // Create default cashier privileges (restrictive by default)
        \App\Models\CashierPrivilege::create([
            'company_id' => $company->id,
            'cashier_id' => $cashier->id,
            'managed_by' => $manager->id,
            'can_view_older_sales' => false, // Cannot see older sales by default
            'can_process_refunds' => false,
            'can_apply_discounts' => false,
            'can_void_transactions' => false,
            'can_modify_prices' => false,
            'can_press_end_of_day' => false, // Cannot press end of day by default
            'can_view_daily_reports' => false,
            'can_view_cash_drawer' => true,
            'can_add_products' => false,
            'can_edit_products' => false,
            'can_manage_inventory' => false,
            'can_access_settings' => false,
            'can_backup_data' => false,
        ]);
    }
}
