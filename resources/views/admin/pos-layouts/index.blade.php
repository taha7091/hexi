@extends('layouts.admin')

@section('title', 'POS Layouts Management - ERP System')
@section('page-title', 'POS Layouts Management')
@section('breadcrumb', 'Home > Admin > POS Layouts')

@section('styles')
<style>
    .table-container {
        background: white;
        border-radius: 15px;
        padding: 20px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        margin-top: 20px;
        overflow-x: auto;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        font-size: 14px;
        color: #2d3748;
    }

    thead {
        background: #667eea;
        color: white;
    }

    th, td {
        padding: 12px 15px;
        vertical-align: middle;
        border-bottom: 1px solid #e2e8f0;
        text-align: left;
    }

    tbody tr:hover {
        background: #f7f9fc;
    }

    .status-badge {
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .status-active {
        background: #c6f6d5;
        color: #22543d;
    }

    .status-inactive {
        background: #fed7d7;
        color: #742a2a;
    }

    .header-actions {
        display: flex;
        justify-content: flex-end;
        align-items: center;
        margin-bottom: 15px;
    }

    .btn, .table-actions .btn {
        padding: 6px 12px;
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
        font-size: 14px;
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
        border: none;
        transition: all 0.2s ease;
    }

    .btn:hover, .table-actions .btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 2px 8px rgba(0,0,0,0.12);
    }

    .btn-primary {
        background: linear-gradient(135deg,#667eea,#764ba2);
        color: #fff;
    }

    .btn-secondary {
        background: #f1f1f1;
        color: #2c3e50;
        border: 1px solid #ccc;
    }

    .table-actions {
        display: flex;
        gap: 0.3rem;
        flex-wrap: wrap;
    }

    .filters {
        background: white;
        padding: 20px;
        border-radius: 15px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        margin-bottom: 20px;
    }

    .filter-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 15px;
        align-items: end;
    }

    .filter-group {
        display: flex;
        flex-direction: column;
    }

    .filter-group label {
        font-weight: 600;
        color: #2d3748;
        margin-bottom: 5px;
    }

    .filter-group select, .filter-group input {
        width: 100%;
        padding: 10px;
        border-radius: 8px;
        border: 2px solid #e2e8f0;
        font-size: 14px;
    }

    .empty-state {
        text-align: center;
        padding: 2rem;
        color: #718096;
    }

    .empty-state i {
        font-size: 3rem;
        margin-bottom: 1rem;
        color: #cbd5e0;
        display: block;
    }
</style>
@endsection

@section('content')
<div class="header-actions">
    <a href="{{ route('admin.pos-layouts.create') }}" class="btn btn-primary">
        <i class="fas fa-plus"></i> Add New Layout
    </a>
</div>

<!-- Filters (unchanged) -->
<div class="filters">
    <div class="filter-row">
        <div class="filter-group">
            <label for="brandFilter">Brand</label>
            <select id="brandFilter">
                <option value="">All Brands</option>
                @foreach($brands as $brand)
                    <option value="{{ $brand->id }}">{{ $brand->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="filter-group">
            <label for="statusFilter">Status</label>
            <select id="statusFilter">
                <option value="">All Status</option>
                <option value="1">Active</option>
                <option value="0">Inactive</option>
            </select>
        </div>
        <div class="filter-group">
            <label for="searchInput">Search</label>
            <input type="text" id="searchInput" placeholder="Search layouts...">
        </div>
    </div>
</div>

<div class="table-container">
    @if($layouts->count() > 0)
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Brand</th>
                    <th>Description</th>
                    <th>Default</th>
                    <th>Groups</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($layouts as $layout)
                    <tr data-brand-id="{{ $layout->brand_id }}">
                        <td>
                            <strong>{{ $layout->name }}</strong>
                            @if($layout->is_default)
                                <span class="status-badge status-active ml-2">Default</span>
                            @endif
                        </td>
                        <td>{{ $layout->brand->name }}</td>
                        <td>{{ $layout->description ?? 'No description' }}</td>
                        <td>
                            <span class="status-badge {{ $layout->is_default ? 'status-active' : 'status-inactive' }}">
                                {{ $layout->is_default ? 'Yes' : 'No' }}
                            </span>
                        </td>
                        <td>
                            <span class="status-badge status-active">{{ $layout->groups_count ?? 0 }}</span>
                        </td>
                        <td>{{ $layout->created_at->format('M d, Y') }}</td>
                        <td>
                            <div class="table-actions">
                                <a href="{{ route('admin.pos-layouts.show', $layout) }}" class="btn btn-secondary">View</a>
                                <a href="{{ route('admin.pos-layouts.edit', $layout) }}" class="btn btn-primary">Edit</a>
                                @if(!$layout->is_default)
                                    <form action="{{ route('admin.pos-layouts.destroy', $layout) }}" method="POST" onsubmit="return confirm('Are you sure?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-secondary">Delete</button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="pagination-container mt-3">
            {{ $layouts->links() }}
        </div>
    @else
        <div class="empty-state">
            <i class="fas fa-th-large"></i>
            <h5>No POS Layouts Found</h5>
            <p>Start by creating your first POS layout.</p>
            <a href="{{ route('admin.pos-layouts.create') }}" class="btn btn-primary">Create Layout</a>
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Search
    $('#searchInput').on('keyup', function() {
        var value = $(this).val().toLowerCase();
        $('table tbody tr').filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
        });
    });

    // Brand filter
    $('#brandFilter').on('change', function() {
        var brandId = $(this).val();
        $('table tbody tr').each(function() {
            if (brandId === '' || $(this).data('brand-id') == brandId) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    });

    // Status filter
    $('#statusFilter').on('change', function() {
        var status = $(this).val();
        $('table tbody tr').each(function() {
            var isDefault = $(this).find('.status-active').length > 0;
            if (status === '' || (status == '1' && isDefault) || (status == '0' && !isDefault)) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    });
});
</script>
@endpush
