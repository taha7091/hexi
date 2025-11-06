@extends('layouts.admin')

@section('title', 'Users Management - ERP System')

@section('page-title', 'Users Management')

@section('breadcrumb', 'Home > Admin > Users')

@section('styles')
<style>
    .filters {
        background: #fff;
        padding: 20px;
        border-radius: 12px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        margin-bottom: 25px;
        display: flex;
        flex-wrap: wrap;
        gap: 15px;
        align-items: center;
    }
    .filters select, .filters input {
        padding: 10px 14px;
        border: 2px solid #e9ecef;
        border-radius: 8px;
        font-size: 14px;
        transition: all 0.3s;
    }
    .filters select:focus, .filters input:focus {
        outline: none;
        border-color: #3498db;
        box-shadow: 0 0 0 3px rgba(52,152,219,0.1);
    }
    .table-container {
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        overflow: hidden;
    }
    table { width: 100%; border-collapse: collapse; }
    th, td { padding: 15px; text-align: left; border-bottom: 1px solid #f1f3f4; }
    th {
        background: #f8f9fa;
        font-weight: 600;
        color: #2c3e50;
        font-size: 13px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    tr:hover { background: #f8f9fa; }
    .actions { display: flex; gap: 8px; flex-wrap: wrap; }
    .role-badge {
        padding: 6px 12px;
        border-radius: 8px;
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }
    .role-admin { background: linear-gradient(135deg, #3498db, #2980b9); color: white; }
    .role-manager { background: linear-gradient(135deg, #27ae60, #229954); color: white; }
    .role-user { background: linear-gradient(135deg, #95a5a6, #7f8c8d); color: white; }
    .role-pos_user { background: linear-gradient(135deg, #f39c12, #e67e22); color: white; }
    .company-badge {
        background: linear-gradient(135deg, #e9ecef, #f8f9fa);
        padding: 6px 12px;
        border-radius: 8px;
        font-size: 12px;
        color: #495057;
        font-weight: 500;
    }
    .stats-cards {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }
    .stat-card {
        background: white;
        padding: 20px;
        border-radius: 12px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        text-align: center;
        transition: transform 0.3s ease;
    }
    .stat-card:hover { transform: translateY(-5px); }
    .stat-number { font-size: 28px; font-weight: bold; color: #2c3e50; margin-bottom: 5px; }
    .stat-label { font-size: 13px; color: #7f8c8d; text-transform: uppercase; letter-spacing: 0.5px; }
</style>
@endsection

@section('content')

<div class="header-actions" style="margin-bottom: 20px;">
    <a href="{{ route('admin.users.create') }}" class="btn btn-primary">
        <i style="margin-right: 8px;">➕</i> Add New User
    </a>
</div>

<!-- Filters -->
<div class="filters">
    <form method="GET" style="display: flex; gap: 15px; flex-wrap: wrap; align-items: center;">
        <div>
            <label for="company_id">Company:</label>
            <select name="company_id" id="company_id" onchange="this.form.submit()">
                <option value="">All Companies</option>
                @foreach($companies as $company)
                    <option value="{{ $company->id }}" {{ request('company_id') == $company->id ? 'selected' : '' }}>
                        {{ $company->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="role">Role:</label>
            <select name="role" id="role" onchange="this.form.submit()">
                <option value="">All Roles</option>
                <option value="admin" {{ request('role') == 'admin' ? 'selected' : '' }}>Admin</option>
                <option value="manager" {{ request('role') == 'manager' ? 'selected' : '' }}>Manager</option>
                <option value="user" {{ request('role') == 'user' ? 'selected' : '' }}>User</option>
                <option value="pos_user" {{ request('role') == 'pos_user' ? 'selected' : '' }}>POS User</option>
            </select>
        </div>

        <div>
            <input type="text" name="search" placeholder="Search users..." value="{{ request('search') }}" 
                   onkeyup="if(event.key==='Enter') this.form.submit()" style="width: 200px;">
        </div>

        <button type="submit" class="btn btn-primary">Search</button>
        @if(request()->hasAny(['company_id', 'role', 'search']))
            <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">Clear</a>
        @endif
    </form>
</div>

<div class="table-container">
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Role</th>
                <th>PIN</th>
                <th>Company</th>
                <th>Branch</th>
                <th>Created</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($users as $user)
                <tr>
                    <td>{{ $user->id }}</td>
                    <td><strong>{{ $user->name }}</strong></td>
                    <td>{{ $user->email }}</td>
                    <td>
                        <span class="role-badge role-{{ $user->role }}">
                            {{ ucfirst(str_replace('_', ' ', $user->role)) }}
                        </span>
                        @if($user->companyRole)
                            <div style="margin-top: 4px;">
                                <span style="background: {{ $user->companyRole->color }}; color: white; padding: 2px 6px; border-radius: 4px; font-size: 11px;">
                                    {{ $user->companyRole->icon }} {{ $user->companyRole->name }}
                                </span>
                            </div>
                        @endif
                    </td>
                    <td>
                        @if($user->pin)
                            <span style="background: #e9ecef; padding: 2px 6px; border-radius: 3px; font-family: monospace; font-size: 11px;">
                                ****{{ substr($user->pin, -2) }}
                            </span>
                        @else
                            <span style="color: #999; font-size: 11px;">No PIN</span>
                        @endif
                    </td>
                    <td>
                        @if($user->company)
                            <span class="company-badge">{{ $user->company->name }}</span>
                        @else
                            <span style="color: #666;">No Company</span>
                        @endif
                    </td>
                    <td>
                        @if($user->branch)
                            {{ $user->branch->name }}
                        @else
                            <span style="color: #666;">Not assigned</span>
                        @endif
                    </td>
                    <td>{{ $user->created_at->format('M d, Y') }}</td>
                    <td>
                        <div class="actions">
                            <a href="{{ route('admin.users.show', $user) }}" class="btn btn-primary btn-sm">View</a>
                            <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-warning btn-sm">Edit</a>
                            @if($user->id !== 1)
                                <form method="POST" action="{{ route('admin.users.destroy', $user) }}" onsubmit="return confirm('Are you sure you want to delete this user?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" style="text-align: center; padding: 40px; color: #666;">
                        @if(request()->hasAny(['company_id','role','search']))
                            No users found matching your criteria. <a href="{{ route('admin.users.index') }}">View all users</a>
                        @else
                            No users found. <a href="{{ route('admin.users.create') }}">Add the first user</a>
                        @endif
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($users->hasPages())
    <div style="margin-top: 30px; text-align: center;">
        {{ $users->appends(request()->query())->links() }}
    </div>
@endif

<!-- Quick Stats -->
<div class="stats-cards" style="margin-top: 30px;">
    <div class="stat-card">
        <div class="stat-number">{{ $users->total() }}</div>
        <div class="stat-label">Total Users</div>
    </div>
    <div class="stat-card">
        <div class="stat-number">
            {{ \App\Models\User::where('role', 'admin')->where('id', '!=', 1)->count() }}
        </div>
        <div class="stat-label">Admins</div>
    </div>
    <div class="stat-card">
        <div class="stat-number">
            {{ \App\Models\User::where('role', 'pos_user')->count() }}
        </div>
        <div class="stat-label">POS Users</div>
    </div>
    <div class="stat-card">
        <div class="stat-number">
            {{ \App\Models\User::whereHas('company', function($q) { $q->where('license_expiry', '<', now()); })->count() }}
        </div>
        <div class="stat-label">Expired License</div>
    </div>
</div>

@endsection
