@extends('layouts.admin')
@section('title','Inventory Items')

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

    .filter-row {
        display: flex;
        gap: 10px;
        margin-bottom: 15px;
    }

    .filter-row input, .filter-row select {
        padding: 8px 12px;
        border-radius: 8px;
        border: 1px solid #e2e8f0;
    }
</style>
@endsection

@section('content')
<div class="header-actions">
    <h2>Inventory Items</h2>
    <a href="{{ route('admin.inventory-items.create') }}" class="btn btn-primary">
        <i style="margin-right:8px;">＋</i> New Item
    </a>
</div>

<div class="table-container">
    <form method="GET" class="filter-row">
        <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Search name/code/barcode">
        <button class="btn btn-secondary">Filter</button>
    </form>

    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Group</th>
                <th>Supplier</th>
                <th>Location</th>
                <th>Stock</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($items as $item)
                <tr>
                    <td>{{ $item->name }}</td>
                    <td>{{ $item->group->name ?? '-' }}</td>
                    <td>{{ $item->supplier->name ?? '-' }}</td>
                    <td>{{ $item->location->name ?? '-' }}</td>
                    <td>{{ $item->current_stock }} {{ $item->stockUnit->symbol ?? '' }}</td>
                    <td>
                        <a href="{{ route('admin.inventory-items.edit',$item) }}" class="btn btn-sm btn-primary">Edit</a>
                        <a href="{{ route('admin.inventory-items.history',$item) }}" class="btn btn-sm btn-secondary">History</a>
                        <a href="{{ route('admin.inventory-items.used-in',$item) }}" class="btn btn-sm btn-secondary">Used In ({{ $item->used_in_count ?? 0 }})</a>
                        <form action="{{ route('admin.inventory-items.destroy',$item) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this item?')">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-secondary">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="text-align:center; color:#718096;">No inventory items found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
    {{ $items->links() }}
</div>
@endsection
