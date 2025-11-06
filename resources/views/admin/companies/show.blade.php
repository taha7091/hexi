<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $company->name }} - Company Details</title>
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
        .status-active { color: #28a745; font-weight: bold; }
        .status-expired { color: #dc3545; font-weight: bold; }
        .status-inactive { color: #ffc107; font-weight: bold; }
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 15px; margin-bottom: 20px; }
        .stat-card { background: #f8f9fa; padding: 20px; border-radius: 8px; text-align: center; border-left: 4px solid #007bff; }
        .stat-number { font-size: 32px; font-weight: bold; color: #007bff; margin-bottom: 5px; }
        .stat-label { font-size: 14px; color: #666; }
        .table-container { background: #fff; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); overflow: hidden; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #eee; }
        th { background: #f8f9fa; font-weight: bold; color: #333; }
        tr:hover { background: #f8f9fa; }
        .alert { padding: 15px; border-radius: 5px; margin-bottom: 20px; }
        .alert-warning { background: #fff3cd; color: #856404; border: 1px solid #ffeaa7; }
        .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>{{ $company->name }}</h1>
            <div class="actions">
                <a href="{{ route('admin.companies.index') }}" class="btn btn-secondary">← Back to Companies</a>
                <a href="{{ route('admin.companies.edit', $company) }}" class="btn btn-warning">Edit Company</a>
                <a href="{{ route('admin.brands.create', ['company_id' => $company->id]) }}" class="btn btn-primary">+ Add Brand</a>
            </div>
        </div>

        @if($company->license_expiry < now())
            <div class="alert alert-warning">
                <strong>License Expired:</strong> This company's license expired on {{ $company->license_expiry->format('M d, Y') }}.
            </div>
        @elseif($company->license_expiry < now()->addDays(30))
            <div class="alert alert-warning">
                <strong>License Expiring Soon:</strong> This company's license will expire on {{ $company->license_expiry->format('M d, Y') }}.
            </div>
        @else
            <div class="alert alert-success">
                <strong>License Active:</strong> Valid until {{ $company->license_expiry->format('M d, Y') }}.
            </div>
        @endif

        <!-- Statistics Overview -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number">{{ $company->brands->count() }}</div>
                <div class="stat-label">Brands</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">{{ $company->branches->count() }}</div>
                <div class="stat-label">Branches</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">{{ $company->users->count() }}</div>
                <div class="stat-label">Users</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">{{ $company->active_device_count }}</div>
                <div class="stat-label">Active POS Devices</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">{{ $company->device_limit }}</div>
                <div class="stat-label">POS Limit</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">{{ $company->remaining_device_slots }}</div>
                <div class="stat-label">Remaining Slots</div>
            </div>
        </div>

        <!-- Company Information -->
        <div class="info-grid">
            <div class="info-card">
                <h3>Company Details</h3>
                <div class="info-row">
                    <span class="info-label">Company ID:</span>
                    <span class="info-value">{{ $company->id }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">License Key:</span>
                    <span class="info-value"><code>{{ $company->license_key }}</code></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Status:</span>
                    <span class="info-value">
                        @if($company->license_expiry > now())
                            <span class="status-active">Active</span>
                        @else
                            <span class="status-expired">Expired</span>
                        @endif
                    </span>
                </div>
                <div class="info-row">
                    <span class="info-label">License Expiry:</span>
                    <span class="info-value">{{ $company->license_expiry->format('M d, Y') }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Created:</span>
                    <span class="info-value">{{ $company->created_at->format('M d, Y') }}</span>
                </div>
            </div>

            <div class="info-card">
                <h3>Contact Information</h3>
                <div class="info-row">
                    <span class="info-label">Email:</span>
                    <span class="info-value">{{ $company->contact_email ?? 'Not provided' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Phone:</span>
                    <span class="info-value">{{ $company->contact_phone ?? 'Not provided' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Address:</span>
                    <span class="info-value">{{ $company->address ?? 'Not provided' }}</span>
                </div>
                @if($company->notes)
                <div class="info-row">
                    <span class="info-label">Notes:</span>
                    <span class="info-value">{{ $company->notes }}</span>
                </div>
                @endif
            </div>
        </div>

        <!-- Brands -->
        @if($company->brands->count() > 0)
        <div class="table-container">
            <h3 style="margin: 0; padding: 20px; background: #f8f9fa; border-bottom: 1px solid #eee;">Brands ({{ $company->brands->count() }})</h3>
            <table>
                <thead>
                    <tr>
                        <th>Brand Name</th>
                        <th>Categories</th>
                        <th>Branches</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($company->brands as $brand)
                    <tr>
                        <td><strong>{{ $brand->name }}</strong></td>
                        <td>{{ $brand->categories->count() }}</td>
                        <td>{{ $brand->branches->count() }}</td>
                        <td>{{ $brand->created_at->format('M d, Y') }}</td>
                        <td>
                            <a href="{{ route('admin.brands.show', $brand) }}" class="btn btn-primary" style="padding: 5px 10px; font-size: 12px;">View</a>
                            <a href="{{ route('admin.brands.edit', $brand) }}" class="btn btn-warning" style="padding: 5px 10px; font-size: 12px;">Edit</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="info-card">
            <h3>Brands</h3>
            <p style="color: #666; text-align: center; padding: 20px;">
                No brands created yet. <a href="{{ route('admin.brands.create', ['company_id' => $company->id]) }}">Create the first brand</a>
            </p>
        </div>
        @endif

        <!-- Users -->
        @if($company->users->count() > 0)
        <div class="table-container">
            <h3 style="margin: 0; padding: 20px; background: #f8f9fa; border-bottom: 1px solid #eee;">Users ({{ $company->users->count() }})</h3>
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Branch</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($company->users as $user)
                    <tr>
                        <td><strong>{{ $user->name }}</strong></td>
                        <td>{{ $user->email }}</td>
                        <td>{{ ucfirst($user->role) }}</td>
                        <td>{{ $user->branch->name ?? 'Not assigned' }}</td>
                        <td>{{ $user->created_at->format('M d, Y') }}</td>
                        <td>
                            <a href="{{ route('admin.users.show', $user) }}" class="btn btn-primary" style="padding: 5px 10px; font-size: 12px;">View</a>
                            <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-warning" style="padding: 5px 10px; font-size: 12px;">Edit</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="info-card">
            <h3>Users</h3>
            <p style="color: #666; text-align: center; padding: 20px;">
                No users created yet. <a href="{{ route('admin.users.create', ['company_id' => $company->id]) }}">Create the first user</a>
            </p>
        </div>
        @endif
    </div>
</body>
</html>
