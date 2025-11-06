@extends('layouts.admin')
@section('title', 'Purchase Entries')

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
        background: linear-gradient(135deg, #667eea, #764ba2);
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

    .amount {
        text-align: right;
        font-weight: 500;
    }

    .amount-total {
        font-weight: 700;
        color: #2d3748;
    }
</style>
@endsection

@section('content')
<div class="header-actions">
    <h2>Purchase Entries</h2>
    <a href="{{ route('admin.inventory-purchases.create') }}" class="btn btn-primary">
        <i style="margin-right:8px;">＋</i> New Purchase
    </a>
</div>

<div class="table-container">
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Invoice</th>
                <th>Date</th>
                <th>Supplier</th>
                <th>Location</th>
                <th class="amount">Subtotal</th>
                <th class="amount">Tax</th>
                <th class="amount">Total</th>
                <th style="text-align:right;">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($purchases as $p)
                <tr>
                    <td>{{ $p->id }}</td>
                    <td>{{ $p->invoice_number }}</td>
                    <td>{{ optional($p->invoice_date)->format('Y-m-d') }}</td>
                    <td>{{ $p->supplier->name ?? '-' }}</td>
                    <td>{{ $p->location->name ?? '-' }}</td>
                    <td class="amount">{{ number_format($p->subtotal, 2) }}</td>
                    <td class="amount">{{ number_format($p->tax_amount, 2) }}</td>
                    <td class="amount amount-total">{{ number_format($p->total_amount, 2) }}</td>
                    <td style="text-align:right;">
                        <a href="{{ route('admin.inventory-purchases.show', $p) }}" class="btn btn-sm btn-primary">View</a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" style="text-align:center; color:#718096;">No purchases found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{ $purchases->links() }}
</div>
@endsection
