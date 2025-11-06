@extends('layouts.admin')

@section('title', 'Cashier Privileges Management')

@section('content')
<div class="page-header">
    <div class="header-left">
        <div class="header-icon">👥</div>
        <div>
            <h2>Cashier Privileges Management</h2>
            <p>Configure what your cashiers can and cannot do in the POS system</p>
        </div>
    </div>
    <div class="header-right">
        <a href="{{ route('admin.users.create') }}" class="btn btn-primary">
            ➕ Add New Cashier
        </a>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger">
        {{ session('error') }}
    </div>
@endif

<div class="stats-cards">
    <div class="stat-card">
        <div class="stat-number">{{ $cashiers->count() }}</div>
        <div class="stat-label">Total Cashiers</div>
    </div>
    <div class="stat-card">
        <div class="stat-number">{{ $cashiers->where('cashierPrivileges')->count() }}</div>
        <div class="stat-label">Configured</div>
    </div>
    <div class="stat-card">
        <div class="stat-number">{{ $cashiers->whereNull('cashierPrivileges')->count() }}</div>
        <div class="stat-label">Default Settings</div>
    </div>
</div>

<div class="table-section">
    <div class="table-card">
        @if($cashiers->count() > 0)
            <table class="custom-table">
                <thead>
                    <tr>
                        <th>Cashier Name</th>
                        <th>Email</th>
                        <th>PIN</th>
                        <th>Older Sales</th>
                        <th>End of Day</th>
                        <th>Refunds</th>
                        <th>Discounts</th>
                        <th>Last Updated</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($cashiers as $cashier)
                        @php
                            $privileges = $cashier->cashierPrivileges;
                            $hasCustomPrivileges = $privileges !== null;
                        @endphp
                        <tr>
                            <td>
                                <strong>{{ $cashier->name }}</strong>
                                @if(!$hasCustomPrivileges)
                                    <span class="badge badge-warning">Default</span>
                                @endif
                            </td>
                            <td>{{ $cashier->email }}</td>
                            <td><code>{{ $cashier->pin ?? 'Not Set' }}</code></td>
                            <td>
                                @if($hasCustomPrivileges)
                                    {!! $privileges->can_view_older_sales ? '<span class="badge badge-success">✅ Yes</span>' : '<span class="badge badge-danger">❌ No</span>' !!}
                                @else
                                    <span class="badge badge-danger">❌ No</span>
                                @endif
                            </td>
                            <td>
                                @if($hasCustomPrivileges)
                                    {!! $privileges->can_press_end_of_day ? '<span class="badge badge-success">✅ Yes</span>' : '<span class="badge badge-danger">❌ No</span>' !!}
                                @else
                                    <span class="badge badge-danger">❌ No</span>
                                @endif
                            </td>
                            <td>
                                @if($hasCustomPrivileges)
                                    {!! $privileges->can_process_refunds ? '<span class="badge badge-success">✅ Yes</span>' : '<span class="badge badge-danger">❌ No</span>' !!}
                                @else
                                    <span class="badge badge-danger">❌ No</span>
                                @endif
                            </td>
                            <td>
                                @if($hasCustomPrivileges)
                                    {!! $privileges->can_apply_discounts ? '<span class="badge badge-success">✅ Yes</span>' : '<span class="badge badge-danger">❌ No</span>' !!}
                                @else
                                    <span class="badge badge-danger">❌ No</span>
                                @endif
                            </td>
                            <td>
                                @if($hasCustomPrivileges)
                                    {{ $privileges->updated_at->format('M j, Y') }}
                                @else
                                    <em>Never</em>
                                @endif
                            </td>
                            <td class="actions">
                                <a href="{{ route('admin.cashier-privileges.edit', $cashier) }}" class="btn btn-primary btn-sm">
                                    ⚙️ Configure
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="empty-state">
                <div class="empty-icon">👥</div>
                <h3>No Cashiers Found</h3>
                <p>There are no cashiers in your company yet. Create cashier users first to manage their privileges.</p>
                <a href="{{ route('admin.users.create') }}" class="btn btn-primary">
                    ➕ Add Cashier
                </a>
            </div>
        @endif
    </div>
</div>

<style>
/* Header Section */
.page-header {
    background: linear-gradient(135deg, #4f46e5, #6d28d9);
    padding: 30px 40px;
    border-radius: 14px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    color: white;
    box-shadow: 0 6px 25px rgba(0,0,0,0.15);
    margin-bottom: 35px;
}
.header-left {
    display: flex;
    align-items: center;
    gap: 15px;
}
.header-icon {
    background: rgba(255, 255, 255, 0.2);
    width: 55px;
    height: 55px;
    border-radius: 50%;
    display: flex;
    justify-content: center;
    align-items: center;
    font-size: 28px;
}
.page-header h2 {
    font-size: 1.8rem;
    font-weight: 700;
    margin-bottom: 5px;
}
.page-header p {
    font-size: 14px;
    opacity: 0.9;
    margin: 0;
}
.header-right .btn {
    background: white;
    color: #4f46e5;
    font-weight: 600;
    padding: 10px 18px;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(255,255,255,0.3);
    transition: 0.3s;
}
.header-right .btn:hover {
    background: #f4f4f9;
}

/* Stats Cards */
.stats-cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-bottom: 40px;
}

/* Table Container */
.table-section {
    margin-top: 20px;
}
.table-card {
    background: white;
    border-radius: 12px;
    padding: 25px;
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.08);
}
.custom-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
}
.custom-table thead {
    background: #f9fafb;
    text-transform: uppercase;
    font-size: 13px;
    color: #4b5563;
}
.custom-table th, .custom-table td {
    padding: 14px 18px;
    border-bottom: 1px solid #e5e7eb;
}
.custom-table th:first-child, .custom-table td:first-child {
    border-top-left-radius: 8px;
}
.custom-table th:last-child, .custom-table td:last-child {
    border-top-right-radius: 8px;
}
.custom-table tbody tr:hover {
    background: #f3f4f6;
    transition: 0.2s;
}
.actions .btn-sm {
    padding: 6px 12px;
    font-size: 13px;
}

/* Badges */
.badge {
    padding: 4px 8px;
    border-radius: 6px;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
.badge-success {
    background: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}
.badge-danger {
    background: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}
.badge-warning {
    background: #fff3cd;
    color: #856404;
    border: 1px solid #ffeaa7;
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: 60px 20px;
    background: white;
    border-radius: 12px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
}

/* Alerts */
.alert {
    padding: 15px 20px;
    border-radius: 8px;
    margin-bottom: 20px;
    border: 1px solid;
}
.alert-success {
    background: #d4edda;
    color: #155724;
    border-color: #c3e6cb;
}
.alert-danger {
    background: #f8d7da;
    color: #721c24;
    border-color: #f5c6cb;
}
code {
    background: #f8f9fa;
    padding: 2px 6px;
    border-radius: 4px;
    font-family: 'Courier New', monospace;
    font-size: 12px;
    color: #e83e8c;
}
</style>
@endsection
