@extends('layouts.admin')
@section('title','Purchase #'.$purchase->id)

@section('styles')
<style>
    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
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
        background: linear-gradient(135deg,#667eea,#764ba2);
        color: #fff;
    }

    .btn-secondary {
        background: #f1f1f1;
        color: #2c3e50;
        border: 1px solid #ccc;
    }

    .details-container {
        background: white;
        border-radius: 15px;
        padding: 25px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        margin-top: 15px;
        margin-bottom: 25px;
    }

    .info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 15px;
    }

    .info-box {
        background: #f7f9fc;
        border-radius: 10px;
        padding: 12px 15px;
        line-height: 1.5;
    }

    .info-label {
        font-size: 13px;
        color: #718096;
        font-weight: 600;
    }

    .info-value {
        font-size: 15px;
        color: #2d3748;
        font-weight: 500;
    }

    .notes {
        background: #edf2f7;
        border-radius: 10px;
        padding: 12px;
        color: #2d3748;
        font-size: 14px;
        margin-top: 15px;
    }

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

    tfoot th {
        font-weight: 600;
    }

    .text-right {
        text-align: right;
    }

    .total-row {
        background: #f0f4ff;
        font-weight: 700;
        color: #2d3748;
    }
</style>
@endsection

@section('content')
<div class="page-header">
    <h2>Purchase Details</h2>
    <a href="{{ route('admin.inventory-purchases.index') }}" class="btn btn-secondary">← Back</a>
</div>

<div class="details-container">
    <div class="info-grid">
        <div class="info-box">
            <div class="info-label">Invoice #</div>
            <div class="info-value">{{ $purchase->invoice_number }}</div>
        </div>
        <div class="info-box">
            <div class="info-label">Date</div>
            <div class="info-value">{{ optional($purchase->invoice_date)->format('Y-m-d') }}</div>
        </div>
        <div class="info-box">
            <div class="info-label">Supplier</div>
            <div class="info-value">{{ $purchase->supplier->name ?? '-' }}</div>
        </div>
        <div class="info-box">
            <div class="info-label">Location</div>
            <div class="info-value">{{ $purchase->location->name ?? '-' }}</div>
        </div>
    </div>

    @if($purchase->notes)
        <div class="notes"><strong>Notes:</strong> {{ $purchase->notes }}</div>
    @endif
</div>

<div class="table-container">
    <table>
        <thead>
            <tr>
                <th>Inventory Item</th>
                <th class="text-right">Qty (Buying)</th>
                <th class="text-right">Unit Cost</th>
                <th class="text-right">Line Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($purchase->items as $it)
                <tr>
                    <td>{{ $it->inventoryItem->name ?? ('#'.$it->inventory_item_id) }}</td>
                    <td class="text-right">{{ number_format($it->quantity_buying, 6) }}</td>
                    <td class="text-right">{{ number_format($it->unit_cost, 2) }}</td>
                    <td class="text-right">{{ number_format($it->line_total, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <th colspan="3" class="text-right">Subtotal</th>
                <th class="text-right">{{ number_format($purchase->subtotal, 2) }}</th>
            </tr>
            <tr>
                <th colspan="3" class="text-right">Tax</th>
                <th class="text-right">{{ number_format($purchase->tax_amount, 2) }}</th>
            </tr>
            <tr class="total-row">
                <th colspan="3" class="text-right">Total</th>
                <th class="text-right">{{ number_format($purchase->total_amount, 2) }}</th>
            </tr>
        </tfoot>
    </table>
</div>
@endsection
