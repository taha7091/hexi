@extends('layouts.admin')

@section('title', 'Sales Management')

@section('content')
<div class="container-fluid">
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Sales Management</h1>
        <div>
            <a href="{{ route('admin.sales.pos') }}" class="btn btn-success shadow-sm">
                <i class="fas fa-cash-register mr-1"></i> POS Interface
            </a>
            <a href="{{ route('admin.sales.create') }}" class="btn btn-primary shadow-sm">
                <i class="fas fa-plus mr-1"></i> New Sale
            </a>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-lg-3 col-6">
            <div class="card border-left-info shadow-sm h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Today's Sales</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">${{ number_format($todaySales, 2) }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar-day fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="card border-left-success shadow-sm h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">This Week</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">${{ number_format($weekSales, 2) }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar-week fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="card border-left-warning shadow-sm h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">This Month</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">${{ number_format($monthSales, 2) }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar-alt fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="card border-left-primary shadow-sm h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Sales (Count)</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $totalSales }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-chart-line fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-filter mr-2"></i>Filter Sales</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.sales.index') }}" method="GET">
                <div class="row">
                    <div class="col-md-3 form-group">
                        <label for="date_from">From Date</label>
                        <input type="date" name="date_from" id="date_from" class="form-control" value="{{ request('date_from') }}">
                    </div>
                    <div class="col-md-3 form-group">
                        <label for="date_to">To Date</label>
                        <input type="date" name="date_to" id="date_to" class="form-control" value="{{ request('date_to') }}">
                    </div>
                    <div class="col-md-3 form-group">
                        <label for="status">Status</label>
                        <select name="status" id="status" class="form-control">
                            <option value="">All Status</option>
                            <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                    </div>
                    <div class="col-md-3 form-group">
                        <label for="search">Search</label>
                        <input type="text" name="search" id="search" class="form-control" placeholder="Sale #, Branch..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="row">
                    <div class="col-12 text-right">
                        <a href="{{ route('admin.sales.index') }}" class="btn btn-secondary">
                            <i class="fas fa-undo mr-1"></i> Clear
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search mr-1"></i> Apply Filters
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header">
            <h6 class="m-0 font-weight-bold">Sales History</h6>
        </div>
        <div class="card-body">
            @if($sales->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover" id="salesTable">
                        <thead>
                            <tr>
                                <th>Sale Info</th>
                                <th>Location</th>
                                <th>Items</th>
                                <th>Subtotal</th>
                                <th>Tax</th>
                                <th>Discount</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($sales as $sale)
                                <tr>
                                    <td>
                                        <a href="{{ route('admin.sales.show', $sale) }}" class="font-weight-bold">{{ $sale->sale_number }}</a>
                                        <div class="small text-muted">{{ $sale->sale_date->format('M d, Y H:i A') }}</div>
                                    </td>
                                    <td>
                                        <div>{{ $sale->branch->name }}</div>
                                        <div class="small text-muted">{{ $sale->posDevice->device_name }}</div>
                                    </td>
                                    <td>
                                        <span class="badge badge-pill badge-info">{{ $sale->saleItems->count() }}</span>
                                    </td>
                                    <td class="text-muted">${{ number_format($sale->subtotal, 2) }}</td>
                                    <td class="text-muted">${{ number_format($sale->tax_amount, 2) }}</td>
                                    <td class="text-muted">${{ number_format($sale->discount_amount, 2) }}</td>
                                    <td><strong>${{ number_format($sale->total_amount, 2) }}</strong></td>
                                    <td>
                                        @switch($sale->payment_status)
                                            @case('completed')
                                                <span class="badge badge-lg badge-pill badge-success">Completed</span>
                                                @break
                                            @case('pending')
                                                <span class="badge badge-lg badge-pill badge-warning">Pending</span>
                                                @break
                                            @case('cancelled')
                                                <span class="badge badge-lg badge-pill badge-danger">Cancelled</span>
                                                @break
                                            @default
                                                <span class="badge badge-lg badge-pill badge-secondary">{{ ucfirst($sale->payment_status) }}</span>
                                        @endswitch
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('admin.sales.show', $sale) }}" class="btn btn-sm btn-info" data-toggle="tooltip" title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.sales.receipt', $sale) }}" class="btn btn-sm btn-secondary" target="_blank" data-toggle="tooltip" title="Print Receipt">
                                                <i class="fas fa-receipt"></i>
                                            </a>
                                            @if($sale->payment_status !== 'completed')
                                                <a href="{{ route('admin.sales.edit', $sale) }}" class="btn btn-sm btn-warning" data-toggle="tooltip" title="Edit Sale">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-center mt-4">
                    {{ $sales->appends(request()->query())->links() }}
                </div>
                
            @else
                <div class="text-center py-5">
                    <i class="fas fa-search-dollar fa-4x text-gray-300 mb-3"></i>
                    <h5 class="text-muted">No Sales Found</h5>
                    <p class="text-muted">No sales match your current filter criteria.</p>
                </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    // Initialize Bootstrap tooltips
    $('[data-toggle="tooltip"]').tooltip();

    // Set correct 'selected' state for status filter on page load (if loaded via back button)
    // This is good practice as GET forms sometimes don't repopulate from 'value' attribute
    $('#status').val("{{ request('status') }}");
});
</script>
@endpush
@endsection