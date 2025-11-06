@extends('layouts.admin')

@section('title', 'Sales by Category Report')
@section('page-title', 'Sales by Category Report')

@section('styles')
<style>
    .report-container {
        background: var(--bg-primary);
        min-height: 100vh;
        padding: 2rem 0;
    }

    .report-header {
        background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
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
        border-color: #4facfe;
        box-shadow: 0 0 0 3px rgba(79, 172, 254, 0.15);
    }

    .btn-filter {
        padding: 0.75rem 1.5rem;
        background: linear-gradient(135deg, #4facfe, #00f2fe);
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
        box-shadow: 0 8px 25px rgba(79, 172, 254, 0.3);
    }

    .report-content {
        background: var(--card-bg);
        border-radius: 0 0 25px 25px;
        box-shadow: 0 25px 80px rgba(0,0,0,0.1);
        overflow: hidden;
    }

    .report-stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1.5rem;
        padding: 2rem;
        background: linear-gradient(135deg, rgba(79, 172, 254, 0.05), rgba(0, 242, 254, 0.05));
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
        color: #4facfe;
        margin-bottom: 0.5rem;
    }

    .stat-label {
        color: var(--text-secondary);
        font-size: 0.9rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    .category-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
        gap: 2rem;
        padding: 2rem;
    }

    .category-card {
        background: var(--card-bg);
        border-radius: 20px;
        padding: 2rem;
        box-shadow: 0 15px 35px rgba(0,0,0,0.1);
        transition: all 0.4s cubic-bezier(0.25, 0.8, 0.25, 1);
        position: relative;
        overflow: hidden;
        border: 2px solid transparent;
    }

    .category-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: var(--category-gradient);
        transition: all 0.3s ease;
    }

    .category-card:hover {
        transform: translateY(-10px) scale(1.02);
        box-shadow: 0 25px 50px rgba(0,0,0,0.15);
        border-color: var(--category-color);
    }

    .category-card:hover::before {
        height: 6px;
        box-shadow: 0 0 20px var(--category-glow);
    }

    .category-card.electronics {
        --category-gradient: linear-gradient(90deg, #667eea, #764ba2);
        --category-color: #667eea;
        --category-glow: rgba(102, 126, 234, 0.5);
    }

    .category-card.clothing {
        --category-gradient: linear-gradient(90deg, #f093fb, #f5576c);
        --category-color: #f093fb;
        --category-glow: rgba(240, 147, 251, 0.5);
    }

    .category-card.food {
        --category-gradient: linear-gradient(90deg, #43e97b, #38f9d7);
        --category-color: #43e97b;
        --category-glow: rgba(67, 233, 123, 0.5);
    }

    .category-card.home {
        --category-gradient: linear-gradient(90deg, #ffc107, #ff8f00);
        --category-color: #ffc107;
        --category-glow: rgba(255, 193, 7, 0.5);
    }

    .category-header {
        display: flex;
        align-items: center;
        gap: 1.5rem;
        margin-bottom: 2rem;
    }

    .category-icon {
        width: 80px;
        height: 80px;
        border-radius: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2.5rem;
        color: white;
        background: var(--category-gradient);
        box-shadow: 0 15px 35px var(--category-glow);
        position: relative;
        overflow: hidden;
        transition: all 0.3s ease;
    }

    .category-icon::before {
        content: '';
        position: absolute;
        top: -50%;
        left: -50%;
        width: 200%;
        height: 200%;
        background: linear-gradient(45deg, transparent, rgba(255,255,255,0.2), transparent);
        transform: rotate(-45deg);
        transition: all 0.3s ease;
    }

    .category-card:hover .category-icon {
        transform: scale(1.1) rotate(5deg);
    }

    .category-card:hover .category-icon::before {
        animation: shine 0.6s ease;
    }

    @keyframes shine {
        0% { transform: translateX(-100%) translateY(-100%) rotate(-45deg); }
        100% { transform: translateX(100%) translateY(100%) rotate(-45deg); }
    }

    .category-info h3 {
        font-size: 1.8rem;
        font-weight: 800;
        color: var(--text-primary);
        margin: 0 0 0.5rem 0;
    }

    .category-info p {
        color: var(--text-secondary);
        font-size: 1rem;
        margin: 0;
    }

    .category-metrics {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 1.5rem;
        margin-bottom: 2rem;
    }

    .metric {
        text-align: center;
        padding: 1rem;
        background: var(--bg-secondary);
        border-radius: 15px;
        transition: all 0.3s ease;
    }

    .metric:hover {
        background: var(--bg-primary);
        transform: translateY(-2px);
    }

    .metric-value {
        font-size: 1.8rem;
        font-weight: 900;
        color: var(--category-color);
        margin-bottom: 0.5rem;
    }

    .metric-label {
        color: var(--text-secondary);
        font-size: 0.85rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    .category-progress {
        margin-bottom: 1.5rem;
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
        height: 12px;
        background: var(--bg-secondary);
        border-radius: 6px;
        overflow: hidden;
        position: relative;
    }

    .progress-bar {
        height: 100%;
        background: var(--category-gradient);
        border-radius: 6px;
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

    .category-actions {
        display: flex;
        gap: 1rem;
    }

    .btn-category {
        flex: 1;
        padding: 0.75rem 1rem;
        border-radius: 10px;
        font-weight: 700;
        text-decoration: none;
        border: none;
        cursor: pointer;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        font-size: 0.9rem;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    .btn-primary-category {
        background: var(--category-gradient);
        color: white;
        box-shadow: 0 8px 25px var(--category-glow);
    }

    .btn-primary-category:hover {
        transform: translateY(-2px);
        box-shadow: 0 12px 35px var(--category-glow);
    }

    .btn-secondary-category {
        background: var(--bg-secondary);
        color: var(--text-primary);
        border: 2px solid var(--border-color);
    }

    .btn-secondary-category:hover {
        background: var(--bg-primary);
        border-color: var(--category-color);
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

        .category-grid {
            grid-template-columns: 1fr;
            padding: 1rem;
        }

        .chart-grid {
            grid-template-columns: 1fr;
        }

        .category-metrics {
            grid-template-columns: 1fr;
        }

        .category-actions {
            flex-direction: column;
        }

        .export-actions {
            flex-direction: column;
            align-items: stretch;
        }

        .export-buttons {
            justify-content: center;
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
                        <i class="fas fa-chart-pie"></i>
                        Sales by Category Report
                    </h1>
                    <p class="report-subtitle">Category-wise sales performance analysis with hierarchical breakdown</p>
                </div>

                <div class="report-content">
                    <!-- Filters Section -->
                    <div class="filters-section">
                        <form id="reportFilters" method="GET">
                            <div class="filters-grid">
                                <div class="filter-group">
                                    <label class="filter-label">Date Range</label>
                                    <select class="filter-control" name="date_range" id="dateRange">
                                        <option value="today">Today</option>
                                        <option value="yesterday">Yesterday</option>
                                        <option value="last_week">Last Week</option>
                                        <option value="last_month" selected>Last Month</option>
                                        <option value="this_month">This Month</option>
                                        <option value="custom">Custom Range</option>
                                    </select>
                                </div>

                                <div class="filter-group">
                                    <label class="filter-label">Brand</label>
                                    <select class="filter-control" name="brand_id">
                                        <option value="">All Brands</option>
                                        <option value="1">Apple</option>
                                        <option value="2">Samsung</option>
                                        <option value="3">Nike</option>
                                    </select>
                                </div>

                                <div class="filter-group">
                                    <label class="filter-label">View Type</label>
                                    <select class="filter-control" name="view_type">
                                        <option value="category">By Category</option>
                                        <option value="brand">By Brand</option>
                                        <option value="division">By Division</option>
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
                            <div class="stat-value">$89,450</div>
                            <div class="stat-label">Total Revenue</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-value">8</div>
                            <div class="stat-label">Active Categories</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-value">2,847</div>
                            <div class="stat-label">Items Sold</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-value">$31.42</div>
                            <div class="stat-label">Avg. Item Price</div>
                        </div>
                    </div>

                    <!-- Category Grid -->
                    <div class="category-grid">
                        <!-- Electronics Category -->
                        <div class="category-card electronics">
                            <div class="category-header">
                                <div class="category-icon">📱</div>
                                <div class="category-info">
                                    <h3>Electronics</h3>
                                    <p>Smartphones, Laptops, Accessories</p>
                                </div>
                            </div>
                            
                            <div class="category-metrics">
                                <div class="metric">
                                    <div class="metric-value">$45,230</div>
                                    <div class="metric-label">Revenue</div>
                                </div>
                                <div class="metric">
                                    <div class="metric-value">287</div>
                                    <div class="metric-label">Units Sold</div>
                                </div>
                                <div class="metric">
                                    <div class="metric-value">50.6%</div>
                                    <div class="metric-label">Market Share</div>
                                </div>
                                <div class="metric">
                                    <div class="metric-value">+15.2%</div>
                                    <div class="metric-label">Growth</div>
                                </div>
                            </div>

                            <div class="category-progress">
                                <div class="progress-label">
                                    <span>Performance</span>
                                    <span>85%</span>
                                </div>
                                <div class="progress-bar-container">
                                    <div class="progress-bar" style="width: 85%"></div>
                                </div>
                            </div>

                            <div class="category-actions">
                                <a href="#" class="btn-category btn-primary-category">
                                    <i class="fas fa-chart-line"></i>
                                    View Details
                                </a>
                                <a href="#" class="btn-category btn-secondary-category">
                                    <i class="fas fa-download"></i>
                                    Export
                                </a>
                            </div>
                        </div>

                        <!-- Clothing Category -->
                        <div class="category-card clothing">
                            <div class="category-header">
                                <div class="category-icon">👕</div>
                                <div class="category-info">
                                    <h3>Clothing</h3>
                                    <p>Apparel, Footwear, Accessories</p>
                                </div>
                            </div>
                            
                            <div class="category-metrics">
                                <div class="metric">
                                    <div class="metric-value">$28,940</div>
                                    <div class="metric-label">Revenue</div>
                                </div>
                                <div class="metric">
                                    <div class="metric-value">1,456</div>
                                    <div class="metric-label">Units Sold</div>
                                </div>
                                <div class="metric">
                                    <div class="metric-value">32.4%</div>
                                    <div class="metric-label">Market Share</div>
                                </div>
                                <div class="metric">
                                    <div class="metric-value">+8.7%</div>
                                    <div class="metric-label">Growth</div>
                                </div>
                            </div>

                            <div class="category-progress">
                                <div class="progress-label">
                                    <span>Performance</span>
                                    <span>72%</span>
                                </div>
                                <div class="progress-bar-container">
                                    <div class="progress-bar" style="width: 72%"></div>
                                </div>
                            </div>

                            <div class="category-actions">
                                <a href="#" class="btn-category btn-primary-category">
                                    <i class="fas fa-chart-line"></i>
                                    View Details
                                </a>
                                <a href="#" class="btn-category btn-secondary-category">
                                    <i class="fas fa-download"></i>
                                    Export
                                </a>
                            </div>
                        </div>

                        <!-- Food & Beverages Category -->
                        <div class="category-card food">
                            <div class="category-header">
                                <div class="category-icon">🍕</div>
                                <div class="category-info">
                                    <h3>Food & Beverages</h3>
                                    <p>Snacks, Drinks, Fresh Food</p>
                                </div>
                            </div>
                            
                            <div class="category-metrics">
                                <div class="metric">
                                    <div class="metric-value">$12,680</div>
                                    <div class="metric-label">Revenue</div>
                                </div>
                                <div class="metric">
                                    <div class="metric-value">892</div>
                                    <div class="metric-label">Units Sold</div>
                                </div>
                                <div class="metric">
                                    <div class="metric-value">14.2%</div>
                                    <div class="metric-label">Market Share</div>
                                </div>
                                <div class="metric">
                                    <div class="metric-value">-2.1%</div>
                                    <div class="metric-label">Growth</div>
                                </div>
                            </div>

                            <div class="category-progress">
                                <div class="progress-label">
                                    <span>Performance</span>
                                    <span>58%</span>
                                </div>
                                <div class="progress-bar-container">
                                    <div class="progress-bar" style="width: 58%"></div>
                                </div>
                            </div>

                            <div class="category-actions">
                                <a href="#" class="btn-category btn-primary-category">
                                    <i class="fas fa-chart-line"></i>
                                    View Details
                                </a>
                                <a href="#" class="btn-category btn-secondary-category">
                                    <i class="fas fa-download"></i>
                                    Export
                                </a>
                            </div>
                        </div>

                        <!-- Home & Garden Category -->
                        <div class="category-card home">
                            <div class="category-header">
                                <div class="category-icon">🏠</div>
                                <div class="category-info">
                                    <h3>Home & Garden</h3>
                                    <p>Furniture, Decor, Garden Tools</p>
                                </div>
                            </div>
                            
                            <div class="category-metrics">
                                <div class="metric">
                                    <div class="metric-value">$2,600</div>
                                    <div class="metric-label">Revenue</div>
                                </div>
                                <div class="metric">
                                    <div class="metric-value">212</div>
                                    <div class="metric-label">Units Sold</div>
                                </div>
                                <div class="metric">
                                    <div class="metric-value">2.8%</div>
                                    <div class="metric-label">Market Share</div>
                                </div>
                                <div class="metric">
                                    <div class="metric-value">+5.3%</div>
                                    <div class="metric-label">Growth</div>
                                </div>
                            </div>

                            <div class="category-progress">
                                <div class="progress-label">
                                    <span>Performance</span>
                                    <span>35%</span>
                                </div>
                                <div class="progress-bar-container">
                                    <div class="progress-bar" style="width: 35%"></div>
                                </div>
                            </div>

                            <div class="category-actions">
                                <a href="#" class="btn-category btn-primary-category">
                                    <i class="fas fa-chart-line"></i>
                                    View Details
                                </a>
                                <a href="#" class="btn-category btn-secondary-category">
                                    <i class="fas fa-download"></i>
                                    Export
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Charts Section -->
                    <div class="chart-section">
                        <div class="chart-grid">
                            <div class="chart-container">
                                <h3 class="chart-title">Revenue Distribution</h3>
                                <canvas id="revenueChart" width="400" height="300"></canvas>
                            </div>
                            <div class="chart-container">
                                <h3 class="chart-title">Category Growth Trends</h3>
                                <canvas id="growthChart" width="400" height="300"></canvas>
                            </div>
                        </div>
                    </div>

                    <!-- Export Actions -->
                    <div class="export-actions">
                        <div class="pagination-info">
                            Showing 4 categories with complete data
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
    // Initialize Revenue Distribution Chart (Pie Chart)
    const revenueCtx = document.getElementById('revenueChart').getContext('2d');
    const revenueChart = new Chart(revenueCtx, {
        type: 'doughnut',
        data: {
            labels: ['Electronics', 'Clothing', 'Food & Beverages', 'Home & Garden'],
            datasets: [{
                data: [45230, 28940, 12680, 2600],
                backgroundColor: [
                    'rgba(102, 126, 234, 0.8)',
                    'rgba(240, 147, 251, 0.8)',
                    'rgba(67, 233, 123, 0.8)',
                    'rgba(255, 193, 7, 0.8)'
                ],
                borderColor: [
                    'rgb(102, 126, 234)',
                    'rgb(240, 147, 251)',
                    'rgb(67, 233, 123)',
                    'rgb(255, 193, 7)'
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
                            const value = context.parsed;
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = ((value / total) * 100).toFixed(1);
                            return context.label + ': $' + value.toLocaleString() + ' (' + percentage + '%)';
                        }
                    }
                }
            }
        }
    });

    // Initialize Growth Trends Chart (Line Chart)
    const growthCtx = document.getElementById('growthChart').getContext('2d');
    const growthChart = new Chart(growthCtx, {
        type: 'line',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
            datasets: [
                {
                    label: 'Electronics',
                    data: [12000, 15000, 18000, 22000, 25000, 28000],
                    borderColor: 'rgb(102, 126, 234)',
                    backgroundColor: 'rgba(102, 126, 234, 0.1)',
                    borderWidth: 3,
                    fill: false,
                    tension: 0.4
                },
                {
                    label: 'Clothing',
                    data: [8000, 9500, 11000, 13000, 15000, 17000],
                    borderColor: 'rgb(240, 147, 251)',
                    backgroundColor: 'rgba(240, 147, 251, 0.1)',
                    borderWidth: 3,
                    fill: false,
                    tension: 0.4
                },
                {
                    label: 'Food & Beverages',
                    data: [5000, 5200, 4800, 5500, 5800, 6000],
                    borderColor: 'rgb(67, 233, 123)',
                    backgroundColor: 'rgba(67, 233, 123, 0.1)',
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

    // Animate progress bars on page load
    setTimeout(() => {
        $('.progress-bar').each(function() {
            const width = $(this).css('width');
            $(this).css('width', '0%').animate({ width: width }, 1500);
        });
    }, 500);

    // Animate stats on page load
    $('.stat-value, .metric-value').each(function() {
        const $this = $(this);
        const text = $this.text();
        const isPrice = text.includes('$');
        const isPercentage = text.includes('%');
        const isNegative = text.includes('-');
        const countTo = parseInt(text.replace(/[^0-9]/g, ''));
        
        $({ countNum: 0 }).animate({
            countNum: countTo
        }, {
            duration: 2000,
            easing: 'swing',
            step: function() {
                let displayValue = Math.floor(this.countNum).toLocaleString();
                
                if (isPrice) {
                    displayValue = '$' + displayValue;
                } else if (isPercentage) {
                    displayValue = (isNegative ? '-' : '+') + displayValue + '%';
                }
                
                $this.text(displayValue);
            },
            complete: function() {
                $this.text(text);
            }
        });
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

    // Category card hover effects
    $('.category-card').on('mouseenter', function() {
        $(this).find('.category-icon').addClass('animate__animated animate__pulse');
    }).on('mouseleave', function() {
        $(this).find('.category-icon').removeClass('animate__animated animate__pulse');
    });
});
</script>
@endsection