@extends('layouts.admin')

@section('title', 'Reports Dashboard')
@section('page-title', 'Reports & Analytics')

@section('styles')
<style>
    .reports-container {
        background: var(--bg-primary);
        min-height: 100vh;
        padding: 2rem 0;
    }

    .reports-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #f093fb 100%);
        color: white;
        padding: 4rem 3rem;
        text-align: center;
        position: relative;
        overflow: hidden;
        border-radius: 25px 25px 0 0;
        margin-bottom: 0;
    }

    .reports-header::before {
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

    .reports-title {
        font-size: 3.5rem;
        font-weight: 900;
        margin: 0;
        position: relative;
        z-index: 2;
        text-shadow: 0 4px 8px rgba(0,0,0,0.2);
        background: linear-gradient(45deg, #ffffff, #f0f8ff);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        animation: titleGlow 3s ease-in-out infinite alternate;
    }

    @keyframes titleGlow {
        from { filter: drop-shadow(0 0 10px rgba(255,255,255,0.3)); }
        to { filter: drop-shadow(0 0 20px rgba(255,255,255,0.6)); }
    }

    .reports-subtitle {
        opacity: 0.95;
        margin-top: 1rem;
        position: relative;
        z-index: 2;
        font-size: 1.3rem;
        font-weight: 300;
        letter-spacing: 1px;
    }

    .reports-icon {
        position: absolute;
        top: 2rem;
        right: 3rem;
        font-size: 4rem;
        opacity: 0.2;
        z-index: 1;
        animation: float 6s ease-in-out infinite;
    }

    @keyframes float {
        0%, 100% { transform: translateY(0px); }
        50% { transform: translateY(-20px); }
    }

    .stats-overview {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 2rem;
        margin: 2rem 0;
        padding: 2rem;
        background: var(--card-bg);
        border-radius: 0 0 25px 25px;
        box-shadow: 0 25px 80px rgba(0,0,0,0.1);
    }

    .stat-card {
        background: var(--card-bg);
        border-radius: 15px;
        padding: 2rem;
        text-align: center;
        box-shadow: 0 10px 25px rgba(0,0,0,0.1);
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

    .stat-card:nth-child(1) {
        --stat-gradient: linear-gradient(90deg, #43e97b, #38f9d7);
        --stat-glow: rgba(67, 233, 123, 0.5);
    }

    .stat-card:nth-child(2) {
        --stat-gradient: linear-gradient(90deg, #4facfe, #00f2fe);
        --stat-glow: rgba(79, 172, 254, 0.5);
    }

    .stat-card:nth-child(3) {
        --stat-gradient: linear-gradient(90deg, #f093fb, #f5576c);
        --stat-glow: rgba(240, 147, 251, 0.5);
    }

    .stat-card:nth-child(4) {
        --stat-gradient: linear-gradient(90deg, #667eea, #764ba2);
        --stat-glow: rgba(102, 126, 234, 0.5);
    }

    .stat-value {
        font-size: 2.5rem;
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

    .reports-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
        gap: 2rem;
        padding: 3rem;
        background: var(--bg-primary);
    }

    .report-card {
        background: var(--card-bg);
        border-radius: 20px;
        padding: 2.5rem;
        box-shadow: 0 15px 35px rgba(0,0,0,0.1);
        transition: all 0.4s cubic-bezier(0.25, 0.8, 0.25, 1);
        position: relative;
        overflow: hidden;
        border: 2px solid transparent;
    }

    .report-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, var(--card-gradient));
        transition: all 0.3s ease;
    }

    .report-card:hover {
        transform: translateY(-10px) scale(1.02);
        box-shadow: 0 25px 50px rgba(0,0,0,0.15);
        border-color: var(--card-border);
    }

    .report-card:hover::before {
        height: 6px;
        box-shadow: 0 0 20px var(--card-glow);
    }

    .report-card.sales-by-item {
        --card-gradient: #667eea, #764ba2;
        --card-border: #667eea;
        --card-glow: rgba(102, 126, 234, 0.5);
    }

    .report-card.sales-by-category {
        --card-gradient: #4facfe, #00f2fe;
        --card-border: #4facfe;
        --card-glow: rgba(79, 172, 254, 0.5);
    }

    .report-card.inventory-status {
        --card-gradient: #43e97b, #38f9d7;
        --card-border: #43e97b;
        --card-glow: rgba(67, 233, 123, 0.5);
    }

    .report-card.profit-analysis {
        --card-gradient: #f093fb, #f5576c;
        --card-border: #f093fb;
        --card-glow: rgba(240, 147, 251, 0.5);
    }

    .report-icon {
        width: 80px;
        height: 80px;
        border-radius: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2.5rem;
        color: white;
        margin-bottom: 2rem;
        position: relative;
        overflow: hidden;
        transition: all 0.3s ease;
    }

    .report-icon::before {
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

    .report-card:hover .report-icon {
        transform: scale(1.1) rotate(5deg);
    }

    .report-card:hover .report-icon::before {
        animation: shine 0.6s ease;
    }

    @keyframes shine {
        0% { transform: translateX(-100%) translateY(-100%) rotate(-45deg); }
        100% { transform: translateX(100%) translateY(100%) rotate(-45deg); }
    }

    .sales-by-item .report-icon {
        background: linear-gradient(135deg, #667eea, #764ba2);
        box-shadow: 0 15px 35px rgba(102, 126, 234, 0.3);
    }

    .sales-by-category .report-icon {
        background: linear-gradient(135deg, #4facfe, #00f2fe);
        box-shadow: 0 15px 35px rgba(79, 172, 254, 0.3);
    }

    .inventory-status .report-icon {
        background: linear-gradient(135deg, #43e97b, #38f9d7);
        box-shadow: 0 15px 35px rgba(67, 233, 123, 0.3);
    }

    .profit-analysis .report-icon {
        background: linear-gradient(135deg, #f093fb, #f5576c);
        box-shadow: 0 15px 35px rgba(240, 147, 251, 0.3);
    }

    .report-title {
        font-size: 1.8rem;
        font-weight: 800;
        color: var(--text-primary);
        margin-bottom: 1rem;
        line-height: 1.2;
    }

    .report-description {
        color: var(--text-secondary);
        font-size: 1rem;
        line-height: 1.6;
        margin-bottom: 2rem;
    }

    .report-features {
        list-style: none;
        padding: 0;
        margin-bottom: 2rem;
    }

    .report-features li {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.5rem 0;
        color: var(--text-secondary);
        font-size: 0.95rem;
    }

    .report-features li::before {
        content: '✓';
        color: #43e97b;
        font-weight: bold;
        font-size: 1.1rem;
    }

    .report-actions {
        display: flex;
        gap: 1rem;
        margin-top: auto;
    }

    .btn-report {
        flex: 1;
        padding: 1rem 1.5rem;
        border-radius: 15px;
        font-weight: 700;
        text-decoration: none;
        border: none;
        cursor: pointer;
        transition: all 0.4s cubic-bezier(0.25, 0.8, 0.25, 1);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.75rem;
        font-size: 1rem;
        text-transform: uppercase;
        letter-spacing: 1px;
        position: relative;
        overflow: hidden;
    }

    .btn-report::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
        transition: left 0.6s ease;
    }

    .btn-report:hover::before {
        left: 100%;
    }

    .btn-primary-report {
        background: linear-gradient(135deg, var(--card-gradient));
        color: white;
        box-shadow: 0 8px 25px var(--card-glow);
    }

    .btn-primary-report:hover {
        transform: translateY(-3px) scale(1.05);
        box-shadow: 0 12px 35px var(--card-glow);
    }

    .btn-secondary-report {
        background: var(--bg-secondary);
        color: var(--text-primary);
        border: 2px solid var(--border-color);
    }

    .btn-secondary-report:hover {
        background: var(--bg-primary);
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        border-color: var(--card-border);
    }

    /* Mobile Responsive */
    @media (max-width: 768px) {
        .reports-grid {
            grid-template-columns: 1fr;
            padding: 2rem;
        }

        .reports-title {
            font-size: 2.5rem;
        }

        .reports-header {
            padding: 3rem 2rem;
        }

        .report-card {
            padding: 2rem;
        }

        .report-actions {
            flex-direction: column;
        }

        .stats-overview {
            grid-template-columns: repeat(2, 1fr);
        }
    }
</style>
@endsection

@section('content')
<div class="reports-container">
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-12">
                <!-- Reports Header -->
                <div class="reports-header">
                    <div class="reports-icon">📊</div>
                    <h1 class="reports-title">Reports & Analytics taha</h1>
                    <p class="reports-subtitle">Comprehensive business insights and data analysis</p>
                </div>

                <!-- Quick Stats Overview -->
                <div class="stats-overview">
                    <div class="stat-card">
                        <div class="stat-value">${{ number_format($todaysSales ?? 0, 2) }}</div>
                        <div class="stat-label">Today's Sales</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-value">{{ $todaysOrders ?? 0 }}</div>
                        <div class="stat-label">Orders Today</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-value">${{ number_format($thisMonthSales ?? 0, 2) }}</div>
                        <div class="stat-label">This Month</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-value">{{ number_format($stockLevel ?? 0, 1) }}%</div>
                        <div class="stat-label">Stock Level</div>
                    </div>
                </div>

                <!-- Reports Grid -->
                <div class="reports-grid">
                    <!-- Sales by Item Report -->
                    <div class="report-card sales-by-item">
                        <div class="report-icon">
                            <i class="fas fa-chart-bar"></i>
                        </div>
                        <h3 class="report-title">Sales by Item</h3>
                        <p class="report-description">
                            Detailed analysis of individual product performance with sales quantities, revenue, and trends.
                        </p>
                        <ul class="report-features">
                            <li>Product-wise sales breakdown</li>
                            <li>Revenue and quantity analysis</li>
                            <li>Top performing items</li>
                            <li>Trend analysis over time</li>
                            <li>Export to Excel/PDF</li>
                        </ul>
                        <div class="report-actions">
                            <a href="{{ route('admin.reports.sales-by-item') }}" class="btn-report btn-primary-report">
                                <i class="fas fa-chart-line"></i> View Report
                            </a>
                            <a href="#" class="btn-report btn-secondary-report">
                                <i class="fas fa-cog"></i> Configure
                            </a>
                        </div>
                    </div>

                    <!-- Sales by Category Report -->
                    <div class="report-card sales-by-category">
                        <div class="report-icon">
                            <i class="fas fa-chart-pie"></i>
                        </div>
                        <h3 class="report-title">Sales by Category</h3>
                        <p class="report-description">
                            Category-wise sales performance analysis with hierarchical breakdown and comparison metrics.
                        </p>
                        <ul class="report-features">
                            <li>Category performance overview</li>
                            <li>Brand and division breakdown</li>
                            <li>Market share analysis</li>
                            <li>Growth rate comparison</li>
                            <li>Visual charts and graphs</li>
                        </ul>
                        <div class="report-actions">
                            <a href="{{ route('admin.reports.sales-by-category') }}" class="btn-report btn-primary-report">
                                <i class="fas fa-chart-pie"></i> View Report
                            </a>
                            <a href="#" class="btn-report btn-secondary-report">
                                <i class="fas fa-cog"></i> Configure
                            </a>
                        </div>
                    </div>

                    <!-- Inventory Status Report -->
                    <div class="report-card inventory-status">
                        <div class="report-icon">
                            <i class="fas fa-boxes"></i>
                        </div>
                        <h3 class="report-title">Inventory Status</h3>
                        <p class="report-description">
                            Comprehensive inventory analysis with stock levels, reorder alerts, and movement tracking.
                        </p>
                        <ul class="report-features">
                            <li>Current stock levels</li>
                            <li>Low stock alerts</li>
                            <li>Inventory valuation</li>
                            <li>Movement history</li>
                            <li>Reorder recommendations</li>
                        </ul>
                        <div class="report-actions">
                            <a href="{{ route('admin.reports.inventory-status') }}" class="btn-report btn-primary-report">
                                <i class="fas fa-warehouse"></i> View Report
                            </a>
                            <a href="#" class="btn-report btn-secondary-report">
                                <i class="fas fa-cog"></i> Configure
                            </a>
                        </div>
                    </div>

                    <!-- Profit Analysis Report -->
                    <div class="report-card profit-analysis">
                        <div class="report-icon">
                            <i class="fas fa-dollar-sign"></i>
                        </div>
                        <h3 class="report-title">Profit Analysis</h3>
                        <p class="report-description">
                            Detailed profit margin analysis with cost breakdown, markup calculations, and profitability insights.
                        </p>
                        <ul class="report-features">
                            <li>Profit margin by product</li>
                            <li>Cost analysis breakdown</li>
                            <li>Markup percentage tracking</li>
                            <li>Profitability trends</li>
                            <li>ROI calculations</li>
                        </ul>
                        <div class="report-actions">
                            <a href="{{ route('admin.reports.profit-analysis') }}" class="btn-report btn-primary-report">
                                <i class="fas fa-chart-area"></i> View Report
                            </a>
                            <a href="#" class="btn-report btn-secondary-report">
                                <i class="fas fa-cog"></i> Configure
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
    // Animate stats on page load
    $('.stat-value').each(function() {
        const $this = $(this);
        const text = $this.text();
        const isPrice = text.includes('$');
        const isPercentage = text.includes('%');
        const countTo = parseFloat(text.replace(/[^0-9.]/g, ''));
        
        if (countTo > 0) {
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
        }
    });

    // Add hover effects to report cards
    $('.report-card').on('mouseenter', function() {
        $(this).find('.report-icon').addClass('animate__animated animate__pulse');
    }).on('mouseleave', function() {
        $(this).find('.report-icon').removeClass('animate__animated animate__pulse');
    });
});
</script>
@endsection