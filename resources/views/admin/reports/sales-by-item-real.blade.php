@extends('layouts.admin')

@section('title', 'Sales by Item Report')
@section('page-title', 'Sales by Item Report')

@section('styles')
<style>
    .report-container {
        background: var(--bg-primary);
        min-height: 100vh;
        padding: 2rem 0;
    }

    .report-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 3rem;
        border-radius: 25px 25px 0 0;
        position: relative;
        overflow: hidden;
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
    }

    .filters-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
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
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.15);
    }

    .btn-filter {
        padding: 0.75rem 1.5rem;
        background: linear-gradient(135deg, #667eea, #764ba2);
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
        box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
    }

    .report-stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1.5rem;
        padding: 2rem;
        background: linear-gradient(135deg, rgba(102, 126, 234, 0.05), rgba(118, 75, 162, 0.05));
    }

    .stat-card {
        background: var(--card-bg);
        padding: 1.5rem;
        border-radius: 15px;
        text-align: center;
        box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        transition: all 0.3s ease;
    }

    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 35px rgba(0,0,0,0.15);
    }

    .stat-value {
        font-size: 2rem;
        font-weight: 900;
        color: #667eea;
        margin-bottom: 0.5rem;
    }

    .stat-label {
        color: var(--text-secondary);
        font-size: 0.9rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    .report-table-container {
        padding: 2rem;
        overflow-x: auto;
    }

    .report-table {
        width: 100%;
        border-collapse: collapse;
        background: var(--card-bg);
        border-radius: 15px;
        overflow: hidden;
        box-shadow: 0 10px 25px rgba(0,0,0,0.1);
    }

    .report-table thead {
        background: linear-gradient(135deg, #667eea, #764ba2);
        color: white;
    }

    .report-table th {
        padding: 1.5rem 1rem;
        text-align: left;
        font-weight: 700;
        font-size: 0.9rem;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    .report-table tbody tr {
        transition: all 0.3s ease;
        border-bottom: 1px solid var(--border-color);
    }

    .report-table tbody tr:hover {
        background: rgba(102, 126, 234, 0.05);
        transform: scale(1.01);
    }

    .report-table td {
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
        background: linear-gradient(135deg, #667eea, #764ba2);
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

    .quantity-badge {
        background: linear-gradient(135deg, #43e97b, #38f9d7);
        color: white;
        padding: 0.5rem 1rem;
        border-radius: 20px;
        font-weight: 700;
        font-size: 0.9rem;
    }

    .revenue-amount {
        font-size: 1.1rem;
        font-weight: 700;
        color: #667eea;
    }

    .no-data {
        text-align: center;
        padding: 3rem;
        color: var(--text-secondary);
        font-size: 1.1rem;
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

    .pagination-info {
        color: var(--text-secondary);
        font-size: 0.9rem;
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
                        <i class="fas fa-chart-bar"></i>
                        Sales by Item Report
                    </h1>
                    <p class="report-subtitle">Detailed analysis of individual product performance and sales trends</p>
                </div>

                <div class="report-content">
                    <!-- Filters Section -->
                    <div class="filters-section">
                        <form id="reportFilters" method="GET">
                            <div class="filters-grid">
                                <div class="filter-group">
                                    <label class="filter-label">Date Range</label>
                                    <select class="filter-control" name="date_range" id="dateRange">
                                        <option value="today" {{ $dateRange == 'today' ? 'selected' : '' }}>Today</option>
                                        <option value="yesterday" {{ $dateRange == 'yesterday' ? 'selected' : '' }}>Yesterday</option>
                                        <option value="last_week" {{ $dateRange == 'last_week' ? 'selected' : '' }}>Last Week</option>
                                        <option value="last_month" {{ $dateRange == 'last_month' ? 'selected' : '' }}>Last Month</option>
                                        <option value="this_month" {{ $dateRange == 'this_month' ? 'selected' : '' }}>This Month</option>
                                        <option value="custom" {{ $dateRange == 'custom' ? 'selected' : '' }}>Custom Range</option>
                                    </select>
                                </div>

                                <div class="filter-group">
                                    <label class="filter-label">Category</label>
                                    <select class="filter-control" name="category_id">
                                        <option value="">All Categories</option>
                                        @foreach($categories as $category)
                                            <option value="{{ $category->id }}" {{ $categoryId == $category->id ? 'selected' : '' }}>
                                                {{ $category->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="filter-group">
                                    <label class="filter-label">Sort By</label>
                                    <select class="filter-control" name="sort_by">
                                        <option value="revenue_desc" {{ $sortBy == 'revenue_desc' ? 'selected' : '' }}>Revenue (High to Low)</option>
                                        <option value="revenue_asc" {{ $sortBy == 'revenue_asc' ? 'selected' : '' }}>Revenue (Low to High)</option>
                                        <option value="quantity_desc" {{ $sortBy == 'quantity_desc' ? 'selected' : '' }}>Quantity (High to Low)</option>
                                        <option value="quantity_asc" {{ $sortBy == 'quantity_asc' ? 'selected' : '' }}>Quantity (Low to High)</option>
                                        <option value="name_asc" {{ $sortBy == 'name_asc' ? 'selected' : '' }}>Product Name (A-Z)</option>
                                        <option value="name_desc" {{ $sortBy == 'name_desc' ? 'selected' : '' }}>Product Name (Z-A)</option>
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

                    <!-- Report Stats -->
                    <div class="report-stats">
                        <div class="stat-card">
                            <div class="stat-value">${{ number_format($totalRevenue, 2) }}</div>
                            <div class="stat-label">Total Revenue</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-value">{{ number_format($totalQuantity) }}</div>
                            <div class="stat-label">Items Sold</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-value">{{ $uniqueProducts }}</div>
                            <div class="stat-label">Unique Products</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-value">${{ number_format($avgOrderValue, 2) }}</div>
                            <div class="stat-label">Avg. Order Value</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-value">{{ $totalOrders }}</div>
                            <div class="stat-label">Total Orders</div>
                        </div>
                    </div>

                    <!-- Report Table -->
                    <div class="report-table-container">
                        @if($salesData->count() > 0)
                            <table class="report-table" id="salesTable">
                                <thead>
                                    <tr>
                                        <th>Product</th>
                                        <th>SKU</th>
                                        <th>Category</th>
                                        <th>Qty Sold</th>
                                        <th>Revenue</th>
                                        <th>Avg. Price</th>
                                        <th>Profit</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($salesData as $item)
                                        <tr>
                                            <td>
                                                <div class="product-info">
                                                    <div class="product-image">
                                                        {{ strtoupper(substr($item->name, 0, 2)) }}
                                                    </div>
                                                    <div class="product-details">
                                                        <h4>{{ $item->name }}</h4>
                                                        <p>{{ $item->category_name ?? 'No Category' }}</p>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>{{ $item->sku }}</td>
                                            <td>{{ $item->category_name ?? 'No Category' }}</td>
                                            <td><span class="quantity-badge">{{ $item->total_quantity }}</span></td>
                                            <td><span class="revenue-amount">${{ number_format($item->total_revenue, 2) }}</span></td>
                                            <td>${{ number_format($item->avg_price, 2) }}</td>
                                            <td>
                                                <span class="revenue-amount">
                                                    ${{ number_format($item->profit, 2) }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>

                            <!-- Pagination -->
                            <div class="d-flex justify-content-center mt-4">
                                {{ $salesData->appends(request()->query())->links() }}
                            </div>
                        @else
                            <div class="no-data">
                                <i class="fas fa-chart-bar" style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.3;"></i>
                                <h3>No Sales Data Found</h3>
                                <p>No sales data available for the selected date range and filters.</p>
                            </div>
                        @endif
                    </div>

                    <!-- Export Actions -->
                    <div class="export-actions">
                        <div class="pagination-info">
                            Showing {{ $salesData->firstItem() ?? 0 }}-{{ $salesData->lastItem() ?? 0 }} of {{ $salesData->total() }} products
                        </div>
                        <div class="export-buttons">
                            <a href="#" class="btn-export btn-excel">
                                <i class="fas fa-file-excel"></i>
                                Export Excel
                            </a>
                            <a href="#" class="btn-export btn-pdf">
                                <i class="fas fa-file-pdf"></i>
                                Export PDF
                            </a>
                            <a href="#" class="btn-export btn-print" onclick="window.print()">
                                <i class="fas fa-print"></i>
                                Print Report
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
    // Handle date range selection
    $('#dateRange').on('change', function() {
        const value = $(this).val();
        if (value === 'custom') {
            // You can add custom date picker here
            alert('Custom date range functionality can be added here');
        }
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

    // Animate stats on page load
    $('.stat-value').each(function() {
        const $this = $(this);
        const text = $this.text();
        const isPrice = text.includes('$');
        const countTo = parseInt(text.replace(/[^0-9]/g, ''));
        
        if (countTo > 0) {
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
        }
    });
});
</script>
@endsection