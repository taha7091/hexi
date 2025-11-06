@extends('layouts.admin')

@section('title', 'Categories Management - ERP System')
@section('page-title', 'Categories Management')
@section('breadcrumb', 'Home > Admin > Categories')

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

    .filter-group select {
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
    <a href="{{ route('admin.categories.create') }}" class="btn btn-primary">
        <i style="margin-right: 8px;">＋</i> Create Category
    </a>
</div>

<div class="search-filters">
    <form method="GET" action="{{ route('admin.categories.index') }}">
        <div class="filter-row">
            <div class="filter-group">
                <label for="brand_id">Filter by Brand</label>
                <select id="brand_id" name="brand_id">
                    <option value="">All Brands</option>
                    @foreach($brands as $brand)
                        <option value="{{ $brand->id }}" {{ request('brand_id') == $brand->id ? 'selected' : '' }}>
                            {{ $brand->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="filter-group">
                <label for="status">Status</label>
                <select id="status" name="status">
                    <option value="">All Status</option>
                    <option value="1" {{ request('status') == '1' ? 'selected' : '' }}>Active</option>
                    <option value="0" {{ request('status') == '0' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>

            <button type="submit" class="btn btn-secondary">Filter</button>
        </div>
    </form>
</div>

<div class="table-container">
    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Brand</th>
                <th>Description</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($categories as $category)
                <tr>
                    <td>{{ $category->name }}</td>
                    <td>{{ $category->brand->name ?? 'N/A' }}</td>
                    <td>{{ Str::limit($category->description ?? 'No description.', 50) }}</td>
                    <td>
                        <span class="status-badge {{ $category->is_active ? 'status-active' : 'status-inactive' }}">
                            {{ $category->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                    <td>
                        <a href="{{ route('admin.categories.edit', $category->id) }}" class="btn btn-sm btn-primary">Edit</a>
                        <a href="{{ route('admin.categories.show', $category->id) }}" class="btn btn-sm btn-secondary">View</a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" style="text-align:center; color:#718096;">No categories found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
