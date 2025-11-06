@extends('layouts.admin')

@section('title', 'Companies Management - ERP System')

@section('page-title', 'Companies Management')

@section('breadcrumb', 'Home > Admin > Companies')

@section('styles')
<style>
/* === GENERAL CONTAINER === */
.table-container {
    background: #ffffff;
    border-radius: 14px;
    box-shadow: 0 4px 18px rgba(0, 0, 0, 0.06);
    overflow: hidden;
    margin-bottom: 25px;
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
    max-width: 100%;
}

/* === TABLE STYLING === */
table {
    width: 100%;
    border-collapse: collapse;
    font-size: 13px;
    table-layout: auto;
    min-width: unset;
}
th, td {
    padding: 10px 12px;
    text-align: left;
    border-bottom: 1px solid #eef0f2;
    white-space: nowrap;
}
th {
    background: #f8fafb;
    font-weight: 700;
    color: #2c3e50;
    font-size: 12px;
    text-transform: uppercase;
    letter-spacing: 0.6px;
}
tr:nth-child(even) {
    background: #fcfcfc;
}

/* === STATUS COLORS === */
.status-active {
    color: #16a085;
    font-weight: 600;
}
.status-expired {
    color: #e74c3c;
    font-weight: 600;
}

/* === ACTION BUTTONS === */
.actions {
    display: flex;
    gap: 6px;
    flex-wrap: wrap;
}
.actions .btn {
    flex: 1;
    min-width: 70px;
    text-align: center;
    padding: 8px 10px;
    font-size: 12px;
    font-weight: 600;
    color: #ffffff;
    background-color: #3498db;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    transition: background-color 0.2s ease;
}
.actions .btn:hover {
    background-color: #2d83c4;
}

/* === SEARCH BOX === */
.search-box {
    margin-bottom: 20px;
}
.search-box input {
    padding: 10px 16px;
    border: 2px solid #e3e6e8;
    border-radius: 8px;
    width: 100%;
    max-width: 300px;
    font-size: 13px;
    transition: all 0.3s ease;
}
.search-box input:focus {
    outline: none;
    border-color: #3498db;
    box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
}

/* === HEADER ACTIONS === */
.header-actions {
    display: flex;
    gap: 12px;
    margin-bottom: 20px;
    align-items: center;
    flex-wrap: wrap;
}

/* === STATISTICS CARDS === */
.stats-cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 15px;
    margin-bottom: 25px;
}
.stat-card {
    background: #fff;
    padding: 18px 14px;
    border-radius: 10px;
    box-shadow: 0 4px 14px rgba(0,0,0,0.05);
    text-align: center;
    border: 1px solid #f1f3f4;
}
.stat-number {
    font-size: 24px;
    font-weight: 700;
    color: #2c3e50;
    margin-bottom: 4px;
}
.stat-label {
    font-size: 12px;
    color: #7f8c8d;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

/* === MOBILE CARD VIEW === */
.mobile-card-view {
    display: none;
}

/* === RESPONSIVE BREAKPOINTS === */
@media (max-width: 1200px) {
    table { font-size: 12.5px; }
    th, td { padding: 8px 10px; }
    .actions .btn { min-width: 65px; font-size: 11.5px; padding: 7px 8px; }
}

@media (max-width: 992px) {
    .stats-cards { grid-template-columns: repeat(2, 1fr); }
    th, td { padding: 8px 6px; font-size: 12px; }
}

@media (max-width: 768px) {
    .header-actions {
        flex-direction: column;
        align-items: stretch;
        gap: 8px;
    }

    .search-box input {
        max-width: none;
        width: 100%;
    }

    .stats-cards {
        grid-template-columns: 1fr;
    }

    .actions {
        flex-direction: row;
        flex-wrap: wrap;
        justify-content: center;
        gap: 6px;
    }

    .actions .btn {
        flex: 1 1 45%;
        font-size: 11.5px;
        padding: 7px 9px;
        min-width: 85px;
    }

    th, td {
        padding: 8px 6px;
        font-size: 11.5px;
    }
}

@media (max-width: 480px) {
    .table-container table { display: none; }
    .mobile-card-view { display: block; }

    .company-mobile-card {
        background: #fff;
        border-radius: 10px;
        margin-bottom: 12px;
        box-shadow: 0 4px 14px rgba(0,0,0,0.05);
        border: 1px solid #e9ecef;
        overflow: hidden;
    }

    .company-mobile-header {
        background: #3498db;
        color: white;
        padding: 12px;
        font-weight: 600;
        font-size: 14px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .company-mobile-info {
        padding: 12px;
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 8px;
        font-size: 12px;
    }

    .company-mobile-info strong {
        color: #2c3e50;
        display: block;
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 3px;
    }

    .company-mobile-actions {
        padding: 12px;
        border-top: 1px solid #f1f3f4;
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        background: #f9fafb;
    }

    .company-mobile-actions .btn {
        flex: 1 1 45%;
        padding: 7px 9px;
        font-size: 11.5px;
        background: #3498db;
        color: white;
        border-radius: 6px;
        border: none;
        text-align: center;
    }

    .company-mobile-actions .btn:hover {
        background: #2d83c4;
    }
}
</style>

@endsection


@section('content')
<div class="header-actions">
    <a href="{{ route('admin.companies.create') }}" class="btn btn-primary">
        <i style="margin-right: 8px;">➕</i> Add New Company
    </a>
</div>

<!-- Stats Cards -->
<div class="stats-cards">
    <div class="stat-card">
        <div class="stat-number">{{ $companies->total() }}</div>
        <div class="stat-label">Total Companies</div>
    </div>
    <div class="stat-card">
        <div class="stat-number">{{ $companies->where('license_expiry', '>', now())->count() }}</div>
        <div class="stat-label">Active Licenses</div>
    </div>
    <div class="stat-card">
        <div class="stat-number">{{ $companies->where('license_expiry', '<', now())->count() }}</div>
        <div class="stat-label">Expired Licenses</div>
    </div>
</div>

<div class="search-box">
    <input type="text" id="searchInput" placeholder="🔍 Search companies..." onkeyup="searchTable()">
</div>

<div class="mobile-scroll-hint">
    👈 Scroll horizontally to see all columns
</div>

<div class="table-container table-responsive">
            <table id="companiesTable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Company Name</th>
                        <th>License Key</th>
                        <th>License Status</th>
                        <th>License Expiry</th>
                        <th>POS Limit</th>
                        <th>Active POS</th>
                        <th>Brands</th>
                        <th>Users</th>
                        <th>Sales</th>
                        <th>Revenue</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($companies as $company)
                        <tr>
                            <td>{{ $company->id }}</td>
                            <td><strong>{{ $company->name }}</strong></td>
                            <td><code>{{ $company->license_key }}</code></td>
                            <td>
                                @if($company->license_expiry > now())
                                    <span class="status-active">Active</span>
                                @else
                                    <span class="status-expired">Expired</span>
                                @endif
                            </td>
                            <td>{{ $company->license_expiry->format('M d, Y') }}</td>
                            <td>{{ $company->pos_limit }}</td>
                            <td>
                                @php
                                    $activePOS = $company->branches->sum(function($branch) {
                                        return $branch->posDevices->where('status', 'active')->count();
                                    });
                                @endphp
                                {{ $activePOS }}
                            </td>
                            <td>{{ $company->brands->count() }}</td>
                            <td>{{ $company->users->count() }}</td>
                            <td><strong>{{ $company->sales_count ?? 0 }}</strong></td>
                            <td><strong style="color: #059669;">${{ number_format($company->total_sales_amount ?? 0, 2) }}</strong></td>
                            <td>
                                <div class="actions">
                                    <a href="{{ route('admin.companies.show', $company) }}" class="btn btn-primary" style="padding: 5px 10px; font-size: 12px;">View</a>
                                    <a href="{{ route('admin.companies.edit', $company) }}" class="btn btn-warning" style="padding: 5px 10px; font-size: 12px;">Edit</a>
                                    @if(($company->sales_count ?? 0) > 0)
                                        <button onclick="resetCompanySales({{ $company->id }}, {{ json_encode($company->name) }})" class="btn btn-danger" style="padding: 5px 10px; font-size: 12px;">🗑️ Reset Sales</button>
                                    @endif
                                    <button onclick="removeAllCompanyData({{ $company->id }}, {{ json_encode($company->name) }})" class="btn btn-danger" style="padding: 5px 10px; font-size: 12px; background-color: #dc2626;">💣 Remove All Data</button>
                                    <form method="POST" action="{{ route('admin.companies.destroy', $company) }}" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this company? This will delete all related data.')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger" style="padding: 5px 10px; font-size: 12px;">Delete Company</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="12" style="text-align: center; color: #666; padding: 40px;">
                                No companies found. <a href="{{ route('admin.companies.create') }}">Add the first company</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Mobile Card View -->
        <div class="mobile-card-view">
            @forelse($companies as $company)
                <div class="company-mobile-card">
                    <div class="company-mobile-header">
                        {{ $company->name }}
                    </div>
                    <div class="company-mobile-info">
                        <div><strong>ID:</strong> {{ $company->id }}</div>
                        <div><strong>License:</strong> <code>{{ $company->license_key }}</code></div>
                        <div><strong>Status:</strong>
                            @if($company->license_expiry > now())
                                <span class="status-active">Active</span>
                            @else
                                <span class="status-expired">Expired</span>
                            @endif
                        </div>
                        <div><strong>Expiry:</strong> {{ $company->license_expiry->format('M d, Y') }}</div>
                        <div><strong>POS Limit:</strong> {{ $company->pos_limit }}</div>
                        <div><strong>Active POS:</strong>
                            @php
                                $activePosCount = $company->posDevices()->where('status', 'active')->count();
                            @endphp
                            {{ $activePosCount }}
                        </div>
                        <div><strong>Brands:</strong> {{ $company->brands->count() }}</div>
                        <div><strong>Users:</strong> {{ $company->users->count() }}</div>
                        <div><strong>Sales:</strong> {{ $company->sales_count ?? 0 }}</div>
                        <div><strong>Revenue:</strong> <span style="color: #059669;">${{ number_format($company->total_sales_amount ?? 0, 2) }}</span></div>
                    </div>
                    <div class="company-mobile-actions">
                        <a href="{{ route('admin.companies.show', $company) }}" class="btn btn-primary">View</a>
                        <a href="{{ route('admin.companies.edit', $company) }}" class="btn btn-warning">Edit</a>
                        @if(($company->sales_count ?? 0) > 0)
                            <button onclick="resetCompanySales({{ $company->id }}, {{ json_encode($company->name) }})" class="btn btn-danger">🗑️ Reset Sales</button>
                        @endif
                        <button onclick="removeAllCompanyData({{ $company->id }}, {{ json_encode($company->name) }})" class="btn btn-danger" style="background-color: #dc2626;">💣 Remove All Data</button>
                        <form method="POST" action="{{ route('admin.companies.destroy', $company) }}" style="display: inline; flex: 1;" onsubmit="return confirm('Are you sure you want to delete this company? This will delete all related data.')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger" style="width: 100%;">Delete Company</button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="company-mobile-card">
                    <div class="company-mobile-header">No Companies Found</div>
                    <div class="company-mobile-info">
                        <a href="{{ route('admin.companies.create') }}">Add the first company</a>
                    </div>
                </div>
            @endforelse
        </div>

@if($companies->hasPages())
    <div style="margin-top: 30px; text-align: center;">
        {{ $companies->links() }}
    </div>
@endif
@endsection


@section('scripts')
<script type="text/javascript">
function searchTable() {
    const input = document.getElementById('searchInput');
    const filter = input.value.toLowerCase();
    const table = document.getElementById('companiesTable');
    const rows = table.getElementsByTagName('tr');

    for (let i = 1; i < rows.length; i++) {
        const cells = rows[i].getElementsByTagName('td');
        let found = false;

        for (let j = 0; j < cells.length - 1; j++) {
            if (cells[j].textContent.toLowerCase().includes(filter)) {
                found = true;
                break;
            }
        }

        rows[i].style.display = found ? '' : 'none';
    }
}

function resetCompanySales(companyId, companyName) {
    console.log('Reset sales function called with:', companyId, companyName);

    // First confirmation
    if (!confirm('WARNING: This will permanently delete ALL sales data for "' + companyName + '".\n\nThis includes:\n• All sales records\n• All sale items\n• All payment records\n\nThis action CANNOT be undone!\n\nAre you sure you want to continue?')) {
        return;
    }

    // Second confirmation
    if (!confirm('FINAL WARNING: You are about to permanently delete all sales data for "' + companyName + '".\n\nClick OK to proceed or Cancel to abort.')) {
        return;
    }

    // Third confirmation - type DELETE
    const confirmation = prompt('Type "DELETE" (all caps) to confirm permanent deletion of sales data:');
    if (confirmation !== 'DELETE') {
        alert('Deletion cancelled. Sales data is safe.');
        return;
    }

    // Get CSRF token
    const csrfToken = document.querySelector('meta[name="csrf-token"]');
    if (!csrfToken) {
        alert('Error: CSRF token not found. Please refresh the page.');
        return;
    }

    // Make the API call
    fetch('/admin/companies/' + companyId + '/reset-sales', {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken.getAttribute('content'),
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            confirmation: 'DELETE'
        })
    })
    .then(function(response) {
        return response.json();
    })
    .then(function(data) {
        if (data.success) {
            alert('Success!\n\n' + data.message + '\n\nDeleted:\n• ' + data.data.deleted_sales + ' sales\n• ' + data.data.deleted_sale_items + ' sale items\n• ' + data.data.deleted_payments + ' payments');
            location.reload();
        } else {
            alert('Error: ' + (data.error || 'Unknown error occurred'));
        }
    })
    .catch(function(error) {
        console.error('Error:', error);
        alert('An error occurred while resetting sales data. Check console for details.');
    });
}

function removeAllCompanyData(companyId, companyName) {
    console.log('Remove all data function called with:', companyId, companyName);

    // First confirmation
    if (!confirm('⚠️ EXTREME WARNING ⚠️\n\nThis will PERMANENTLY DELETE ALL DATA for "' + companyName + '":\n\n• All Products\n• All Users\n• All Brands\n• All Sales\n• All Categories\n• All Groups\n• All Branches\n• All POS Devices\n\nThis action CANNOT be undone!\n\nAre you absolutely sure?')) {
        return;
    }

    // Second confirmation
    if (!confirm('⚠️ FINAL WARNING ⚠️\n\nYou are about to PERMANENTLY DELETE EVERYTHING for "' + companyName + '".\n\nThis includes:\n✗ Products\n✗ Users\n✗ Brands\n✗ Sales\n✗ Categories\n✗ Groups\n✗ Branches\n✗ POS Devices\n\nClick OK to continue or Cancel to abort.')) {
        return;
    }

    // Third confirmation - type the company name
    const nameConfirmation = prompt('Type the company name "' + companyName + '" to confirm permanent deletion:');
    if (nameConfirmation !== companyName) {
        alert('Deletion cancelled. Company name did not match.');
        return;
    }

    // Fourth confirmation - type REMOVE_ALL_DATA
    const finalConfirmation = prompt('Type "REMOVE_ALL_DATA" to confirm permanent deletion of all company data:');
    if (finalConfirmation !== 'REMOVE_ALL_DATA') {
        alert('Deletion cancelled. All data is safe.');
        return;
    }

    // Get CSRF token
    const csrfToken = document.querySelector('meta[name="csrf-token"]');
    if (!csrfToken) {
        alert('Error: CSRF token not found. Please refresh the page.');
        return;
    }

    // Show loading message
    const loadingAlert = alert('Processing... This may take a moment.');

    // Make the API call
    fetch('/admin/companies/' + companyId + '/remove-all-data', {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken.getAttribute('content'),
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            confirmation: 'REMOVE_ALL_DATA'
        })
    })
    .then(function(response) {
        return response.json();
    })
    .then(function(data) {
        if (data.success) {
            const stats = data.data.deleted;
            let message = 'Success!\n\n' + data.message + '\n\nDeleted:\n';
            message += '• ' + stats.sales + ' sales\n';
            message += '• ' + stats.sale_items + ' sale items\n';
            message += '• ' + stats.payments + ' payments\n';
            message += '• ' + stats.products + ' products\n';
            message += '• ' + stats.users + ' users\n';
            message += '• ' + stats.brands + ' brands\n';
            message += '• ' + stats.categories + ' categories\n';
            message += '• ' + stats.groups + ' groups\n';
            message += '• ' + stats.divisions + ' divisions\n';
            message += '• ' + stats.branches + ' branches\n';
            message += '• ' + stats.pos_devices + ' POS devices';

            alert(message);
            location.reload();
        } else {
            alert('Error: ' + (data.error || 'Unknown error occurred'));
        }
    })
    .catch(function(error) {
        console.error('Error:', error);
        alert('An error occurred while removing company data. Check console for details.');
    });
}
</script>
@endsection
