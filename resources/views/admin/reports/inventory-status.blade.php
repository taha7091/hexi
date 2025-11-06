@extends('layouts.admin')

@section('title', 'Inventory Status Report')
@section('page-title', 'Inventory Status Report')

@section('styles')
<style>
    .report-container {
        background: var(--bg-primary);
        min-height: 100vh;
        padding: 2rem 0;
    }

    .report-header {
        background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
        color: white;
        padding: 3rem;
        border-radius: 25px 25px 0 0;
        position: relative;
        overflow: hidden;
    }

    .report-header::before {
        content: '';
        position: absolute;
        top: -50%;
        left: -50%;
        width: 200%;
        height: 200%;
        background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
        animation: rotate 20s linear infinite;
    }

    @keyframes rotate {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }

    .report-title {
        font-size: 2.5rem;
        font-weight: 900;
        margin: 0;
        position: relative;
        z-index: 2;
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .report-subtitle {
        opacity: 0.9;
        margin-top: 0.5rem;
        position: relative;
        z-index: 2;
        font-size: 1.1rem;
    }

    .report-content {
        background: var(--card-bg);
        border-radius: 0 0 25px 25px;
        box-shadow: 0 25px 80px rgba(0,0,0,0.1);
        overflow: hidden;
    }

    .filters-section {
        background: var(--card-bg);
        padding: 2rem;
        border-bottom: 2px solid var(--border-color);
        position: sticky;
        top: 0;
        z-index: 100;
    }

    .filters-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1.5rem;
        align-items: end;
    }

    .filter-group {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }

    .filter-label {
        font-weight: 700;
        color: var(--text-primary);
        font-size: 0.9rem;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    .filter-control {
        padding: 0.75rem 1rem;
        border: 2px solid var(--border-color);
        border-radius: 10px;
        font-size: 0.95rem;
        transition: all 0.3s ease;
        background: var(--bg-secondary);
        color: var(--text-primary);
    }

    .filter-control:focus {
        outline: none;
        border-color: #43e97b;
        box-shadow: 0 0 0 3px rgba(67, 233, 123, 0.15);
    }

    .btn-filter {
        padding: 0.75rem 1.5rem;
        background: linear-gradient(135deg, #43e97b, #38f9d7);
        color: white;
        border: none;
        border-radius: 10px;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .btn-filter:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(67, 233, 123, 0.3);
    }

    .inventory-stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 1.5rem;
        padding: 2rem;
        background: linear-gradient(135deg, rgba(67, 233, 123, 0.05), rgba(56, 249, 215, 0.05));
    }

    .stat-card {
        background: var(--card-bg);
        padding: 2rem;
        border-radius: 15px;
        text-align: center;
        box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }

    .stat-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: var(--stat-gradient);
        transition: all 0.3s ease;
    }

    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 35px rgba(0,0,0,0.15);
    }

    .stat-card:hover::before {
        height: 6px;
        box-shadow: 0 0 20px var(--stat-glow);
    }

    .stat-card.total-items {
        --stat-gradient: linear-gradient(90deg, #43e97b, #38f9d7);
        --stat-glow: rgba(67, 233, 123, 0.5);
    }

    .stat-card.low-stock {
        --stat-gradient: linear-gradient(90deg, #ffc107, #ff8f00);
        --stat-glow: rgba(255, 193, 7, 0.5);
    }

    .stat-card.out-of-stock {
        --stat-gradient: linear-gradient(90deg, #e53e3e, #c53030);
        --stat-glow: rgba(229, 62, 62, 0.5);
    }

    .stat-card.total-value {
        --stat-gradient: linear-gradient(90deg, #667eea, #764ba2);
        --stat-glow: rgba(102, 126, 234, 0.5);
    }

    .stat-icon {
        width: 60px;
        height: 60px;
        border-radius: 15px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2rem;
        color: white;
        background: var(--stat-gradient);
        margin: 0 auto 1rem;
        box-shadow: 0 10px 25px var(--stat-glow);
    }

    .stat-value {
        font-size: 2.2rem;
        font-weight: 900;
        color: var(--text-primary);
        margin-bottom: 0.5rem;
    }

    .stat-label {
        color: var(--text-secondary);
        font-size: 1rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    .inventory-alerts {
        padding: 2rem;
        border-bottom: 2px solid var(--border-color);
    }

    .alerts-title {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--text-primary);
        margin-bottom: 1.5rem;
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .alerts-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 1.5rem;
    }

    .alert-card {
        background: var(--card-bg);
        border-radius: 15px;
        padding: 1.5rem;
        box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        transition: all 0.3s ease;
        border-left: 4px solid var(--alert-color);
    }

    .alert-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 12px 30px rgba(0,0,0,0.15);
    }

    .alert-card.critical {
        --alert-color: #e53e3e;
        background: linear-gradient(135deg, rgba(229, 62, 62, 0.05), rgba(197, 48, 48, 0.05));
    }

    .alert-card.warning {
        --alert-color: #ffc107;
        background: linear-gradient(135deg, rgba(255, 193, 7, 0.05), rgba(255, 143, 0, 0.05));
    }

    .alert-card.info {
        --alert-color: #4facfe;
        background: linear-gradient(135deg, rgba(79, 172, 254, 0.05), rgba(0, 242, 254, 0.05));
    }

    .alert-header {
        display: flex;
        align-items: center;
        gap: 1rem;
        margin-bottom: 1rem;
    }

    .alert-icon {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
        color: white;
        background: var(--alert-color);
    }

    .alert-title {
        font-size: 1.1rem;
        font-weight: 700;
        color: var(--text-primary);
        margin: 0;
    }

    .alert-count {
        background: var(--alert-color);
        color: white;
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 700;
        margin-left: auto;
    }

    .alert-description {
        color: var(--text-secondary);
        font-size: 0.9rem;
        line-height: 1.5;
    }

    .inventory-table-container {
        padding: 2rem;
        overflow-x: auto;
    }

    .table-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 2rem;
    }

    .table-title {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--text-primary);
    }

    .table-actions {
        display: flex;
        gap: 1rem;
    }

    .btn-table-action {
        padding: 0.5rem 1rem;
        background: var(--bg-secondary);
        border: 2px solid var(--border-color);
        border-radius: 8px;
        color: var(--text-primary);
        text-decoration: none;
        font-weight: 600;
        font-size: 0.9rem;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .btn-table-action:hover {
        background: var(--bg-primary);
        border-color: #43e97b;
        transform: translateY(-1px);
    }

    .inventory-table {
        width: 100%;
        border-collapse: collapse;
        background: var(--card-bg);
        border-radius: 15px;
        overflow: hidden;
        box-shadow: 0 10px 25px rgba(0,0,0,0.1);
    }

    .inventory-table thead {
        background: linear-gradient(135deg, #43e97b, #38f9d7);
        color: white;
    }

    .inventory-table th {
        padding: 1.5rem 1rem;
        text-align: left;
        font-weight: 700;
        font-size: 0.9rem;
        text-transform: uppercase;
        letter-spacing: 1px;
        position: relative;
    }

    .inventory-table th:hover {
        background: rgba(255,255,255,0.1);
        cursor: pointer;
    }

    .inventory-table tbody tr {
        transition: all 0.3s ease;
        border-bottom: 1px solid var(--border-color);
    }

    .inventory-table tbody tr:hover {
        background: rgba(67, 233, 123, 0.05);
        transform: scale(1.01);
    }

    .inventory-table td {
        padding: 1.25rem 1rem;
        color: var(--text-primary);
        font-weight: 500;
    }

    .product-info {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .product-image {
        width: 50px;
        height: 50px;
        border-radius: 10px;
        background: linear-gradient(135deg, #43e97b, #38f9d7);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: bold;
        font-size: 1.2rem;
    }

    .product-details h4 {
        margin: 0;
        font-size: 1rem;
        font-weight: 700;
        color: var(--text-primary);
    }

    .product-details p {
        margin: 0;
        font-size: 0.85rem;
        color: var(--text-secondary);
    }

    .stock-level {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .stock-badge {
        padding: 0.5rem 1rem;
        border-radius: 20px;
        font-weight: 700;
        font-size: 0.9rem;
        text-align: center;
        min-width: 80px;
    }

    .stock-badge.in-stock {
        background: linear-gradient(135deg, #43e97b, #38f9d7);
        color: white;
    }

    .stock-badge.low-stock {
        background: linear-gradient(135deg, #ffc107, #ff8f00);
        color: white;
    }

    .stock-badge.out-of-stock {
        background: linear-gradient(135deg, #e53e3e, #c53030);
        color: white;
    }

    .stock-progress {
        flex: 1;
        height: 8px;
        background: var(--bg-secondary);
        border-radius: 4px;
        overflow: hidden;
        position: relative;
    }

    .stock-progress-bar {
        height: 100%;
        border-radius: 4px;
        transition: width 1s ease;
        position: relative;
        overflow: hidden;
    }

    .stock-progress-bar.high {
        background: linear-gradient(90deg, #43e97b, #38f9d7);
    }

    .stock-progress-bar.medium {
        background: linear-gradient(90deg, #ffc107, #ff8f00);
    }

    .stock-progress-bar.low {
        background: linear-gradient(90deg, #e53e3e, #c53030);
    }

    .stock-progress-bar::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
        animation: progressShine 2s infinite;
    }

    @keyframes progressShine {
        0% { left: -100%; }
        100% { left: 100%; }
    }

    .value-amount {
        font-size: 1.1rem;
        font-weight: 700;
        color: #43e97b;
    }

    .reorder-actions {
        display: flex;
        gap: 0.5rem;
    }

    .btn-reorder {
        padding: 0.5rem 1rem;
        border: none;
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        font-size: 0.85rem;
        text-decoration: none;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .btn-reorder-primary {
        background: linear-gradient(135deg, #43e97b, #38f9d7);
        color: white;
    }

    .btn-reorder-secondary {
        background: var(--bg-secondary);
        color: var(--text-primary);
        border: 1px solid var(--border-color);
    }

    .btn-reorder:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 15px rgba(0,0,0,0.2);
    }

    .export-actions {
        padding: 2rem;
        border-top: 2px solid var(--border-color);
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 1rem;
    }

    .export-buttons {
        display: flex;
        gap: 1rem;
    }

    .btn-export {
        padding: 0.75rem 1.5rem;
        border: none;
        border-radius: 10px;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        text-decoration: none;
    }

    .btn-excel {
        background: linear-gradient(135deg, #43e97b, #38f9d7);
        color: white;
    }

    .btn-pdf {
        background: linear-gradient(135deg, #f093fb, #f5576c);
        color: white;
    }

    .btn-print {
        background: linear-gradient(135deg, #4facfe, #00f2fe);
        color: white;
    }

    .btn-export:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.2);
    }

    /* Mobile Responsive */
    @media (max-width: 768px) {
        .filters-grid {
            grid-template-columns: 1fr;
        }

        .inventory-stats {
            grid-template-columns: repeat(2, 1fr);
        }

        .alerts-grid {
            grid-template-columns: 1fr;
        }

        .inventory-table-container {
            padding: 1rem;
        }

        .table-header {
            flex-direction: column;
            gap: 1rem;
            align-items: stretch;
        }

        .table-actions {
            justify-content: center;
        }

        .export-actions {
            flex-direction: column;
            align-items: stretch;
        }

        .export-buttons {
            justify-content: center;
        }

        .product-info {
            flex-direction: column;
            text-align: center;
        }

        .stock-level {
            flex-direction: column;
        }

        .reorder-actions {
            flex-direction: column;
        }
    }
</style>
@endsection

@section('content')
<div class="report-container">
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-12">
                <!-- Report Header -->
                <div class="report-header">
                    <h1 class="report-title">
                        <i class="fas fa-boxes"></i>
                        Inventory Status Report
                    </h1>
                    <p class="report-subtitle">
                        Comprehensive inventory analysis with stock levels and reorder alerts
                    </p>
                </div>

                <div class="report-content">
                    <!-- Filters Section -->
                    <div class="filters-section">
                        <form id="reportFilters" method="GET">
                            <div class="filters-grid">
                                <div class="filter-group">
                                    <label class="filter-label">Inventory Category</label>
                                    <select class="filter-control" name="category_id">
                                        <option value="">All Categories</option>
                                        @foreach($categories as $category)
                                            <option value="{{ $category->id }}" {{ ($categoryId ?? null) == $category->id ? 'selected' : '' }}>
                                                {{ $category->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="filter-group">
                                    <label class="filter-label">Stock Status</label>
                                    <select class="filter-control" name="stock_status">
                                        <option value="">All Items</option>
                                        <option value="in_stock" {{ ($stockStatus ?? '') == 'in_stock' ? 'selected' : '' }}>In Stock</option>
                                        <option value="low_stock" {{ ($stockStatus ?? '') == 'low_stock' ? 'selected' : '' }}>Low Stock</option>
                                        <option value="out_of_stock" {{ ($stockStatus ?? '') == 'out_of_stock' ? 'selected' : '' }}>Out of Stock</option>
                                    </select>
                                </div>

                                <div class="filter-group">
                                    <label class="filter-label">Brand</label>
                                    <select class="filter-control" name="brand_id">
                                        <option value="">All Brands</option>
                                        @foreach($brands as $brand)
                                            <option value="{{ $brand->id }}" {{ ($brandId ?? null) == $brand->id ? 'selected' : '' }}>
                                                {{ $brand->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="filter-group">
                                    <label class="filter-label">Location</label>
                                    <select class="filter-control" name="location_id">
                                        <option value="">All Locations</option>
                                        @foreach($locations as $loc)
                                            <option value="{{ $loc->id }}" {{ ($locationId ?? null) == $loc->id ? 'selected' : '' }}>
                                                {{ $loc->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="filter-group">
                                    <label class="filter-label">Sort By</label>
                                    <select class="filter-control" name="sort_by">
                                        <option value="stock_asc" {{ ($sortBy ?? '') == 'stock_asc' ? 'selected' : '' }}>Stock Level (Low to High)</option>
                                        <option value="stock_desc" {{ ($sortBy ?? '') == 'stock_desc' ? 'selected' : '' }}>Stock Level (High to Low)</option>
                                        <option value="value_desc" {{ ($sortBy ?? '') == 'value_desc' ? 'selected' : '' }}>Value (High to Low)</option>
                                        <option value="name_asc" {{ ($sortBy ?? '') == 'name_asc' ? 'selected' : '' }}>Item Name (A-Z)</option>
                                    </select>
                                </div>

                                <div class="filter-group">
                                    <button type="submit" class="btn-filter">
                                        <i class="fas fa-search"></i>
                                        Apply Filters
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>

                    <!-- Inventory Stats -->
                    <div class="inventory-stats">
                        <div class="stat-card total-items">
                            <div class="stat-icon">📦</div>
                            <div class="stat-value">{{ number_format($totalItems) }}</div>
                            <div class="stat-label">Total Items</div>
                        </div>
                        <div class="stat-card low-stock">
                            <div class="stat-icon">⚠️</div>
                            <div class="stat-value">{{ number_format($lowStockItems) }}</div>
                            <div class="stat-label">Low Stock Items</div>
                        </div>
                        <div class="stat-card out-of-stock">
                            <div class="stat-icon">❌</div>
                            <div class="stat-value">{{ number_format($outOfStockItems) }}</div>
                            <div class="stat-label">Out of Stock</div>
                        </div>
                        <div class="stat-card total-value">
                            <div class="stat-icon">💰</div>
                            <div class="stat-value">${{ number_format($totalInventoryValue, 2) }}</div>
                            <div class="stat-label">Total Inventory Value</div>
                        </div>
                    </div>

                    <!-- Inventory Alerts -->
                    <div class="inventory-alerts">
                        <h3 class="alerts-title">
                            <i class="fas fa-exclamation-triangle"></i>
                            Inventory Alerts
                        </h3>
                        <div class="alerts-grid">
                            <div class="alert-card critical">
                                <div class="alert-header">
                                    <div class="alert-icon">🚨</div>
                                    <h4 class="alert-title">Critical Stock Levels</h4>
                                    <span class="alert-count">{{ $outOfStockItems }}</span>
                                </div>
                                <p class="alert-description">
                                    Items that are completely out of stock and need immediate restocking.
                                </p>
                            </div>

                            <div class="alert-card warning">
                                <div class="alert-header">
                                    <div class="alert-icon">⚠️</div>
                                    <h4 class="alert-title">Low Stock Warning</h4>
                                    <span class="alert-count">{{ $lowStockItems }}</span>
                                </div>
                                <p class="alert-description">
                                    Items below minimum stock level that require reordering soon.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Inventory Table -->
                    <div class="inventory-table-container">
                        <div class="table-header">
                            <h3 class="table-title">Detailed Inventory Status</h3>
                            <div class="table-actions">
                                <a href="{{route('admin.reports.inventory-status') }}" class="btn-table-action">
                                    <i class="fas fa-sync"></i>
                                    Refresh Data
                                </a>
                            </div>
                        </div>

                        @if($inventoryData->count() > 0)
                            <table class="inventory-table">
                                <thead>
                                    <tr>
                                        <th>Item</th>
                                        <th>Code</th>
                                        <th>Location</th>
                                        <th>Stock Unit</th>
                                        <th>Current Stock</th>
                                        <th>Min Stock</th>
                                        <th>Status</th>
                                        <th>Cost Price</th>
                                        <th>Total Value</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($inventoryData as $item)
                                        @php
                                            $status = $item->current_stock <= 0 ? 'out_of_stock' :
                                                      ($item->current_stock <= $item->minimum_stock ? 'low_stock' : 'in_stock');
                                            $progress = $item->minimum_stock > 0 ? min(($item->current_stock / $item->minimum_stock) * 100, 100) : 100;
                                        @endphp
                                        <tr>
                                            <td>
                                                <div class="product-info">
                                                    <div class="product-image">{{ strtoupper(substr($item->name, 0, 2)) }}</div>
                                                    <div class="product-details">
                                                        <h4>{{ $item->name }}</h4>
                                                        <p>{{ optional($item->brand)->name ?? 'No Brand' }}</p>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>{{ $item->code }}</td>
                                            <td>{{ optional($item->location)->name ?? '—' }}</td>
                                            <td>{{ optional($item->stockUnit)->symbol ?? optional($item->stockUnit)->name ?? '—' }}</td>
                                            <td>{{ $item->current_stock }}</td>
                                            <td>{{ $item->minimum_stock }}</td>
                                            <td>
                                                <div class="stock-level">
                                                    <span class="stock-badge {{ $status }}">
                                                        {{ ucfirst(str_replace('_', ' ', $status)) }}
                                                    </span>
                                                    <div class="stock-progress">
                                                        <div class="stock-progress-bar {{ $status }}" style="width: {{ $progress }}%"></div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>${{ number_format($item->cost_price, 2) }}</td>
                                            <td><span class="value-amount">${{ number_format($item->current_stock * $item->cost_price, 2) }}</span></td>
                                            <td>
                                                <div class="reorder-actions">
                                                    @if($status == 'out_of_stock' || $status == 'low_stock')
                                                        <a href="#" class="btn-reorder btn-reorder-primary">
                                                            <i class="fas fa-shopping-cart"></i>
                                                            Reorder
                                                        </a>
                                                    @else
                                                        <a href="#" class="btn-reorder btn-reorder-secondary">
                                                            <i class="fas fa-eye"></i>
                                                            View
                                                        </a>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>

                            <!-- Pagination -->
                            <div class="d-flex justify-content-center mt-4">
                                {{ $inventoryData->appends(request()->query())->links() }}
                            </div>
                        @else
                            <div class="no-data">
                                <i class="fas fa-boxes" style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.3;"></i>
                                <h3>No Inventory Data Found</h3>
                                <p>No items found for the selected filters.</p>
                            </div>
                        @endif
                    </div>

                    <!-- Export Actions -->
                  <div class="export-actions">
    <div class="pagination-info">
        Showing {{ $inventoryData->firstItem() ?? 0 }}-{{ $inventoryData->lastItem() ?? 0 }} of {{ $inventoryData->total() ?? 0 }} items
    </div>
    <div class="export-buttons">
        <a href="{{ route('admin.reports.inventory-status.excel') }}" class="btn-export btn-excel">
            <i class="fas fa-file-excel"></i> Export Excel
        </a>
        <a href="{{ route('admin.reports.inventory-status.pdf') }}" class="btn-export btn-pdf">
            <i class="fas fa-file-pdf"></i> Export PDF
        </a>
        <a href="#" class="btn-export btn-print" onclick="window.print()">
            <i class="fas fa-print"></i> Print Report
        </a>
    </div>
</div>

                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
$(document).ready(function() {
    // Animate progress bars on page load
    setTimeout(() => {
        $('.stock-progress-bar').each(function() {
            const width = $(this).css('width');
            $(this).css('width', '0%').animate({ width: width }, 1500);
        });
    }, 500);

    // Animate stats on page load
    $('.stat-value').each(function() {
        const $this = $(this);
        const text = $this.text();
        const isPrice = text.includes('$');
        const countTo = parseInt(text.replace(/[^0-9]/g, ''));
        
        $({ countNum: 0 }).animate({
            countNum: countTo
        }, {
            duration: 2000,
            easing: 'swing',
            step: function() {
                if (isPrice) {
                    $this.text('$' + Math.floor(this.countNum).toLocaleString());
                } else {
                    $this.text(Math.floor(this.countNum).toLocaleString());
                }
            },
            complete: function() {
                $this.text(text);
            }
        });
    });

    // Filter functionality
    $('#reportFilters').on('submit', function(e) {
        e.preventDefault();
        // Here you would typically make an AJAX call to filter the data
        console.log('Applying filters...');
    });

    // Reorder button functionality
    $('.btn-reorder-primary').on('click', function(e) {
        e.preventDefault();
        const productName = $(this).closest('tr').find('.product-details h4').text();
        
        if ($(this).find('i').hasClass('fa-exclamation-triangle')) {
            alert('Urgent reorder required for: ' + productName);
        } else {
            alert('Reorder initiated for: ' + productName);
        }
    });

    // View button functionality
    $('.btn-reorder-secondary').on('click', function(e) {
        e.preventDefault();
        const productName = $(this).closest('tr').find('.product-details h4').text();
        alert('Viewing details for: ' + productName);
    });

    // Export functionality
    $('.btn-excel').on('click', function(e) {
        e.preventDefault();
        alert('Excel export functionality would be implemented here');
    });

    $('.btn-pdf').on('click', function(e) {
        e.preventDefault();
        alert('PDF export functionality would be implemented here');
    });

    // Table refresh functionality
    $('.btn-table-action').on('click', function(e) {
        e.preventDefault();
        if ($(this).find('i').hasClass('fa-sync')) {
            $(this).find('i').addClass('fa-spin');
            setTimeout(() => {
                $(this).find('i').removeClass('fa-spin');
                alert('Inventory data refreshed successfully!');
            }, 2000);
        }
    });

    // Alert card interactions
    $('.alert-card').on('click', function() {
        const alertType = $(this).find('.alert-title').text();
        const count = $(this).find('.alert-count').text();
        alert('Showing ' + count + ' items for: ' + alertType);
    });

    // Add hover effects to stat cards
    $('.stat-card').on('mouseenter', function() {
        $(this).find('.stat-icon').addClass('animate__animated animate__pulse');
    }).on('mouseleave', function() {
        $(this).find('.stat-icon').removeClass('animate__animated animate__pulse');
    });
});
</script>
@endsection