<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $brand->name }} - Brand Details</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f9f9f9; padding: 20px; margin: 0; }
        .container { max-width: 1200px; margin: 0 auto; }
        .header { background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin-bottom: 20px; }
        .header h1 { margin: 0; color: #333; }
        .header .actions { margin-top: 15px; }
        .btn { padding: 10px 20px; border: none; border-radius: 5px; text-decoration: none; display: inline-block; cursor: pointer; font-size: 14px; margin-right: 10px; }
        .btn-primary { background: #007bff; color: white; }
        .btn-warning { background: #ffc107; color: white; }
        .btn-secondary { background: #6c757d; color: white; }
        .btn:hover { opacity: 0.9; }
        .info-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .info-card { background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .info-card h3 { margin: 0 0 15px 0; color: #333; border-bottom: 2px solid #007bff; padding-bottom: 5px; }
        .info-row { display: flex; justify-content: space-between; margin-bottom: 10px; padding: 8px 0; border-bottom: 1px solid #eee; }
        .info-label { font-weight: bold; color: #666; }
        .info-value { color: #333; }
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 15px; margin-bottom: 20px; }
        .stat-card { background: #f8f9fa; padding: 20px; border-radius: 8px; text-align: center; border-left: 4px solid #007bff; }
        .stat-number { font-size: 32px; font-weight: bold; color: #007bff; margin-bottom: 5px; }
        .stat-label { font-size: 14px; color: #666; }
        .table-container { background: #fff; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); overflow: hidden; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #eee; }
        th { background: #f8f9fa; font-weight: bold; color: #333; }
        tr:hover { background: #f8f9fa; }
        .company-badge { background: #e9ecef; padding: 4px 8px; border-radius: 4px; font-size: 12px; color: #495057; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>{{ $brand->name }}</h1>
            <div class="actions">
                <a href="{{ route('admin.brands.index') }}" class="btn btn-secondary">← Back to Brands</a>
                <a href="{{ route('admin.brands.edit', $brand) }}" class="btn btn-warning">Edit Brand</a>
                <a href="#" class="btn btn-primary">+ Add Category</a>
                <a href="#" class="btn btn-primary">+ Add Branch</a>
            </div>
        </div>

        <!-- Statistics Overview -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number">{{ $brand->categories->count() }}</div>
                <div class="stat-label">Categories</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">
                    {{ $brand->categories->sum(function($category) { return $category->divisions->count(); }) }}
                </div>
                <div class="stat-label">Divisions</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">
                    @php
                        $productCount = $brand->categories->sum(function($category) {
                            return $category->divisions->sum(function($division) {
                                return $division->groups->sum(function($group) {
                                    return $group->products->count();
                                });
                            });
                        });
                    @endphp
                    {{ $productCount }}
                </div>
                <div class="stat-label">Products</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">{{ $brand->branches->count() }}</div>
                <div class="stat-label">Branches</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">
                    {{ $brand->branches->sum(function($branch) { return $branch->posDevices->count(); }) }}
                </div>
                <div class="stat-label">POS Devices</div>
            </div>
        </div>

        <!-- Brand Information -->
        <div class="info-grid">
            <div class="info-card">
                <h3>Brand Details</h3>
                <div class="info-row">
                    <span class="info-label">Brand ID:</span>
                    <span class="info-value">{{ $brand->id }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Company:</span>
                    <span class="info-value">
                        <span class="company-badge">{{ $brand->company->name }}</span>
                    </span>
                </div>
                <div class="info-row">
                    <span class="info-label">Created:</span>
                    <span class="info-value">{{ $brand->created_at->format('M d, Y') }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Last Updated:</span>
                    <span class="info-value">{{ $brand->updated_at->format('M d, Y') }}</span>
                </div>
                @if($brand->description)
                <div class="info-row">
                    <span class="info-label">Description:</span>
                    <span class="info-value">{{ $brand->description }}</span>
                </div>
                @endif
            </div>

            <div class="info-card">
                <h3>Company Information</h3>
                <div class="info-row">
                    <span class="info-label">License Status:</span>
                    <span class="info-value" style="color: {{ $brand->company->license_expiry > now() ? '#28a745' : '#dc3545' }};">
                        {{ $brand->company->license_expiry > now() ? 'Active' : 'Expired' }}
                    </span>
                </div>
                <div class="info-row">
                    <span class="info-label">License Expiry:</span>
                    <span class="info-value">{{ $brand->company->license_expiry->format('M d, Y') }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">POS Limit:</span>
                    <span class="info-value">{{ $brand->company->pos_limit }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Total Brands:</span>
                    <span class="info-value">{{ $brand->company->brands->count() }}</span>
                </div>
            </div>
        </div>

        <!-- Categories -->
        @if($brand->categories->count() > 0)
        <div class="table-container">
            <h3 style="margin: 0; padding: 20px; background: #f8f9fa; border-bottom: 1px solid #eee;">Categories ({{ $brand->categories->count() }})</h3>
            <table>
                <thead>
                    <tr>
                        <th>Category Name</th>
                        <th>Divisions</th>
                        <th>Groups</th>
                        <th>Products</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($brand->categories as $category)
                    <tr>
                        <td><strong>{{ $category->name }}</strong></td>
                        <td>{{ $category->divisions->count() }}</td>
                        <td>{{ $category->divisions->sum(function($division) { return $division->groups->count(); }) }}</td>
                        <td>
                            {{ $category->divisions->sum(function($division) { 
                                return $division->groups->sum(function($group) { 
                                    return $group->products->count(); 
                                }); 
                            }) }}
                        </td>
                        <td>{{ $category->created_at->format('M d, Y') }}</td>
                        <td>
                            <a href="#" class="btn btn-primary" style="padding: 5px 10px; font-size: 12px;">View</a>
                            <a href="#" class="btn btn-warning" style="padding: 5px 10px; font-size: 12px;">Edit</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="info-card">
            <h3>Categories</h3>
            <p style="color: #666; text-align: center; padding: 20px;">
                No categories created yet. <a href="#">Create the first category</a>
            </p>
        </div>
        @endif

        <!-- Branches -->
        @if($brand->branches->count() > 0)
        <div class="table-container">
            <h3 style="margin: 0; padding: 20px; background: #f8f9fa; border-bottom: 1px solid #eee;">Branches ({{ $brand->branches->count() }})</h3>
            <table>
                <thead>
                    <tr>
                        <th>Branch Name</th>
                        <th>POS Devices</th>
                        <th>Users</th>
                        <th>Sales</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($brand->branches as $branch)
                    <tr>
                        <td><strong>{{ $branch->name }}</strong></td>
                        <td>{{ $branch->posDevices->count() }}</td>
                        <td>{{ $branch->users->count() }}</td>
                        <td>{{ $branch->sales->count() }}</td>
                        <td>{{ $branch->created_at->format('M d, Y') }}</td>
                        <td>
                            <a href="#" class="btn btn-primary" style="padding: 5px 10px; font-size: 12px;">View</a>
                            <a href="#" class="btn btn-warning" style="padding: 5px 10px; font-size: 12px;">Edit</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="info-card">
            <h3>Branches</h3>
            <p style="color: #666; text-align: center; padding: 20px;">
                No branches created yet. <a href="#">Create the first branch</a>
            </p>
        </div>
        @endif
    </div>
</body>
</html>
