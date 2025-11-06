<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $user->name }} - User Details</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f9f9f9; padding: 20px; margin: 0; }
        .container { max-width: 1000px; margin: 0 auto; }
        .header { background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin-bottom: 20px; }
        .header h1 { margin: 0; color: #333; }
        .header .actions { margin-top: 15px; }
        .btn { padding: 10px 20px; border: none; border-radius: 5px; text-decoration: none; display: inline-block; cursor: pointer; font-size: 14px; margin-right: 10px; }
        .btn-primary { background: #007bff; color: white; }
        .btn-warning { background: #ffc107; color: white; }
        .btn-secondary { background: #6c757d; color: white; }
        .btn-danger { background: #dc3545; color: white; }
        .btn:hover { opacity: 0.9; }
        .info-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .info-card { background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .info-card h3 { margin: 0 0 15px 0; color: #333; border-bottom: 2px solid #007bff; padding-bottom: 5px; }
        .info-row { display: flex; justify-content: space-between; margin-bottom: 10px; padding: 8px 0; border-bottom: 1px solid #eee; }
        .info-label { font-weight: bold; color: #666; }
        .info-value { color: #333; }
        .role-badge { padding: 6px 12px; border-radius: 4px; font-size: 12px; font-weight: bold; }
        .role-admin { background: #007bff; color: white; }
        .role-manager { background: #28a745; color: white; }
        .role-user { background: #6c757d; color: white; }
        .role-pos_user { background: #ffc107; color: black; }
        .company-badge { background: #e9ecef; padding: 4px 8px; border-radius: 4px; font-size: 12px; color: #495057; }
        .alert { padding: 15px; border-radius: 5px; margin-bottom: 20px; }
        .alert-warning { background: #fff3cd; color: #856404; border: 1px solid #ffeaa7; }
        .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>{{ $user->name }}</h1>
            <div class="actions">
                <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">← Back to Users</a>
                <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-warning">Edit User</a>
                @if($user->id !== 1)
                    <form method="POST" action="{{ route('admin.users.destroy', $user) }}" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this user?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Delete User</button>
                    </form>
                @endif
            </div>
        </div>

        @if($user->company && $user->company->license_expiry < now())
            <div class="alert alert-warning">
                <strong>Company License Expired:</strong> This user's company license expired on {{ $user->company->license_expiry->format('M d, Y') }}.
            </div>
        @elseif($user->company && $user->company->license_expiry < now()->addDays(30))
            <div class="alert alert-warning">
                <strong>License Expiring Soon:</strong> This user's company license will expire on {{ $user->company->license_expiry->format('M d, Y') }}.
            </div>
        @endif

        <!-- User Information -->
        <div class="info-grid">
            <div class="info-card">
                <h3>User Details</h3>
                <div class="info-row">
                    <span class="info-label">User ID:</span>
                    <span class="info-value">{{ $user->id }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Full Name:</span>
                    <span class="info-value">{{ $user->name }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Email:</span>
                    <span class="info-value">{{ $user->email }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Role:</span>
                    <span class="info-value">
                        <span class="role-badge role-{{ $user->role }}">
                            {{ ucfirst(str_replace('_', ' ', $user->role)) }}
                        </span>
                    </span>
                </div>
                <div class="info-row">
                    <span class="info-label">Created:</span>
                    <span class="info-value">{{ $user->created_at->format('M d, Y') }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Last Updated:</span>
                    <span class="info-value">{{ $user->updated_at->format('M d, Y') }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">PIN Code:</span>
                    <span class="info-value">
                        @if($user->pin)
                            <span style="background: #e9ecef; padding: 4px 8px; border-radius: 4px; font-family: monospace;">
                                ****{{ substr($user->pin, -2) }}
                            </span>
                            <small style="color: #666; margin-left: 10px;">POS Login Enabled</small>
                        @else
                            <span style="color: #666;">Not Set</span>
                            <small style="color: #666; margin-left: 10px;">POS Login Disabled</small>
                        @endif
                    </span>
                </div>
            </div>

            <div class="info-card">
                <h3>Company & Branch Information</h3>
                <div class="info-row">
                    <span class="info-label">Company:</span>
                    <span class="info-value">
                        @if($user->company)
                            <span class="company-badge">{{ $user->company->name }}</span>
                        @else
                            <span style="color: #666;">No Company Assigned</span>
                        @endif
                    </span>
                </div>
                @if($user->company)
                <div class="info-row">
                    <span class="info-label">License Status:</span>
                    <span class="info-value" style="color: {{ $user->company->license_expiry > now() ? '#28a745' : '#dc3545' }};">
                        {{ $user->company->license_expiry > now() ? 'Active' : 'Expired' }}
                    </span>
                </div>
                <div class="info-row">
                    <span class="info-label">License Expiry:</span>
                    <span class="info-value">{{ $user->company->license_expiry->format('M d, Y') }}</span>
                </div>
                @endif
                <div class="info-row">
                    <span class="info-label">Branch:</span>
                    <span class="info-value">
                        @if($user->branch)
                            {{ $user->branch->name }}
                            @if($user->branch->brand)
                                <small style="color: #666;">({{ $user->branch->brand->name }})</small>
                            @endif
                        @else
                            <span style="color: #666;">No Branch Assigned</span>
                        @endif
                    </span>
                </div>
            </div>
        </div>

        <!-- Role Permissions -->
        <div class="info-card">
            <h3>Role Permissions</h3>
            @php
                $permissions = [
                    'admin' => [
                        'Full access to company data',
                        'Can manage brands, branches, users, and POS devices',
                        'Can view all reports and analytics',
                        'Can configure system settings'
                    ],
                    'manager' => [
                        'Can manage products and inventory',
                        'Can view sales reports',
                        'Can manage assigned brand/branch data',
                        'Limited user management'
                    ],
                    'user' => [
                        'Basic access to view data',
                        'Can perform limited operations',
                        'Can view assigned data only',
                        'Cannot modify system settings'
                    ],
                    'pos_user' => [
                        'Access to POS system for sales',
                        'Can process transactions',
                        'Basic inventory viewing',
                        'Limited to assigned branch only'
                    ]
                ];
            @endphp
            
            @if(isset($permissions[$user->role]))
                <ul style="margin: 0; padding-left: 20px;">
                    @foreach($permissions[$user->role] as $permission)
                        <li style="margin-bottom: 5px; color: #666;">{{ $permission }}</li>
                    @endforeach
                </ul>
            @else
                <p style="color: #666;">No specific permissions defined for this role.</p>
            @endif
        </div>

        <!-- Activity Summary -->
        <div class="info-card">
            <h3>Activity Summary</h3>
            <div class="info-row">
                <span class="info-label">Account Status:</span>
                <span class="info-value" style="color: #28a745;">Active</span>
            </div>
            <div class="info-row">
                <span class="info-label">Last Login:</span>
                <span class="info-value" style="color: #666;">Never logged in</span>
            </div>
            <div class="info-row">
                <span class="info-label">Total Sales:</span>
                <span class="info-value">0</span>
            </div>
            <div class="info-row">
                <span class="info-label">Access Level:</span>
                <span class="info-value">
                    @if($user->company)
                        Company: {{ $user->company->name }}
                    @else
                        System Level
                    @endif
                </span>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="info-card">
            <h3>Quick Actions</h3>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
                <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-primary" style="text-align: center;">
                    Edit User Details
                </a>
                @if($user->company)
                    <a href="{{ route('admin.companies.show', $user->company) }}" class="btn btn-secondary" style="text-align: center;">
                        View Company
                    </a>
                @endif
                @if($user->branch)
                    <a href="#" class="btn btn-secondary" style="text-align: center;">
                        View Branch
                    </a>
                @endif
                <a href="{{ route('admin.users.create', ['company_id' => $user->company_id]) }}" class="btn btn-success" style="text-align: center;">
                    Add Another User
                </a>
            </div>
        </div>
    </div>
</body>
</html>
