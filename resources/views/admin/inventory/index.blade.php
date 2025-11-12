@extends('layouts.admin')

@section('title', 'Inventory Management')

@section('page-title', 'Inventory Management')

@section('styles')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
    .badge-info { background: #d1ecf1; color: #0c5460; border: 1px solid #bee5eb; }
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

    /* Dashboard Sections */
    .dashboard-section {
        background: white;
        border-radius: 12px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        padding: 20px;
        margin-bottom: 25px;
    }
    .dashboard-section h3 {
        color: #2c3e50;
        font-size: 18px;
        font-weight: 600;
        margin-bottom: 20px;
    }
    .chart-container {
        position: relative;
        height: 300px;
        width: 100%;
    }
    .purchase-trend {
        font-size: 24px;
        font-weight: bold;
        color: #2c3e50;
    }
    .purchase-trend .positive { color: #27ae60; }
    .purchase-trend .negative { color: #e74c3c; }
    .mini-stats {
        display: flex;
        justify-content: space-between;
        margin-top: 15px;
    }
    .mini-stat {
        text-align: center;
        flex: 1;
    }
    .mini-stat .value {
        font-size: 20px;
        font-weight: bold;
        color: #2c3e50;
    }
    .mini-stat .label {
        font-size: 12px;
        color: #7f8c8d;
    }

    /* Recent Purchases Table */
    .purchase-row {
        cursor: pointer;
        transition: background-color 0.2s;
    }
    .purchase-row:hover {
        background-color: #f8f9fa;
    }
    .purchase-status {
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        .stats-cards {
            grid-template-columns: repeat(2, 1fr);
        }
        .chart-container {
            height: 250px;
        }
        .mini-stats {
            flex-direction: column;
            gap: 10px;
        }
    }
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
    <div class="stat-card">
        <div class="stat-number">${{ number_format($purchaseStats['thisMonth'], 0) }}</div>
        <div class="stat-label">Purchase This Month</div>
    </div>
    <div class="stat-card">
        <div class="stat-number">{{ $topCategory->category_name }}</div>
        <div class="stat-label">Top Category</div>
    </div>
</div>

<!-- Purchase Trends and Category Performance -->
<div class="row mb-4">
    <div class="col-md-6">
        <div class="dashboard-section">
            <h3>Purchase Trends</h3>
            <div class="purchase-trend">
                ${{ number_format($purchaseStats['thisMonth'], 0) }}
                <span class="{{ $purchaseStats['growth'] >= 0 ? 'positive' : 'negative' }}">
                    ({{ $purchaseStats['growth'] >= 0 ? '+' : '' }}{{ number_format($purchaseStats['growth'], 1) }}%)
                </span>
            </div>
            <div class="mini-stats">
                <div class="mini-stat">
                    <div class="value">{{ $purchaseStats['orderCount'] }}</div>
                    <div class="label">Orders This Month</div>
                </div>
                <div class="mini-stat">
                    <div class="value">${{ number_format($purchaseStats['averageOrder'], 0) }}</div>
                    <div class="label">Average Order</div>
                </div>
            </div>
            <div class="chart-container mt-3">
                <canvas id="purchaseTrendChart"></canvas>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="dashboard-section">
            <h3>Category Performance</h3>
            <div class="chart-container">
                <canvas id="categoryChart"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Recent Purchase Orders -->
<div class="dashboard-section">
    <h3>Recent Purchase Orders</h3>
    @if($recentPurchases->count() > 0)
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Invoice #</th>
                        <th>Supplier</th>
                        <th>Date</th>
                        <th>Location</th>
                        <th>Amount</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recentPurchases as $purchase)
                        <tr class="purchase-row">
                            <td><strong>{{ $purchase->invoice_number }}</strong></td>
                            <td>{{ $purchase->supplier ? $purchase->supplier->name : 'N/A' }}</td>
                            <td>{{ $purchase->invoice_date ? $purchase->invoice_date->format('M d, Y') : 'N/A' }}</td>
                            <td>{{ $purchase->location ? $purchase->location->name : 'N/A' }}</td>
                            <td><strong>${{ number_format($purchase->total_amount, 2) }}</strong></td>
                            <td>
                                <span class="purchase-status badge-info">
                                    {{ ucfirst($purchase->status) }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="text-center py-4">
            <i class="fas fa-shopping-cart fa-2x text-muted mb-2"></i>
            <p class="text-muted">No recent purchase orders found.</p>
        </div>
    @endif
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

@push('scripts')
<script>
    // Purchase Trend Chart
    const purchaseTrendCtx = document.getElementById('purchaseTrendChart').getContext('2d');
    const purchaseTrendData = @json($purchaseTrendData);

    new Chart(purchaseTrendCtx, {
        type: 'line',
        data: {
            labels: purchaseTrendData.map(item => item.month),
            datasets: [{
                label: 'Monthly Purchases',
                data: purchaseTrendData.map(item => item.value),
                borderColor: '#3498db',
                backgroundColor: 'rgba(52, 152, 219, 0.1)',
                borderWidth: 3,
                fill: true,
                tension: 0.4,
                pointBackgroundColor: '#3498db',
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointRadius: 5,
                pointHoverRadius: 7
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return 'Purchases: $' + new Intl.NumberFormat().format(context.parsed.y.toFixed(0));
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '$' + new Intl.NumberFormat().format(value);
                        }
                    }
                }
            }
        }
    });

    // Category Performance Chart
    const categoryCtx = document.getElementById('categoryChart').getContext('2d');
    const categoryData = @json($categoryData);

    new Chart(categoryCtx, {
        type: 'doughnut',
        data: {
            labels: categoryData.labels,
            datasets: [{
                data: categoryData.values,
                backgroundColor: categoryData.colors,
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 15,
                        usePointStyle: true,
                        font: {
                            size: 12
                        }
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const value = '$' + new Intl.NumberFormat().format(context.parsed.toFixed(0));
                            const count = categoryData.productCounts[context.dataIndex];
                            return [
                                label + ': ' + value,
                                'Products: ' + count
                            ];
                        }
                    }
                }
            },
            onClick: function(evt, activeElements) {
                if (activeElements.length > 0) {
                    const index = activeElements[0].index;
                    const category = categoryData.labels[index];

                    // Create a form element to filter by category
                    const form = document.createElement('form');
                    form.method = 'GET';
                    form.action = '{{ route("admin.inventory.index") }}';

                    // Add category filter
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'category_filter';
                    input.value = category;
                    form.appendChild(input);

                    document.body.appendChild(form);
                    form.submit();
                }
            }
        }
    });
</script>
@endpush
