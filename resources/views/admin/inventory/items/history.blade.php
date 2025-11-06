@extends('layouts.admin')
@section('title', 'Item History')

@section('styles')
<style>
    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 25px;
    }

    .page-header h2 {
        font-size: 22px;
        font-weight: 700;
        color: #2d3748;
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
        background: linear-gradient(135deg, #667eea, #764ba2);
        color: #fff;
    }

    .btn-secondary {
        background: #f1f1f1;
        color: #2c3e50;
        border: 1px solid #ccc;
    }

    .filter-container {
        background: white;
        border-radius: 15px;
        padding: 20px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        margin-bottom: 25px;
    }

    .filter-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 15px;
    }

    label {
        font-size: 13px;
        color: #4a5568;
        font-weight: 600;
        display: block;
        margin-bottom: 5px;
    }

    select, input[type="date"] {
        width: 100%;
        padding: 10px;
        border-radius: 8px;
        border: 1px solid #cbd5e0;
        font-size: 14px;
        color: #2d3748;
    }

    .btn-group {
        display: flex;
        gap: 10px;
        margin-top: 10px;
    }

    .table-container {
        background: white;
        border-radius: 15px;
        padding: 20px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.08);
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

    .text-end {
        text-align: right;
    }

    .badge {
        display: inline-block;
        padding: 5px 10px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        text-transform: capitalize;
    }

    .bg-success {
        background: #c6f6d5;
        color: #22543d;
    }

    .bg-danger {
        background: #fed7d7;
        color: #742a2a;
    }

    .bg-secondary {
        background: #e2e8f0;
        color: #2d3748;
    }

    .no-data {
        text-align: center;
        color: #718096;
        padding: 25px 0;
    }

    .pagination-container {
        margin-top: 15px;
        text-align: right;
    }
</style>
@endsection

@section('content')
<div class="page-header">
    <h2>History: {{ $inventory_item->name }}</h2>
    <a href="{{ route('admin.inventory-items.index') }}" class="btn btn-secondary">← Back to Items</a>
</div>

<div class="filter-container">
    <form method="GET" action="{{ route('admin.inventory-items.history', $inventory_item) }}">
        <div class="filter-grid">
            <div>
                <label>Location</label>
                <select name="location_id">
                    <option value="">All locations</option>
                    @foreach($locations as $loc)
                        <option value="{{ $loc->id }}" @selected(request('location_id') == $loc->id)>{{ $loc->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label>Date From</label>
                <input type="date" name="date_from" value="{{ request('date_from') }}">
            </div>
            <div>
                <label>Date To</label>
                <input type="date" name="date_to" value="{{ request('date_to') }}">
            </div>
            <div>
                <label>Type</label>
                <select name="type">
                    <option value="adjustments" @selected(($type ?? request('type','adjustments'))==='adjustments')>Adjustments only</option>
                    <option value="purchases" @selected(($type ?? request('type'))==='purchases')>Purchases only</option>
                    <option value="deductions" @selected(($type ?? request('type'))==='deductions')>Deductions only</option>
                    <option value="all" @selected(($type ?? request('type'))==='all')>All</option>
                </select>
            </div>
        </div>
        <div class="btn-group">
            <button class="btn btn-primary">Filter</button>
            <a href="{{ route('admin.inventory-items.history', $inventory_item) }}" class="btn btn-secondary">Reset</a>
        </div>
    </form>
</div>

<div class="table-container">
    <table>
        <thead>
            <tr>
                <th>Adj #</th>
                <th>Date</th>
                <th>Location</th>
                <th>Type</th>
                <th class="text-end">Before</th>
                <th class="text-end">Change</th>
                <th class="text-end">After</th>
                <th>Reason</th>
                <th>User</th>
            </tr>
        </thead>
        <tbody>
            @forelse($adjustments as $adj)
                <tr>
                    <td><a href="{{ route('admin.inventory-adjustments.show', $adj) }}">{{ $adj->number ?? ('ADJ-' . str_pad($adj->id, 6, '0', STR_PAD_LEFT)) }}</a></td>
                    <td>{{ $adj->created_at->format('Y-m-d H:i') }}</td>
                    <td>{{ $adj->location->name ?? '-' }}</td>
                    <td>
                        <span class="badge bg-{{ $adj->adjustment_type === 'add' ? 'success' : ($adj->adjustment_type === 'subtract' ? 'danger' : 'secondary') }}">
                            {{ ucfirst($adj->adjustment_type) }}
                        </span>
                    </td>
                    <td class="text-end">{{ number_format($adj->quantity_before, 6) }}</td>
                    <td class="text-end">{{ number_format($adj->adjustment_amount, 6) }}</td>
                    <td class="text-end">{{ number_format($adj->quantity_after, 6) }}</td>
                    <td>{{ $adj->reason }}</td>
                    <td>{{ $adj->user->name ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="no-data">No history found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
    <div class="pagination-container">
        {{ $adjustments->links() }}
    </div>
</div>
@endsection
