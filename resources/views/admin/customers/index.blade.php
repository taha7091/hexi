@extends('layouts.admin')

@section('title', 'Customers Management - ERP System')
@section('page-title', 'Customers')
@section('breadcrumb', 'Home > Admin > Customers')

@section('styles')
<style>
    .table-container {
        background: white;
        border-radius: 15px;
        padding: 20px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        margin-top: 20px;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        font-size: 14px;
    }

    thead {
        background: #667eea;
        color: white;
    }

    th, td {
        padding: 12px 15px;
        text-align: left;
        border-bottom: 1px solid #e2e8f0;
        vertical-align: middle;
    }

    tbody tr:hover {
        background: #f7f9fc;
    }

    .status-badge {
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .status-active {
        background: #c6f6d5;
        color: #22543d;
    }

    .status-inactive {
        background: #fed7d7;
        color: #742a2a;
    }

    .header-actions {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 15px;
    }

    .search-filters {
        background: white;
        padding: 20px;
        border-radius: 15px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        margin-bottom: 20px;
    }

    .filter-row {
        display: grid;
        grid-template-columns: 1fr 1fr auto;
        gap: 15px;
        align-items: end;
    }

    .filter-group {
        display: flex;
        flex-direction: column;
    }

    .filter-group label {
        font-weight: 600;
        color: #2d3748;
        margin-bottom: 5px;
    }

    .filter-group select,
    .filter-group input {
        width: 100%;
        padding: 10px;
        border-radius: 8px;
        border: 2px solid #e2e8f0;
        font-size: 14px;
    }

    .btn {
        padding: 8px 15px;
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
        font-size: 14px;
        text-decoration: none;
        border: none;
    }

    .btn-primary {
        background: linear-gradient(135deg,#667eea,#764ba2);
        color: #fff;
    }

    .btn-secondary {
        background: #f1f1f1;
        color: #2c3e50;
        border: 1px solid #ccc;
    }

    .btn-sm {
        padding: 5px 10px;
        font-size: 12px;
    }

    @media (max-width: 768px) {
        .filter-row {
            grid-template-columns: 1fr;
            gap: 10px;
        }
    }
</style>
@endsection

@section('content')
<div class="header-actions">
    <a href="{{ route('admin.customers.create') }}" class="btn btn-primary">
        <i style="margin-right: 8px;">＋</i> New Customer
    </a>
</div>

<div class="search-filters">
    <form method="GET" action="{{ route('admin.customers.index') }}">
        <div class="filter-row">
            <div class="filter-group">
                <label for="search">Search</label>
                <input type="text" id="search" name="search" placeholder="Name, Email, Phone, Code" value="{{ request('search') }}">
            </div>
            <div class="filter-group">
                <label for="group_id">Group</label>
                <select id="group_id" name="group_id">
                    <option value="">All Groups</option>
                    @foreach($groups as $group)
                        <option value="{{ $group->id }}" {{ request('group_id') == $group->id ? 'selected' : '' }}>
                            {{ $group->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <a href="{{ route('admin.customers.index') }}" class="btn btn-secondary">Clear</a>
                <button type="submit" class="btn btn-primary">Apply</button>
            </div>
        </div>
    </form>
</div>

<div class="table-container">
    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Group</th>
                <th>Contact</th>
                <th>Balance</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($customers as $customer)
                <tr>
                    <td>
                        <a href="{{ route('admin.customers.show', $customer) }}" class="font-weight-bold">{{ $customer->name }}</a>
                        <div class="small text-muted">Code: {{ $customer->code ?? '-' }}</div>
                    </td>
                    <td>{{ optional($customer->group)->name ?? '-' }}</td>
                    <td>
                        <div>{{ $customer->email ?? '-' }}</div>
                        <div class="small text-muted">{{ $customer->phone ?? '' }}</div>
                    </td>
                    <td><strong>${{ number_format($customer->balance, 2) }}</strong></td>
                    <td>
                        <span class="status-badge {{ $customer->is_active ? 'status-active' : 'status-inactive' }}">
                            {{ $customer->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                    <td>
                        <a href="{{ route('admin.customers.show', $customer) }}" class="btn btn-sm btn-secondary">View</a>
                        <a href="{{ route('admin.customers.edit', $customer) }}" class="btn btn-sm btn-primary">Edit</a>
                        <form action="{{ route('admin.customers.destroy', $customer) }}" method="POST" style="display:inline;" onsubmit="return confirm('Delete this customer?');">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-danger">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="text-align:center; color:#718096;">No customers found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="d-flex justify-content-center mt-4">
    {{ $customers->appends(request()->query())->links() }}
</div>
@endsection
