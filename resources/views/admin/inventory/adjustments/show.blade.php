@extends('layouts.admin')

@section('title', 'Adjustment Details - ERP System')
@section('page-title', 'Adjustment Details')
@section('breadcrumb', 'Home > Admin > Adjustments')

@section('styles')
<style>
    .table-container, .card {
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
        vertical-align: middle;
    }

    tbody tr:hover {
        background: #f7f9fc;
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

    .btn-secondary, .btn-outline-secondary {
        background: #f1f1f1;
        color: #2c3e50;
        border: 1px solid #ccc;
    }

    .fw-bold {
        font-weight: 600;
        color: #2d3748;
        margin-bottom: 5px;
    }

    .status-badge {
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .status-positive {
        background: #c6f6d5;
        color: #22543d;
    }

    .status-negative {
        background: #fed7d7;
        color: #742a2a;
    }

    @media (max-width: 768px) {
        .row.g-3 > [class*='col-'] {
            margin-bottom: 15px;
        }
    }
</style>
@endsection

@section('content')
<div class="header-actions">
    <h2>Adjustment {{ $inventory_adjustment->number ?? ('ADJ-' . str_pad($inventory_adjustment->id, 6, '0', STR_PAD_LEFT)) }}</h2>
    <a href="{{ route('admin.inventory-adjustments.index') }}" class="btn btn-secondary">Back</a>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<div class="table-container">
    <table>
        <thead>
            <tr>
                <th>Item</th>
                <th>Location</th>
                <th>Type</th>
                <th>Before</th>
                <th>Change</th>
                <th>After</th>
                <th>Date</th>
                <th>Reason</th>
                <th>User</th>
                <th>Notes</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ $inventory_adjustment->inventoryItem->name ?? '#' }}</td>
                <td>{{ $inventory_adjustment->location->name ?? '-' }}</td>
                <td>{{ ucfirst($inventory_adjustment->adjustment_type) }}</td>
                <td>{{ number_format($inventory_adjustment->quantity_before, 6) }}</td>
                <td>
                    <span class="status-badge {{ $inventory_adjustment->adjustment_amount >= 0 ? 'status-positive' : 'status-negative' }}">
                        {{ number_format($inventory_adjustment->adjustment_amount, 6) }}
                    </span>
                </td>
                <td>{{ number_format($inventory_adjustment->quantity_after, 6) }}</td>
                <td>{{ $inventory_adjustment->created_at->format('Y-m-d H:i') }}</td>
                <td>{{ $inventory_adjustment->reason ?? '-' }}</td>
                <td>{{ $inventory_adjustment->user->name ?? '-' }}</td>
                <td>{{ $inventory_adjustment->notes ?? '-' }}</td>
            </tr>
        </tbody>
    </table>
</div>
@endsection
