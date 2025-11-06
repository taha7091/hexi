@extends('layouts.admin')

@section('title', 'Inventory Management')

@section('page-title', 'Inventory Management')

@section('styles')
<style>
    .filters {
        background: #fff;
        padding: 20px;
        border-radius: 12px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        margin-bottom: 25px;
        display: flex;
        gap: 15px;
        flex-wrap: wrap;
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
    .badge { padding: 5px 10px; border-radius: 6px; font-size: 12px; font-weight: 600; text-transform: uppercase; }
    .badge-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
    .badge-warning { background: #fff3cd; color: #856404; border: 1px solid #ffeeba; }
    .badge-danger { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
    .actions { display: flex; gap: 8px; flex-wrap: wrap; }
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

<!-- Stats Cards -->
<div class="stats-cards mb-4">
    <div class="stat-card">
        <div class="stat-number">{{ $totalProducts }}</div>
        <div class="stat-label">Total Products</div>
    </div>
    <div class="stat-card">
        <div class="stat-number">{{ $lowStockCount }}</div>
        <div class="stat-label">Low Stock Items</div>
    </div>
    <div class="stat-card">
        <div class="stat-number">{{ $outOfStockCount }}</div>
        <div class="stat-label">Out of Stock</div>
    </div>
    <div class="stat-card">
        <div class="stat-number">${{ number_format($totalValue, 0) }}</div>
        <div class="stat-label">Inventory Value</div>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title">Inventory Overview</h3>
        <div>
            <a href="{{ route('admin.inventory.adjustment-history') }}" class="btn btn-info">
                <i class="fas fa-history"></i> Adjustment History
            </a>
            <a href="{{ route('admin.inventory.report') }}" class="btn btn-success">
                <i class="fas fa-chart-bar"></i> Generate Report
            </a>
        </div>
    </div>

    <div class="card-body">

        <!-- Filters -->
        <div class="filters">
            <form method="GET" style="display: flex; gap: 15px; flex-wrap: wrap; align-items: center;">
                <select name="group_id" onchange="this.form.submit()">
                    <option value="">All Groups</option>
                    @foreach($groups as $group)
                        <option value="{{ $group->id }}" {{ request('group_id') == $group->id ? 'selected' : '' }}>
                            {{ $group->division->name }} - {{ $group->name }}
                        </option>
                    @endforeach
                </select>

                <select name="stock_status" onchange="this.form.submit()">
                    <option value="">All Stock Status</option>
                    <option value="normal" {{ request('stock_status')=='normal' ? 'selected':'' }}>Normal Stock</option>
                    <option value="low" {{ request('stock_status')=='low' ? 'selected':'' }}>Low Stock</option>
                    <option value="out" {{ request('stock_status')=='out' ? 'selected':'' }}>Out of Stock</option>
                </select>

                <input type="text" name="search" placeholder="Search products..." value="{{ request('search') }}">
                <button type="submit" class="btn btn-primary">Filter</button>
                @if(request()->hasAny(['group_id','stock_status','search']))
                    <a href="{{ route('admin.inventory.index') }}" class="btn btn-secondary">Clear</a>
                @endif
            </form>
        </div>

        <!-- Products Table -->
        <div class="table-container mt-3">
            @if($products->count() > 0)
                <table>
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>SKU</th>
                            <th>Group</th>
                            <th>Current Stock</th>
                            <th>Min Level</th>
                            <th>Status</th>
                            <th>Cost Price</th>
                            <th>Stock Value</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($products as $product)
                            <tr>
                                <td>
                                    @if($product->image_url)
                                        <img src="{{ $product->image_url }}" class="img-thumbnail me-2" style="width: 40px; height: 40px;">
                                    @endif
                                    <strong>{{ $product->name }}</strong>
                                    @if($product->description)
                                        <br><small class="text-muted">{{ Str::limit($product->description,50) }}</small>
                                    @endif
                                </td>
                                <td>{{ $product->sku }}</td>
                                <td>{{ $product->group->name }}</td>
                                <td>
                                    <span class="badge {{ $product->isOutOfStock() ? 'badge-danger' : ($product->isLowStock() ? 'badge-warning' : 'badge-success') }}">
                                        {{ $product->stock_quantity ?? 0 }}
                                    </span>
                                </td>
                                <td>{{ $product->min_stock_level ?? 'Not set' }}</td>
                                <td>
                                    @if($product->isOutOfStock())
                                        <span class="badge badge-danger">Out of Stock</span>
                                    @elseif($product->isLowStock())
                                        <span class="badge badge-warning">Low Stock</span>
                                    @else
                                        <span class="badge badge-success">In Stock</span>
                                    @endif
                                </td>
                                <td>${{ number_format($product->cost_price ?? 0, 2) }}</td>
                                <td>${{ number_format(($product->stock_quantity ?? 0) * ($product->cost_price ?? 0), 2) }}</td>
                                <td>
                                    <div class="actions">
                                        <a href="{{ route('admin.inventory.adjust-stock', $product) }}" class="btn btn-sm btn-primary">
                                            <i class="fas fa-edit"></i> Adjust
                                        </a>
                                        <a href="{{ route('admin.products.show', $product) }}" class="btn btn-sm btn-info">
                                            <i class="fas fa-eye"></i> View
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <!-- Pagination -->
                <div class="d-flex justify-content-center mt-3">
                    {{ $products->appends(request()->query())->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-boxes fa-3x text-muted mb-3"></i>
                    <h4 class="text-muted">No Products Found</h4>
                    <p class="text-muted">No products match your current filters.</p>
                    <a href="{{ route('admin.inventory.index') }}" class="btn btn-primary">
                        <i class="fas fa-refresh"></i> Clear Filters
                    </a>
                </div>
            @endif
        </div>

    </div>
</div>

@endsection
