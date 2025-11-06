@extends('layouts.admin')

@section('title', 'Products Management - ERP System')
@section('page-title', 'Products Management')
@section('breadcrumb', 'Home > Admin > Products')

@section('styles')
<style>
    /* ====== Table Container ====== */
    .table-container {
        background: white;
        border-radius: 15px;
        padding: 20px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        margin-top: 20px;
        overflow-x: auto;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        font-size: 14px;
        color: #2d3748;
    }

    thead {
        background: #667eea;
        color: white;
    }

    th, td {
        padding: 12px 15px;
        vertical-align: middle;
        border-bottom: 1px solid #e2e8f0;
        text-align: left;
    }

    tbody tr:hover {
        background: #f7f9fc;
    }

    .product-image {
        width: 50px;
        height: 50px;
        object-fit: cover;
        border-radius: 8px;
        border: 1px solid #ddd;
    }

    .no-image {
        width: 50px;
        height: 50px;
        border-radius: 8px;
        background: #f0f0f0;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #aaa;
        font-size: 0.9rem;
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
        justify-content: flex-end;
        align-items: center;
        margin-bottom: 15px;
    }

    .btn, .table-actions .btn {
        padding: 6px 12px;
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
        font-size: 14px;
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
        border: none;
        transition: all 0.2s ease;
    }

    .btn:hover, .table-actions .btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 2px 8px rgba(0,0,0,0.12);
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

    .table-actions {
        display: flex;
        gap: 0.3rem;
        flex-wrap: wrap;
    }

    .filters {
        background: white;
        padding: 20px;
        border-radius: 15px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        margin-bottom: 20px;
    }

    .filter-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
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

    .filter-group select, .filter-group input {
        width: 100%;
        padding: 10px;
        border-radius: 8px;
        border: 2px solid #e2e8f0;
        font-size: 14px;
    }

    .empty-state {
        text-align: center;
        padding: 2rem;
        color: #718096;
    }

    .empty-state i {
        font-size: 3rem;
        margin-bottom: 1rem;
        color: #cbd5e0;
        display: block;
    }
</style>
@endsection

@section('content')
<div class="header-actions">
    <a href="{{ route('admin.products.create') }}" class="btn btn-primary">
        <i class="fas fa-plus"></i> Add New Product
    </a>
</div>

<!-- Filters (keep the same as before) -->
<div class="filters">
    <div class="filter-row">
        <div class="filter-group">
            <label for="groupFilter">Group</label>
            <select id="groupFilter">
                <option value="">All Groups</option>
                @foreach($groups as $group)
                    <option value="{{ $group->id }}">{{ $group->division->name }} - {{ $group->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="filter-group">
            <label for="statusFilter">Status</label>
            <select id="statusFilter">
                <option value="">All Status</option>
                <option value="1">Active</option>
                <option value="0">Inactive</option>
            </select>
        </div>
        <div class="filter-group">
            <label for="stockFilter">Stock</label>
            <select id="stockFilter">
                <option value="">All Stock</option>
                <option value="low">Low Stock</option>
                <option value="out">Out of Stock</option>
            </select>
        </div>
        <div class="filter-group">
            <label for="searchInput">Search</label>
            <input type="text" id="searchInput" placeholder="Search products...">
        </div>
    </div>
</div>

<div class="table-container">
    @if($products->count() > 0)
        <table>
            <thead>
                <tr>
                    <th>Image</th>
                    <th>Name</th>
                    <th>SKU</th>
                    <th>Group</th>
                    <th>Price</th>
                    <th>Cost</th>
                    <th>Stock</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($products as $product)
                    <tr data-group-id="{{ $product->group_id }}" 
                        data-status="{{ $product->is_active ? '1' : '0' }}" 
                        data-stock-status="{{ $product->isOutOfStock() ? 'out' : ($product->isLowStock() ? 'low' : 'normal') }}">
                        <td>
                            @if($product->image_url)
                                <img src="{{ $product->image_url }}" class="product-image">
                            @else
                                <div class="no-image"><i class="fas fa-image"></i></div>
                            @endif
                        </td>
                        <td>
                            <strong>{{ $product->name }}</strong>
                            @if($product->description)
                                <br><small class="text-secondary">{{ Str::limit($product->description, 50) }}</small>
                            @endif
                        </td>
                        <td>{{ $product->sku }}</td>
                        <td>{{ $product->group->name }}</td>
                        <td>${{ number_format($product->price, 2) }}</td>
                        <td>${{ number_format($product->cost_price, 2) }}</td>
                        <td>
                            <span class="status-badge {{ $product->isLowStock() ? 'status-inactive' : ($product->isOutOfStock() ? 'status-inactive' : 'status-active') }}">
                                {{ $product->stock_quantity ?? 0 }}
                            </span>
                        </td>
                        <td>
                            <span class="status-badge {{ $product->is_active ? 'status-active' : 'status-inactive' }}">
                                {{ $product->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td>
                            <div class="table-actions">
                                <a href="{{ route('admin.products.show', $product) }}" class="btn btn-secondary">View</a>
                                <a href="{{ route('admin.products.edit', $product) }}" class="btn btn-primary">Edit</a>
                                <form action="{{ route('admin.products.destroy', $product) }}" method="POST" onsubmit="return confirm('Are you sure?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-secondary">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="pagination-container mt-3">
            {{ $products->links() }}
        </div>
    @else
        <div class="empty-state">
            <i class="fas fa-box"></i>
            <h5>No Products Found</h5>
            <p>Start by creating your first product.</p>
            <a href="{{ route('admin.products.create') }}" class="btn btn-primary">Create Product</a>
        </div>
    @endif
</div>
@endsection
