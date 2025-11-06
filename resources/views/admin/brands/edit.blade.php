<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Brand: {{ $brand->name }} - ERP System</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f9f9f9; padding: 20px; margin: 0; }
        .container { max-width: 800px; margin: 0 auto; }
        .header { background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin-bottom: 20px; }
        .header h1 { margin: 0; color: #333; }
        .form-container { background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; color: #333; }
        .form-group input, .form-group select, .form-group textarea { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; font-size: 14px; }
        .form-group input:focus, .form-group select:focus, .form-group textarea:focus { outline: none; border-color: #007bff; }
        .form-group small { color: #666; font-size: 12px; }
        .error { color: #dc3545; font-size: 12px; margin-top: 5px; }
        .btn { padding: 12px 24px; border: none; border-radius: 5px; text-decoration: none; display: inline-block; cursor: pointer; font-size: 14px; margin-right: 10px; }
        .btn-primary { background: #007bff; color: white; }
        .btn-secondary { background: #6c757d; color: white; }
        .btn-danger { background: #dc3545; color: white; }
        .btn:hover { opacity: 0.9; }
        .alert { padding: 15px; border-radius: 5px; margin-bottom: 20px; }
        .alert-danger { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 15px; margin-bottom: 20px; }
        .stat-card { background: #f8f9fa; padding: 15px; border-radius: 5px; text-align: center; }
        .stat-number { font-size: 24px; font-weight: bold; color: #007bff; }
        .stat-label { font-size: 12px; color: #666; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Edit Brand: {{ $brand->name }}</h1>
        </div>

        @if($errors->any())
            <div class="alert alert-danger">
                <strong>Please fix the following errors:</strong>
                <ul style="margin: 10px 0 0 20px;">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Brand Statistics -->
        <div class="form-container" style="margin-bottom: 20px;">
            <h3 style="margin: 0 0 15px 0;">Brand Statistics</h3>
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-number">{{ $brand->categories->count() }}</div>
                    <div class="stat-label">Categories</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number">{{ $brand->branches->count() }}</div>
                    <div class="stat-label">Branches</div>
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
                    <div class="stat-number">
                        {{ $brand->branches->sum(function($branch) { return $branch->posDevices->count(); }) }}
                    </div>
                    <div class="stat-label">POS Devices</div>
                </div>
            </div>
        </div>

        <div class="form-container">
            <form method="POST" action="{{ route('admin.brands.update', $brand) }}">
                @csrf
                @method('PUT')
                
                <div class="form-group">
                    <label for="company_id">Company *</label>
                    <select name="company_id" id="company_id" required>
                        @foreach($companies as $company)
                            <option value="{{ $company->id }}" {{ old('company_id', $brand->company_id) == $company->id ? 'selected' : '' }}>
                                {{ $company->name }}
                                @if($company->license_expiry < now())
                                    (License Expired)
                                @endif
                            </option>
                        @endforeach
                    </select>
                    <small>Company this brand belongs to</small>
                    @error('company_id')
                        <div class="error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="name">Brand Name *</label>
                    <input type="text" name="name" id="name" value="{{ old('name', $brand->name) }}" required>
                    <small>Enter a unique name for this brand</small>
                    @error('name')
                        <div class="error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea name="description" id="description" rows="4">{{ old('description', $brand->description) }}</textarea>
                    <small>Optional description of the brand</small>
                    @error('description')
                        <div class="error">{{ $message }}</div>
                    @enderror
                </div>

                <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee;">
                    <button type="submit" class="btn btn-primary">Update Brand</button>
                    <a href="{{ route('admin.brands.show', $brand) }}" class="btn btn-secondary">View Details</a>
                    <a href="{{ route('admin.brands.index') }}" class="btn btn-secondary">Back to List</a>
                </div>
            </form>
        </div>

        <!-- Danger Zone -->
        <div class="form-container" style="margin-top: 20px; border: 2px solid #dc3545;">
            <h3 style="margin: 0 0 15px 0; color: #dc3545;">Danger Zone</h3>
            <p style="color: #666; margin-bottom: 15px;">
                Deleting this brand will permanently remove all associated categories, divisions, groups, products, and branches. This action cannot be undone.
            </p>
            @if($brand->categories->count() > 0 || $brand->branches->count() > 0)
                <p style="color: #dc3545; margin-bottom: 15px;">
                    <strong>Cannot delete:</strong> This brand has {{ $brand->categories->count() }} categories and {{ $brand->branches->count() }} branches. Remove all related data first.
                </p>
            @else
                <form method="POST" action="{{ route('admin.brands.destroy', $brand) }}" onsubmit="return confirm('Are you sure you want to delete {{ $brand->name }}? This action cannot be undone.')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Delete Brand</button>
                </form>
            @endif
        </div>
    </div>
</body>
</html>
