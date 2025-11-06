@extends('layouts.admin')

@section('title', 'Brands Management - ERP System')

@section('page-title', 'Brands Management')

@section('breadcrumb', 'Home > Admin > Brands')

@section('styles')
<style>
    /* General Styles */
    body {
        background-color: #f4f6f9;
        color: #333;
    }

    .btn {
        padding: 10px 15px;
        border-radius: 8px;
        text-decoration: none;
        font-size: 14px;
        font-weight: 600;
        transition: all 0.3s ease;
        cursor: pointer;
        border: none;
    }

    .btn-primary {
        background-color: #007bff;
        color: white;
    }

    .btn-primary:hover {
        background-color: #0056b3;
    }

    .btn-success {
        background-color: #28a745;
        color: white;
    }

    .btn-success:hover {
        background-color: #218838;
    }
    
    .btn-secondary {
        background-color: #6c757d;
        color: white;
    }

    .btn-warning {
        background-color: #ffc107;
        color: #212529;
    }

    .btn-danger {
        background-color: #dc3545;
        color: white;
    }

    /* Modal Styles */
    .modal {
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0,0,0,0.5);
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .modal-content {
        background-color: #fefefe;
        padding: 0;
        border-radius: 12px;
        width: 90%;
        max-width: 600px;
        max-height: 90vh;
        overflow-y: auto;
        box-shadow: 0 10px 30px rgba(0,0,0,0.3);
    }

    .modal-header {
        padding: 20px 25px;
        border-bottom: 1px solid #e9ecef;
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: linear-gradient(135deg, #007bff, #0056b3);
        color: white;
        border-radius: 12px 12px 0 0;
    }

    .modal-header h3 {
        margin: 0;
        font-size: 18px;
    }

    .modal-body {
        padding: 25px;
    }

    .modal-footer {
        padding: 20px 25px;
        border-top: 1px solid #e9ecef;
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        background: #f8f9fa;
        border-radius: 0 0 12px 12px;
    }

    .close {
        color: white;
        font-size: 28px;
        font-weight: bold;
        cursor: pointer;
    }

    /* Form Styles */
    .form-group {
        margin-bottom: 20px;
    }

    .form-group label {
        display: block;
        margin-bottom: 8px;
        font-weight: 600;
    }

    .form-control {
        width: 100%;
        padding: 12px 16px;
        border: 1px solid #ced4da;
        border-radius: 8px;
        font-size: 14px;
    }

    /* Filters */
    .filters {
        background: #fff;
        padding: 25px;
        border-radius: 12px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        margin-bottom: 25px;
    }

    .filters form {
        display: flex;
        align-items: center;
        gap: 15px;
    }

    /* Table */
    .table-container {
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        overflow-x: auto; /* Important for responsiveness */
    }

    table {
        width: 100%;
        border-collapse: collapse;
    }

    th, td {
        padding: 15px;
        text-align: left;
        border-bottom: 1px solid #f1f3f4;
        white-space: nowrap; /* Prevent content from wrapping */
    }

    th {
        background: #f8f9fa;
        font-weight: 600;
        font-size: 13px;
        text-transform: uppercase;
    }

    /* Header */
    .header-actions {
        display: flex;
        gap: 15px;
        margin-bottom: 25px;
        align-items: center;
        flex-wrap: wrap; /* Allow items to wrap on smaller screens */
    }

    /* Stats Cards */
    .stats-cards {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }

    .stat-card {
        background: white;
        padding: 20px;
        border-radius: 12px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        text-align: center;
    }

    .stat-number {
        font-size: 28px;
        font-weight: bold;
    }

    .stat-label {
        font-size: 13px;
        color: #6c757d;
        text-transform: uppercase;
    }

    /* Responsive Media Queries */
    @media (max-width: 768px) {
        .filters form {
            flex-direction: column;
            align-items: stretch;
        }

        .filters select, .filters input, .filters button {
            width: 100%;
            margin-right: 0;
        }

        .header-actions {
            flex-direction: column;
            align-items: stretch;
        }

        .header-actions .btn {
            width: 100%;
            text-align: center;
        }
    }

</style>
@endsection

@section('content')
<!-- Add a note to the user about the viewport meta tag -->
{{-- Please ensure your main layout file (e.g., resources/views/layouts/admin.blade.php) includes the following line in the <head> section for proper mobile responsiveness: --}}
{{-- <meta name="viewport" content="width=device-width, initial-scale=1.0"> --}}

<div class="header-actions">
    <a href="{{ route('admin.brands.create') }}" class="btn btn-primary">+ Add New Brand</a>
    @if(Auth::user()->isAdmin() || Auth::user()->isMasterAdmin())
        <button onclick="showDefaultDataModal()" class="btn btn-success">Add Brand Default Data</button>
    @endif
</div>

<!-- Filters -->
<div class="filters">
    <form method="GET">
        <select name="company_id" class="form-control" onchange="this.form.submit()">
            <option value="">All Companies</option>
            @foreach($companies as $company)
                <option value="{{ $company->id }}" {{ request('company_id') == $company->id ? 'selected' : '' }}>
                    {{ $company->name }}
                </option>
            @endforeach
        </select>
        <input type="text" name="search" class="form-control" placeholder="Search brands..." value="{{ request('search') }}">
        <button type="submit" class="btn btn-primary">Search</button>
        @if(request()->hasAny(['company_id', 'search']))
            <a href="{{ route('admin.brands.index') }}" class="btn btn-secondary">Clear</a>
        @endif
    </form>
</div>

<div class="table-container">
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Brand Name</th>
                <th>Company</th>
                <th>Categories</th>
                <th>Branches</th>
                <th>Products</th>
                <th>Created</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($brands as $brand)
                <tr>
                    <td>{{ $brand->id }}</td>
                    <td><strong>{{ $brand->name }}</strong></td>
                    <td>{{ $brand->company->name }}</td>
                    <td>{{ $brand->categories->count() }}</td>
                    <td>{{ $brand->branches->count() }}</td>
                    <td>
                        @php
                            $productCount = $brand->categories->sum(function($category) {
                                return $category->divisions->sum(function($division) {
                                    return $division->groups->sum(function($group) {
                                        return $group->products->count();
                                    });
                                });
                            });
                        @endphp
                        {{ $productCount }}
                    </td>
                    <td>{{ $brand->created_at->format('M d, Y') }}</td>
                    <td>
                        <a href="{{ route('admin.brands.show', $brand) }}" class="btn btn-primary">View</a>
                        <a href="{{ route('admin.brands.edit', $brand) }}" class="btn btn-warning">Edit</a>
                        <form method="POST" action="{{ route('admin.brands.destroy', $brand) }}" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this brand?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" style="text-align: center; padding: 40px;">
                        No brands found.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($brands->hasPages())
    <div style="margin-top: 30px; text-align: center;">
        {{ $brands->appends(request()->query())->links() }}
    </div>
@endif

<!-- Quick Stats -->
<div class="stats-cards" style="margin-top: 30px;">
    <div class="stat-card">
        <div class="stat-number">{{ $brands->total() }}</div>
        <div class="stat-label">Total Brands</div>
    </div>
    <div class="stat-card">
        <div class="stat-number">{{ $companies->count() }}</div>
        <div class="stat-label">Companies</div>
    </div>
    <div class="stat-card">
        <div class="stat-number">
            {{ $brands->sum(function($brand) { return $brand->categories->count(); }) }}
        </div>
        <div class="stat-label">Total Categories</div>
    </div>
    <div class="stat-card">
        <div class="stat-number">
            {{ $brands->sum(function($brand) { return $brand->branches->count(); }) }}
        </div>
        <div class="stat-label">Total Branches</div>
    </div>
</div>

<!-- Add Brand Default Data Modal -->
@if(Auth::user()->isAdmin() || Auth::user()->isMasterAdmin())
<div id="defaultDataModal" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Add Brand Default Data</h3>
            <span class="close" onclick="closeDefaultDataModal()">&times;</span>
        </div>
        <div class="modal-body">
            <p>This will create a complete brand structure with default categories, divisions, groups, and sample products for the selected company.</p>
            <form id="defaultDataForm" method="POST" action="{{ route('admin.brands.add-default-data') }}">
                @csrf
                <div class="form-group">
                    <label for="modal_company_id">Company ID:</label>
                    <input type="number" name="company_id" id="modal_company_id" required class="form-control" placeholder="Enter Company ID">
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button onclick="closeDefaultDataModal()" class="btn btn-secondary">Cancel</button>
            <button onclick="submitDefaultData()" class="btn btn-success">Create Default Data</button>
        </div>
    </div>
</div>

<script>
function showDefaultDataModal() {
    document.getElementById('defaultDataModal').style.display = 'flex';
}

function closeDefaultDataModal() {
    document.getElementById('defaultDataModal').style.display = 'none';
}

function submitDefaultData() {
    document.getElementById('defaultDataForm').submit();
}

document.addEventListener('click', function(event) {
    const modal = document.getElementById('defaultDataModal');
    if (event.target === modal) {
        closeDefaultDataModal();
    }
});
</script>
@endif

@endsection