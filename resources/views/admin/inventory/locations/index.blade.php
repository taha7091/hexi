@extends('layouts.admin')
@section('title', 'Locations')

@section('styles')
<style>
    .table-container {
        background: white;
        border-radius: 15px;
        padding: 20px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        margin-top: 20px;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        font-size: 14px;
    }

    thead {
        background: #667eea;
        color: white;
    }

    th, td {
        padding: 12px 15px;
        text-align: left;
        border-bottom: 1px solid #e2e8f0;
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
        justify-content: space-between;
        align-items: center;
        margin-bottom: 15px;
    }

    .btn {
        padding: 8px 15px;
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
        font-size: 14px;
        text-decoration: none;
        border: none;
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

    .btn-sm {
        padding: 5px 10px;
        font-size: 12px;
    }
</style>
@endsection

@section('content')
<div class="header-actions">
    <h2>Locations</h2>
    <a href="{{ route('admin.inventory-locations.create') }}" class="btn btn-primary">
        <i style="margin-right: 8px;">＋</i> New Location
    </a>
</div>

<div class="table-container">
    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Type</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($locations as $location)
                <tr>
                    <td>{{ $location->name }}</td>
                    <td>{{ $location->type ?? '-' }}</td>
                    <td>
                        <span class="status-badge {{ $location->is_active ? 'status-active' : 'status-inactive' }}">
                            {{ $location->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                    <td>
                        <a href="{{ route('admin.inventory-locations.edit', $location) }}" class="btn btn-sm btn-primary">Edit</a>
                        <form action="{{ route('admin.inventory-locations.destroy', $location) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this location?')">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-secondary">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" style="text-align:center; color:#718096;">No locations found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
