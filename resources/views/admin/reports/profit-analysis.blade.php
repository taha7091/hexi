@extends('layouts.admin')

@section('title', 'Profit Analysis Report')
@section('page-title', 'Profit Analysis Report')

@section('styles')
<style>
    .report-container {
        background: var(--bg-primary);
        min-height: 100vh;
        padding: 2rem 0;
    }

    .report-header {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
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
        border-color: #f093fb;
        box-shadow: 0 0 0 3px rgba(240, 147, 251, 0.15);
    }

    .btn-filter {
        padding: 0.75rem 1.5rem;
        background: linear-gradient(135deg, #f093fb, #f5576c);
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
        box-shadow: 0 8px 25px rgba(240, 147, 251, 0.3);
    }

    .profit-stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 1.5rem;
        padding: 2rem;
        background: linear-gradient(135deg, rgba(240, 147, 251, 0.05), rgba(245, 87, 108, 0.05));
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

    .stat-card.total-profit {
        --stat-gradient: linear-gradient(90deg, #f093fb, #f5576c);
        --stat-glow: rgba(240, 147, 251, 0.5);
    }

    .stat-card.profit-margin {
        --stat-gradient: linear-gradient(90deg, #43e97b, #38f9d7);
        --stat-glow: rgba(67, 233, 123, 0.5);
    }

    .stat-card.total-revenue {
        --stat-gradient: linear-gradient(90deg, #4facfe, #00f2fe);
        --stat-glow: rgba(79, 172, 254, 0.5);
    }

    .stat-card.total-cost {
        --stat-gradient: linear-gradient(90deg, #ffc107, #ff8f00);
        --stat-glow: rgba(255, 193, 7, 0.5);
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

    .stat-change {
        margin-top: 0.5rem;
        font-size: 0.9rem;
        font-weight: 600;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
    }

    .stat-change.positive {
        color: #43e97b;
    }

    .stat-change.negative {
        color: #e53e3e;
    }

    .profit-breakdown {
        padding: 2rem;
        border-bottom: 2px solid var(--border-color);
    }

    .breakdown-title {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--text-primary);
        margin-bottom: 2rem;
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .breakdown-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 2rem;
    }

    .breakdown-card {
        background: var(--card-bg);
        border-radius: 15px;
        padding: 2rem;
        box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
        border-left: 4px solid var(--breakdown-color);
    }

    .breakdown-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 35px rgba(0,0,0,0.15);
    }

    .breakdown-card.high-margin {
        --breakdown-color: #43e97b;
        background: linear-gradient(135deg, rgba(67, 233, 123, 0.05), rgba(56, 249, 215, 0.05));
    }

    .breakdown-card.medium-margin {
        --breakdown-color: #ffc107;
        background: linear-gradient(135deg, rgba(255, 193, 7, 0.05), rgba(255, 143, 0, 0.05));
    }

    .breakdown-card.low-margin {
        --breakdown-color: #e53e3e;
        background: linear-gradient(135deg, rgba(229, 62, 62, 0.05), rgba(197, 48, 48, 0.05));
    }

    .breakdown-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
    }

    .breakdown-category {
        font-size: 1.2rem;
        font-weight: 700;
        color: var(--text-primary);
    }

    .breakdown-percentage {
        background: var(--breakdown-color);
        color: white;
        padding: 0.5rem 1rem;
        border-radius: 20px;
        font-weight: 700;
        font-size: 0.9rem;
    }

    .breakdown-metrics {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 1rem;
        margin-bottom: 1.5rem;
    }

    .breakdown-metric {
        text-align: center;
        padding: 1rem;
        background: var(--bg-secondary);
        border-radius: 10px;
    }

    .breakdown-metric-value {
        font-size: 1.5rem;
        font-weight: 900;
        color: var(--breakdown-color);
        margin-bottom: 0.5rem;
    }

    .breakdown-metric-label {
        color: var(--text-secondary);
        font-size: 0.85rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    .breakdown-progress {
        margin-bottom: 1rem;
    }

    .progress-label {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 0.5rem;
        font-size: 0.9rem;
        font-weight: 600;
        color: var(--text-primary);
    }

    .progress-bar-container {
        height: 10px;
        background: var(--bg-secondary);
        border-radius: 5px;
        overflow: hidden;
        position: relative;
    }

    .progress-bar {
        height: 100%;
        background: var(--breakdown-color);
        border-radius: 5px;
        transition: width 1s ease;
        position: relative;
        overflow: hidden;
    }

    .progress-bar::before {
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

    .profit-table-container {
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

    .profit-table {
        width: 100%;
        border-collapse: collapse;
        background: var(--card-bg);
        border-radius: 15px;
        overflow: hidden;
        box-shadow: 0 10px 25px rgba(0,0,0,0.1);
    }

    .profit-table thead {
        background: linear-gradient(135deg, #f093fb, #f5576c);
        color: white;
    }

    .profit-table th {
        padding: 1.5rem 1rem;
        text-align: left;
        font-weight: 700;
        font-size: 0.9rem;
        text-transform: uppercase;
        letter-spacing: 1px;
        position: relative;
    }

    .profit-table th:hover {
        background: rgba(255,255,255,0.1);
        cursor: pointer;
    }

    .profit-table tbody tr {
        transition: all 0.3s ease;
        border-bottom: 1px solid var(--border-color);
    }

    .profit-table tbody tr:hover {
        background: rgba(240, 147, 251, 0.05);
        transform: scale(1.01);
    }

    .profit-table td {
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
        background: linear-gradient(135deg, #f093fb, #f5576c);
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

    .profit-margin-badge {
        padding: 0.5rem 1rem;
        border-radius: 20px;
        font-weight: 700;
        font-size: 0.9rem;
        text-align: center;
        min-width: 80px;
    }

    .profit-margin-badge.high {
        background: linear-gradient(135deg, #43e97b, #38f9d7);
        color: white;
    }

    .profit-margin-badge.medium {
        background: linear-gradient(135deg, #ffc107, #ff8f00);
        color: white;
    }

    .profit-margin-badge.low {
        background: linear-gradient(135deg, #e53e3e, #c53030);
        color: white;
    }

    .amount {
        font-size: 1.1rem;
        font-weight: 700;
    }

    .amount.positive {
        color: #43e97b;
    }

    .amount.negative {
        color: #e53e3e;
    }

    .amount.neutral {
        color: var(--text-primary);
    }

    .chart-section {
        padding: 2rem;
        border-top: 2px solid var(--border-color);
    }

    .chart-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 2rem;
    }

    .chart-container {
        background: var(--bg-secondary);
        border-radius: 15px;
        padding: 2rem;
        box-shadow: 0 10px 25px rgba(0,0,0,0.1);
    }

    .chart-title {
        font-size: 1.3rem;
        font-weight: 700;
        color: var(--text-primary);
        margin-bottom: 1.5rem;
        text-align: center;
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

        .profit-stats {
            grid-template-columns: repeat(2, 1fr);
        }

        .breakdown-grid {
            grid-template-columns: 1fr;
        }

        .chart-grid {
            grid-template-columns: 1fr;
        }

        .breakdown-metrics {
            grid-template-columns: 1fr;
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
                        <i class="fas fa-dollar-sign"></i>
                        Profit Analysis Report
                    </h1>
                    <p class="report-subtitle">Detailed profit margin analysis with cost breakdown and profitability insights</p>
                </div>

                <div class="report-content">
                    <!-- Filters Section -->
                    <div class="filters-section">
                        <form id="reportFilters" method="GET">
                            <div class="filters-grid">
                                <div class="filter-group">
                                    <label class="filter-label">Date Range</label>
                                    <select class="filter-control" name="date_range">
                                        <option value="today">Today</option>
                                        <option value="yesterday">Yesterday</option>
                                        <option value="last_week">Last Week</option>
                                        <option value="last_month" selected>Last Month</option>
                                        <option value="this_month">This Month</option>
                                        <option value="custom">Custom Range</option>
                                    </select>
                                </div>

                                <div class="filter-group">
                                    <label class="filter-label">Category</label>
                                    <select class="filter-control" name="category_id">
                                        <option value="">All Categories</option>
                                        <option value="1">Electronics</option>
                                        <option value="2">Clothing</option>
                                        <option value="3">Food & Beverages</option>
                                    </select>
                                </div>

                                <div class="filter-group">
                                    <label class="filter-label">Profit Margin</label>
                                    <select class="filter-control" name="margin_filter">
                                        <option value="">All Margins</option>
                                        <option value="high">High (>30%)</option>
                                        <option value="medium">Medium (10-30%)</option>
                                        <option value="low">Low (<10%)</option>
                                    </select>
                                </div>

                                <div class="filter-group">
                                    <label class="filter-label">Sort By</label>
                                    <select class="filter-control" name="sort_by">
                                        <option value="profit_desc">Profit (High to Low)</option>
                                        <option value="profit_asc">Profit (Low to High)</option>
                                        <option value="margin_desc">Margin % (High to Low)</option>
                                        <option value="revenue_desc">Revenue (High to Low)</option>
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

                    <!-- Profit Stats -->
                    <div class="profit-stats">
                        <div class="stat-card total-profit">
                            <div class="stat-icon">💰</div>
                            <div class="stat-value">$34,567</div>
                            <div class="stat-label">Total Profit</div>
                            <div class="stat-change positive">
                                <i class="fas fa-arrow-up"></i>
                                +12.5% vs last month
                            </div>
                        </div>
                        <div class="stat-card profit-margin">
                            <div class="stat-icon">📊</div>
                            <div class="stat-value">28.4%</div>
                            <div class="stat-label">Average Margin</div>
                            <div class="stat-change positive">
                                <i class="fas fa-arrow-up"></i>
                                +2.1% vs last month
                            </div>
                        </div>
                        <div class="stat-card total-revenue">
                            <div class="stat-icon">💵</div>
                            <div class="stat-value">$121,890</div>
                            <div class="stat-label">Total Revenue</div>
                            <div class="stat-change positive">
                                <i class="fas fa-arrow-up"></i>
                                +8.7% vs last month
                            </div>
                        </div>
                        <div class="stat-card total-cost">
                            <div class="stat-icon">📉</div>
                            <div class="stat-value">$87,323</div>
                            <div class="stat-label">Total Cost</div>
                            <div class="stat-change negative">
                                <i class="fas fa-arrow-up"></i>
                                +6.2% vs last month
                            </div>
                        </div>
                    </div>

                    <!-- Profit Breakdown -->
                    <div class="profit-breakdown">
                        <h3 class="breakdown-title">
                            <i class="fas fa-chart-pie"></i>
                            Profit Breakdown by Category
                        </h3>
                        <div class="breakdown-grid">
                            <div class="breakdown-card high-margin">
                                <div class="breakdown-header">
                                    <div class="breakdown-category">Electronics</div>
                                    <div class="breakdown-percentage">45.2%</div>
                                </div>
                                <div class="breakdown-metrics">
                                    <div class="breakdown-metric">
                                        <div class="breakdown-metric-value">$18,450</div>
                                        <div class="breakdown-metric-label">Profit</div>
                                    </div>
                                    <div class="breakdown-metric">
                                        <div class="breakdown-metric-value">$52,340</div>
                                        <div class="breakdown-metric-label">Revenue</div>
                                    </div>
                                    <div class="breakdown-metric">
                                        <div class="breakdown-metric-value">$33,890</div>
                                        <div class="breakdown-metric-label">Cost</div>
                                    </div>
                                    <div class="breakdown-metric">
                                        <div class="breakdown-metric-value">35.3%</div>
                                        <div class="breakdown-metric-label">Margin</div>
                                    </div>
                                </div>
                                <div class="breakdown-progress">
                                    <div class="progress-label">
                                        <span>Performance</span>
                                        <span>85%</span>
                                    </div>
                                    <div class="progress-bar-container">
                                        <div class="progress-bar" style="width: 85%"></div>
                                    </div>
                                </div>
                            </div>

                            <div class="breakdown-card medium-margin">
                                <div class="breakdown-header">
                                    <div class="breakdown-category">Clothing</div>
                                    <div class="breakdown-percentage">32.1%</div>
                                </div>
                                <div class="breakdown-metrics">
                                    <div class="breakdown-metric">
                                        <div class="breakdown-metric-value">$11,890</div>
                                        <div class="breakdown-metric-label">Profit</div>
                                    </div>
                                    <div class="breakdown-metric">
                                        <div class="breakdown-metric-value">$45,670</div>
                                        <div class="breakdown-metric-label">Revenue</div>
                                    </div>
                                    <div class="breakdown-metric">
                                        <div class="breakdown-metric-value">$33,780</div>
                                        <div class="breakdown-metric-label">Cost</div>
                                    </div>
                                    <div class="breakdown-metric">
                                        <div class="breakdown-metric-value">26.0%</div>
                                        <div class="breakdown-metric-label">Margin</div>
                                    </div>
                                </div>
                                <div class="breakdown-progress">
                                    <div class="progress-label">
                                        <span>Performance</span>
                                        <span>72%</span>
                                    </div>
                                    <div class="progress-bar-container">
                                        <div class="progress-bar" style="width: 72%"></div>
                                    </div>
                                </div>
                            </div>

                            <div class="breakdown-card low-margin">
                                <div class="breakdown-header">
                                    <div class="breakdown-category">Food & Beverages</div>
                                    <div class="breakdown-percentage">22.7%</div>
                                </div>
                                <div class="breakdown-metrics">
                                    <div class="breakdown-metric">
                                        <div class="breakdown-metric-value">$4,227</div>
                                        <div class="breakdown-metric-label">Profit</div>
                                    </div>
                                    <div class="breakdown-metric">
                                        <div class="breakdown-metric-value">$23,880</div>
                                        <div class="breakdown-metric-label">Revenue</div>
                                    </div>
                                    <div class="breakdown-metric">
                                        <div class="breakdown-metric-value">$19,653</div>
                                        <div class="breakdown-metric-label">Cost</div>
                                    </div>
                                    <div class="breakdown-metric">
                                        <div class="breakdown-metric-value">17.7%</div>
                                        <div class="breakdown-metric-label">Margin</div>
                                    </div>
                                </div>
                                <div class="breakdown-progress">
                                    <div class="progress-label">
                                        <span>Performance</span>
                                        <span>58%</span>
                                    </div>
                                    <div class="progress-bar-container">
                                        <div class="progress-bar" style="width: 58%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Profit Table -->
                    <div class="profit-table-container">
                        <div class="table-header">
                            <h3 class="table-title">Product Profitability Analysis</h3>
                        </div>

                        <table class="profit-table">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Revenue</th>
                                    <th>Cost</th>
                                    <th>Profit</th>
                                    <th>Margin %</th>
                                    <th>Units Sold</th>
                                    <th>Avg. Profit/Unit</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>
                                        <div class="product-info">
                                            <div class="product-image">📱</div>
                                            <div class="product-details">
                                                <h4>iPhone 14 Pro Max</h4>
                                                <p>Premium Smartphone</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td><span class="amount neutral">$49,500.00</span></td>
                                    <td><span class="amount neutral">$31,500.00</span></td>
                                    <td><span class="amount positive">$18,000.00</span></td>
                                    <td><span class="profit-margin-badge high">36.4%</span></td>
                                    <td>45</td>
                                    <td><span class="amount positive">$400.00</span></td>
                                </tr>
                                <tr>
                                    <td>
                                        <div class="product-info">
                                            <div class="product-image">👕</div>
                                            <div class="product-details">
                                                <h4>Premium Cotton T-Shirt</h4>
                                                <p>Casual Wear</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td><span class="amount neutral">$3,840.00</span></td>
                                    <td><span class="amount neutral">$2,560.00</span></td>
                                    <td><span class="amount positive">$1,280.00</span></td>
                                    <td><span class="profit-margin-badge high">33.3%</span></td>
                                    <td>128</td>
                                    <td><span class="amount positive">$10.00</span></td>
                                </tr>
                                <tr>
                                    <td>
                                        <div class="product-info">
                                            <div class="product-image">☕</div>
                                            <div class="product-details">
                                                <h4>Premium Coffee Beans</h4>
                                                <p>Arabica Blend</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td><span class="amount neutral">$2,225.00</span></td>
                                    <td><span class="amount neutral">$1,780.00</span></td>
                                    <td><span class="amount positive">$445.00</span></td>
                                    <td><span class="profit-margin-badge medium">20.0%</span></td>
                                    <td>89</td>
                                    <td><span class="amount positive">$5.00</span></td>
                                </tr>
                                <tr>
                                    <td>
                                        <div class="product-info">
                                            <div class="product-image">💻</div>
                                            <div class="product-details">
                                                <h4>MacBook Pro 16"</h4>
                                                <p>Professional Laptop</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td><span class="amount neutral">$29,988.00</span></td>
                                    <td><span class="amount neutral">$21,588.00</span></td>
                                    <td><span class="amount positive">$8,400.00</span></td>
                                    <td><span class="profit-margin-badge medium">28.0%</span></td>
                                    <td>12</td>
                                    <td><span class="amount positive">$700.00</span></td>
                                </tr>
                                <tr>
                                    <td>
                                        <div class="product-info">
                                            <div class="product-image">👟</div>
                                            <div class="product-details">
                                                <h4>Running Shoes</h4>
                                                <p>Athletic Footwear</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td><span class="amount neutral">$8,040.00</span></td>
                                    <td><span class="amount neutral">$6,432.00</span></td>
                                    <td><span class="amount positive">$1,608.00</span></td>
                                    <td><span class="profit-margin-badge medium">20.0%</span></td>
                                    <td>67</td>
                                    <td><span class="amount positive">$24.00</span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Charts Section -->
                    <div class="chart-section">
                        <div class="chart-grid">
                            <div class="chart-container">
                                <h3 class="chart-title">Profit Trend Over Time</h3>
                                <canvas id="profitTrendChart" width="400" height="300"></canvas>
                            </div>
                            <div class="chart-container">
                                <h3 class="chart-title">Profit Margin Distribution</h3>
                                <canvas id="marginDistributionChart" width="400" height="300"></canvas>
                            </div>
                        </div>
                    </div>

                    <!-- Export Actions -->
                    <div class="export-actions">
                        <div class="pagination-info">
                            Showing detailed profit analysis for all products
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
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
$(document).ready(function() {
    // Initialize Profit Trend Chart
    const profitTrendCtx = document.getElementById('profitTrendChart').getContext('2d');
    const profitTrendChart = new Chart(profitTrendCtx, {
        type: 'line',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
            datasets: [
                {
                    label: 'Profit',
                    data: [15000, 18000, 22000, 25000, 28000, 34567],
                    borderColor: 'rgb(240, 147, 251)',
                    backgroundColor: 'rgba(240, 147, 251, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4
                },
                {
                    label: 'Revenue',
                    data: [45000, 52000, 68000, 78000, 95000, 121890],
                    borderColor: 'rgb(79, 172, 254)',
                    backgroundColor: 'rgba(79, 172, 254, 0.1)',
                    borderWidth: 3,
                    fill: false,
                    tension: 0.4
                }
            ]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 20,
                        usePointStyle: true
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '$' + value.toLocaleString();
                        }
                    }
                }
            }
        }
    });

    // Initialize Margin Distribution Chart
    const marginCtx = document.getElementById('marginDistributionChart').getContext('2d');
    const marginChart = new Chart(marginCtx, {
        type: 'doughnut',
        data: {
            labels: ['High Margin (>30%)', 'Medium Margin (10-30%)', 'Low Margin (<10%)'],
            datasets: [{
                data: [45, 35, 20],
                backgroundColor: [
                    'rgba(67, 233, 123, 0.8)',
                    'rgba(255, 193, 7, 0.8)',
                    'rgba(229, 62, 62, 0.8)'
                ],
                borderColor: [
                    'rgb(67, 233, 123)',
                    'rgb(255, 193, 7)',
                    'rgb(229, 62, 62)'
                ],
                borderWidth: 3
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 20,
                        usePointStyle: true
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.label + ': ' + context.parsed + '%';
                        }
                    }
                }
            }
        }
    });

    // Animate progress bars on page load
    setTimeout(() => {
        $('.progress-bar').each(function() {
            const width = $(this).css('width');
            $(this).css('width', '0%').animate({ width: width }, 1500);
        });
    }, 500);

    // Animate stats on page load
    $('.stat-value, .breakdown-metric-value').each(function() {
        const $this = $(this);
        const text = $this.text();
        const isPrice = text.includes('$');
        const isPercentage = text.includes('%');
        const countTo = parseFloat(text.replace(/[^0-9.]/g, ''));
        
        $({ countNum: 0 }).animate({
            countNum: countTo
        }, {
            duration: 2000,
            easing: 'swing',
            step: function() {
                let displayValue;
                if (isPrice) {
                    displayValue = '$' + Math.floor(this.countNum).toLocaleString();
                } else if (isPercentage) {
                    displayValue = this.countNum.toFixed(1) + '%';
                } else {
                    displayValue = Math.floor(this.countNum).toLocaleString();
                }
                $this.text(displayValue);
            },
            complete: function() {
                $this.text(text);
            }
        });
    });

    // Filter functionality
    $('#reportFilters').on('submit', function(e) {
        e.preventDefault();
        console.log('Applying profit analysis filters...');
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

    // Add hover effects to breakdown cards
    $('.breakdown-card').on('mouseenter', function() {
        $(this).find('.breakdown-percentage').addClass('animate__animated animate__pulse');
    }).on('mouseleave', function() {
        $(this).find('.breakdown-percentage').removeClass('animate__animated animate__pulse');
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