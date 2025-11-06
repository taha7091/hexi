@extends('layouts.admin')

@section('title', 'POS Devices Management')
@section('page-title', 'POS Devices')

@section('styles')
<style>
    .devices-container {
        background: var(--bg-primary);
        min-height: 100vh;
        padding: 2rem 0;
    }

    .devices-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 2rem;
        border-radius: 15px;
        margin-bottom: 2rem;
        position: relative;
        overflow: hidden;
    }

    .devices-header::before {
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

    .devices-title {
        font-size: 2rem;
        font-weight: 800;
        margin: 0;
        position: relative;
        z-index: 2;
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .devices-subtitle {
        opacity: 0.9;
        margin-top: 0.5rem;
        position: relative;
        z-index: 2;
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }

    .stat-card {
        background: var(--card-bg);
        padding: 1.5rem;
        border-radius: 15px;
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
    }

    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 35px rgba(0,0,0,0.15);
    }

    .stat-card.total {
        --stat-gradient: linear-gradient(90deg, #667eea, #764ba2);
    }

    .stat-card.activated {
        --stat-gradient: linear-gradient(90deg, #43e97b, #38f9d7);
    }

    .stat-card.online {
        --stat-gradient: linear-gradient(90deg, #4facfe, #00f2fe);
    }

    .stat-card.companies {
        --stat-gradient: linear-gradient(90deg, #f093fb, #f5576c);
    }

    .stat-value {
        font-size: 2rem;
        font-weight: 900;
        color: var(--text-primary);
        margin-bottom: 0.5rem;
    }

    .stat-label {
        color: var(--text-secondary);
        font-size: 0.9rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    .filters-section {
        background: var(--card-bg);
        padding: 1.5rem;
        border-radius: 15px;
        box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        margin-bottom: 2rem;
    }

    .filters-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
        align-items: end;
    }

    .filter-group {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }

    .filter-label {
        font-weight: 600;
        color: var(--text-primary);
        font-size: 0.9rem;
    }

    .filter-control {
        padding: 0.75rem;
        border: 2px solid var(--border-color);
        border-radius: 8px;
        background: var(--bg-secondary);
        color: var(--text-primary);
        transition: all 0.3s ease;
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
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .btn-filter:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
    }

    .devices-table-container {
        background: var(--card-bg);
        border-radius: 15px;
        box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        overflow: hidden;
    }

    .table-header {
        padding: 1.5rem;
        border-bottom: 2px solid var(--border-color);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .table-title {
        font-size: 1.3rem;
        font-weight: 700;
        color: var(--text-primary);
    }

    .btn-create {
        padding: 0.75rem 1.5rem;
        background: linear-gradient(135deg, #43e97b, #38f9d7);
        color: white;
        text-decoration: none;
        border-radius: 8px;
        font-weight: 600;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .btn-create:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(67, 233, 123, 0.3);
        color: white;
        text-decoration: none;
    }

    .devices-table {
        width: 100%;
        border-collapse: collapse;
    }

    .devices-table thead {
        background: var(--bg-secondary);
    }

    .devices-table th {
        padding: 1rem;
        text-align: left;
        font-weight: 600;
        color: var(--text-primary);
        font-size: 0.9rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .devices-table tbody tr {
        transition: all 0.3s ease;
        border-bottom: 1px solid var(--border-color);
    }

    .devices-table tbody tr:hover {
        background: rgba(102, 126, 234, 0.05);
    }

    .devices-table td {
        padding: 1rem;
        color: var(--text-primary);
    }

    .device-info {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .device-icon {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        background: linear-gradient(135deg, #667eea, #764ba2);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.2rem;
    }

    .device-details h4 {
        margin: 0;
        font-size: 1rem;
        font-weight: 600;
        color: var(--text-primary);
    }

    .device-details p {
        margin: 0;
        font-size: 0.85rem;
        color: var(--text-secondary);
    }

    .status-badge {
        padding: 0.5rem 1rem;
        border-radius: 20px;
        font-weight: 600;
        font-size: 0.85rem;
        text-align: center;
        min-width: 80px;
    }

    .status-badge.online {
        background: linear-gradient(135deg, #43e97b, #38f9d7);
        color: white;
    }

    .status-badge.offline {
        background: linear-gradient(135deg, #6c757d, #495057);
        color: white;
    }

    .status-badge.not-activated {
        background: linear-gradient(135deg, #ffc107, #ff8f00);
        color: white;
    }

    .status-badge.inactive {
        background: linear-gradient(135deg, #e53e3e, #c53030);
        color: white;
    }

    .activation-key {
        font-family: 'Courier New', monospace;
        background: var(--bg-secondary);
        padding: 0.5rem;
        border-radius: 5px;
        font-size: 0.85rem;
        color: var(--text-primary);
        border: 1px solid var(--border-color);
    }

    .device-actions {
        display: flex;
        gap: 0.5rem;
    }

    .btn-action {
        padding: 0.5rem;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        transition: all 0.3s ease;
        font-size: 0.85rem;
        text-decoration: none;
        display: flex;
        align-items: center;
        justify-content: center;
        min-width: 35px;
    }

    .btn-view {
        background: #4facfe;
        color: white;
    }

    .btn-edit {
        background: #ffc107;
        color: white;
    }

    .btn-activate {
        background: #43e97b;
        color: white;
    }

    .btn-deactivate {
        background: #e53e3e;
        color: white;
    }

    .btn-action:hover {
        transform: scale(1.1);
        box-shadow: 0 4px 15px rgba(0,0,0,0.2);
    }

    .no-devices {
        text-align: center;
        padding: 3rem;
        color: var(--text-secondary);
    }

    .no-devices i {
        font-size: 3rem;
        margin-bottom: 1rem;
        opacity: 0.3;
    }

    .pagination-container {
        padding: 1.5rem;
        border-top: 2px solid var(--border-color);
        display: flex;
        justify-content: center;
    }
</style>
@endsection

@section('content')
<div class="devices-container">
    <div class="container-fluid">
        <!-- Header -->
        <div class="devices-header">
            <h1 class="devices-title">
                <i class="fas fa-tablet-alt"></i>
                POS Devices Management
            </h1>
            <p class="devices-subtitle">Manage and monitor all POS devices across your organization</p>
        </div>

        <!-- Statistics -->
        <div class="stats-grid">
            <div class="stat-card total">
                <div class="stat-value">{{ $stats['total_devices'] }}</div>
                <div class="stat-label">Total Devices</div>
            </div>
            <div class="stat-card activated">
                <div class="stat-value">{{ $stats['activated_devices'] }}</div>
                <div class="stat-label">Activated Devices</div>
            </div>
            <div class="stat-card online">
                <div class="stat-value">{{ $stats['online_devices'] }}</div>
                <div class="stat-label">Online Devices</div>
            </div>
            <div class="stat-card companies">
                <div class="stat-value">{{ $stats['companies_with_devices'] }}</div>
                <div class="stat-label">Companies</div>
            </div>
            <div class="stat-card total-mac">
                <div class="stat-value">{{ $stats['total_mac_devices'] }}</div>
                <div class="stat-label">Total Active Devices (All Systems)</div>
            </div>
        </div>

        <!-- Filters -->
        <div class="filters-section">
            <form method="GET" action="{{ route('admin.pos-devices.index') }}">
                <div class="filters-grid">
                    @if(Auth::user()->isMasterAdmin())
                    <div class="filter-group">
                        <label class="filter-label">Company</label>
                        <select name="company_id" class="filter-control">
                            <option value="">All Companies</option>
                            @foreach($companies as $company)
                                <option value="{{ $company->id }}" {{ request('company_id') == $company->id ? 'selected' : '' }}>
                                    {{ $company->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    @endif

                    <div class="filter-group">
                        <label class="filter-label">Status</label>
                        <select name="status" class="filter-control">
                            <option value="">All Status</option>
                            <option value="activated" {{ request('status') == 'activated' ? 'selected' : '' }}>Activated</option>
                            <option value="not_activated" {{ request('status') == 'not_activated' ? 'selected' : '' }}>Not Activated</option>
                            <option value="online" {{ request('status') == 'online' ? 'selected' : '' }}>Online</option>
                            <option value="offline" {{ request('status') == 'offline' ? 'selected' : '' }}>Offline</option>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label class="filter-label">Search</label>
                        <input type="text" name="search" class="filter-control" placeholder="Device name, serial, or key..." value="{{ request('search') }}">
                    </div>

                    <div class="filter-group">
                        <button type="submit" class="btn-filter">
                            <i class="fas fa-search"></i> Filter
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Devices Table -->
        <div class="devices-table-container">
            <div class="table-header">
                <h3 class="table-title">POS Devices</h3>
                <a href="{{ route('admin.pos-devices.create') }}" class="btn-create">
                    <i class="fas fa-plus"></i>
                    Add New Device
                </a>
            </div>

            @if($devices->count() > 0)
                <table class="devices-table">
                    <thead>
                        <tr>
                            <th>Device</th>
                            <th>Company/Branch</th>
                            <th>Activation Key</th>
                            <th>Status</th>
                            <th>Last Seen</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($devices as $device)
                            <tr>
                                <td>
                                    <div class="device-info">
                                        <div class="device-icon">
                                            <i class="fas fa-tablet-alt"></i>
                                        </div>
                                        <div class="device-details">
                                            <h4>{{ $device->device_name }}</h4>
                                            <p>{{ $device->device_serial ?? 'No Serial' }} • {{ $device->device_model ?? 'Unknown Model' }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div>
                                        <strong>{{ $device->branch->company->name ?? 'No Company' }}</strong><br>
                                        <small>{{ $device->branch->name ?? 'No Branch' }}</small>
                                    </div>
                                </td>
                                <td>
                                    <div class="activation-key">{{ $device->activation_key }}</div>
                                </td>
                                <td>
                                    @if($device->is_activated)
                                        @php
                                            $lastSeenMinutes = $device->last_seen ? $device->last_seen->diffInMinutes(now()) : null;
                                            $isOnline = $lastSeenMinutes !== null && $lastSeenMinutes <= 5;
                                        @endphp
                                        <span class="status-badge {{ $isOnline ? 'online' : 'offline' }}">
                                            {{ $isOnline ? 'Online' : 'Offline' }}
                                        </span>
                                    @else
                                        <span class="status-badge not-activated">Not Activated</span>
                                    @endif
                                </td>
                                <td>
                                    @if($device->last_seen)
                                        {{ $device->last_seen->diffForHumans() }}
                                    @else
                                        <span class="text-muted">Never</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="device-actions">
                                        <a href="{{ route('admin.pos-devices.show', $device) }}" class="btn-action btn-view" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.pos-devices.edit', $device) }}" class="btn-action btn-edit" title="Edit Device">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        @if($device->is_activated)
                                            <button class="btn-action btn-deactivate" onclick="deactivateDevice({{ $device->id }})" title="Deactivate">
                                                <i class="fas fa-power-off"></i>
                                            </button>
                                        @else
                                            <button class="btn-action btn-activate" onclick="activateDevice({{ $device->id }})" title="Activate">
                                                <i class="fas fa-play"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <div class="pagination-container">
                    {{ $devices->appends(request()->query())->links() }}
                </div>
            @else
                <div class="no-devices">
                    <i class="fas fa-tablet-alt"></i>
                    <h3>No POS Devices Found</h3>
                    <p>No devices match your current filters. Try adjusting your search criteria.</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function activateDevice(deviceId) {
    if (confirm('Are you sure you want to activate this device?')) {
        fetch(`/admin/pos-devices/${deviceId}/activate`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Device activated successfully');
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while activating the device');
        });
    }
}

function deactivateDevice(deviceId) {
    const reason = prompt('Please enter a reason for deactivation:');
    if (reason !== null) {
        fetch(`/admin/pos-devices/${deviceId}/deactivate`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ reason: reason })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Device deactivated successfully');
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while deactivating the device');
        });
    }
}
</script>
@endsection