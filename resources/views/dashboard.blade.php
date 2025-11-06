@extends('layouts.admin')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')
    @if(Auth::user()->isMasterAdmin())
        <!-- Master Admin Dashboard Content -->
        <div class="dashboard-cards">
            <div class="dashboard-card">
                <div class="card-header">
                    <div class="card-title">Companies</div>
                    <div class="card-icon" style="background: linear-gradient(135deg, #007bff, #0056b3);">🏢</div>
                </div>
                <div class="card-value">{{ \App\Models\Company::count() }}</div>
                <div class="card-description">Total Licensed Companies</div>
                <a href="{{ route('admin.companies.index') }}" class="card-link">Manage Companies →</a>
            </div>

            <div class="dashboard-card">
                <div class="card-header">
                    <div class="card-title">Brands</div>
                    <div class="card-icon" style="background: linear-gradient(135deg, #28a745, #1e7e34);">🏷️</div>
                </div>
                <div class="card-value">{{ \App\Models\Brand::count() }}</div>
                <div class="card-description">Total Brands</div>
            </div>

            <div class="dashboard-card">
                <div class="card-header">
                    <div class="card-title">Users</div>
                    <div class="card-icon" style="background: linear-gradient(135deg, #ffc107, #e0a800);">👥</div>
                </div>
                <div class="card-value">{{ \App\Models\User::where('id', '!=', 1)->count() }}</div>
                <div class="card-description">Total System Users</div>
                <a href="{{ route('admin.users.index') }}" class="card-link">Manage Users →</a>
            </div>

            <div class="dashboard-card">
                <div class="card-header">
                    <div class="card-title">POS Devices</div>
                    <div class="card-icon" style="background: linear-gradient(135deg, #dc3545, #c82333);">🖥️</div>
                </div>
                <div class="card-value">{{ \App\Models\PosDevice::count() }}</div>
                <div class="card-description">Active POS Devices</div>
                <a href="#" class="card-link">Manage Devices →</a>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="quick-actions">
            <a href="{{ route('admin.companies.create') }}" class="quick-action-btn btn-primary">
                + Add New Company
            </a>
            <a href="{{ route('admin.brands.create') }}" class="quick-action-btn btn-success">
                + Add New Brand
            </a>
            <a href="{{ route('admin.users.create') }}" class="quick-action-btn btn-warning">
                + Add New User
            </a>
        </div>

        <!-- Recent Activity -->
        <div class="recent-activity">
            <div class="activity-header">
                <h3>Recent Activity</h3>
            </div>
            <div class="activity-list">
                @php
                    $recentCompanies = \App\Models\Company::latest()->take(5)->get();
                @endphp
                @if($recentCompanies->count() > 0)
                    @foreach($recentCompanies as $company)
                        <div class="activity-item">
                            <div class="activity-icon">🏢</div>
                            <div class="activity-content">
                                <div class="activity-title">{{ $company->name }}</div>
                                <div class="activity-time">Added {{ $company->created_at->diffForHumans() }}</div>
                            </div>
                            <div class="activity-status {{ $company->license_expiry > now() ? 'status-active' : 'status-expired' }}">
                                {{ $company->license_expiry > now() ? 'Active' : 'Expired' }}
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="activity-item">
                        <div class="activity-icon">📝</div>
                        <div class="activity-content">
                            <div class="activity-title">No recent activity</div>
                            <div class="activity-time">Start by adding your first company</div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    @else
        <!-- Company User Dashboard Content -->
        <div class="dashboard-cards">
            <div class="dashboard-card">
                <div class="card-header">
                    <div class="card-title">Your Company</div>
                    <div class="card-icon" style="background: linear-gradient(135deg, #007bff, #0056b3);">🏢</div>
                </div>
                <div class="card-value">{{ Auth::user()->company->name ?? 'No Company' }}</div>
                <div class="card-description">
                    @if(Auth::user()->company)
                        License: {{ Auth::user()->company->license_expiry > now() ? 'Active' : 'Expired' }}
                    @else
                        No company assigned
                    @endif
                </div>
            </div>

            @if(Auth::user()->company)
                <div class="dashboard-card">
                    <div class="card-header">
                        <div class="card-title">Brands</div>
                        <div class="card-icon" style="background: linear-gradient(135deg, #28a745, #1e7e34);">🏷️</div>
                    </div>
                    <div class="card-value">{{ Auth::user()->company->brands->count() }}</div>
                    <div class="card-description">Company Brands</div>
                  
                </div>

                <div class="dashboard-card">
                    <div class="card-header">
                        <div class="card-title">Branches</div>
                        <div class="card-icon" style="background: linear-gradient(135deg, #ffc107, #e0a800);">🏪</div>
                    </div>
                    <div class="card-value">{{ Auth::user()->company->branches->count() }}</div>
                    <div class="card-description">Store Locations</div>
                   
                </div>

                <div class="dashboard-card">
                    <div class="card-header">
                        <div class="card-title">POS Devices</div>
                        <div class="card-icon" style="background: linear-gradient(135deg, #dc3545, #c82333);">🖥️</div>
                    </div>
                    <div class="card-value">{{ Auth::user()->company->active_device_count }}</div>
                    <div class="card-description">Active Devices ({{ Auth::user()->company->active_device_count }}/{{ Auth::user()->company->device_limit }})</div>
                    <a href="#" class="card-link">View Devices →</a>
                </div>
            @endif
        </div>

        <!-- Company Quick Actions -->
        <div class="quick-actions">
            @if(Auth::user()->canManageBrands())
               
            @endif
            <a href="{{ route('admin.users.create') }}" class="quick-action-btn btn-warning">
                + Add New User
            </a>
            @if(Auth::user()->canManageProducts())
                <a href="{{ route('admin.products.index') }}" class="quick-action-btn btn-info">
                    📦 Manage Products
                </a>
            @endif
        </div>
    @endif
@endsection

@section('styles')
    <style>
        /* Dashboard Cards */
        .dashboard-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .dashboard-card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .dashboard-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.15);
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .card-title {
            font-size: 16px;
            font-weight: 600;
            color: #666;
        }

        .card-icon {
            width: 40px;
            height: 40px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            color: white;
        }

        .card-value {
            font-size: 32px;
            font-weight: bold;
            color: #333;
            margin-bottom: 10px;
        }

        .card-description {
            font-size: 14px;
            color: #666;
            margin-bottom: 15px;
        }

        .card-link {
            color: #007bff;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
        }

        .card-link:hover {
            text-decoration: underline;
        }

        /* Quick Actions */
        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 30px;
        }

        .quick-action-btn {
            padding: 15px 20px;
            border: none;
            border-radius: 8px;
            color: white;
            text-decoration: none;
            text-align: center;
            font-weight: 500;
            transition: all 0.3s ease;
            display: block;
        }

        .quick-action-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }

        .btn-primary { background: linear-gradient(135deg, #007bff, #0056b3); }
        .btn-success { background: linear-gradient(135deg, #28a745, #1e7e34); }
        .btn-warning { background: linear-gradient(135deg, #ffc107, #e0a800); }
        .btn-info { background: linear-gradient(135deg, #17a2b8, #138496); }

        /* Recent Activity */
        .recent-activity {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        .activity-header {
            padding: 20px;
            background: #f8f9fa;
            border-bottom: 1px solid #dee2e6;
        }

        .activity-header h3 {
            font-size: 18px;
            font-weight: 600;
            color: #333;
        }

        .activity-list {
            padding: 20px;
        }

        .activity-item {
            display: flex;
            align-items: center;
            padding: 15px 0;
            border-bottom: 1px solid #f1f3f4;
        }

        .activity-item:last-child {
            border-bottom: none;
        }

        .activity-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #e9ecef;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            font-size: 16px;
            color: #666;
        }

        .activity-content {
            flex: 1;
        }

        .activity-title {
            font-weight: 600;
            color: #333;
            margin-bottom: 5px;
        }

        .activity-time {
            font-size: 12px;
            color: #666;
        }

        .activity-status {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 500;
        }

        .status-active {
            background: #d4edda;
            color: #155724;
        }

        .status-expired {
            background: #f8d7da;
            color: #721c24;
        }
    </style>
@endsection