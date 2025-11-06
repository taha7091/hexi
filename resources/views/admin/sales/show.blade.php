@extends('layouts.admin')

@section('title', 'Sale Details')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Sale Details - {{ $sale->sale_number }}</h3>
                    <div>
                        <a href="{{ route('admin.sales.receipt', $sale) }}" class="btn btn-secondary" target="_blank">
                            <i class="fas fa-receipt"></i> Print Receipt
                        </a>
                        @if($sale->payment_status !== 'completed')
                            <a href="{{ route('admin.sales.edit', $sale) }}" class="btn btn-warning">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                        @endif
                        <a href="{{ route('admin.sales.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Sales
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h5>Sale Information</h5>
                            <table class="table table-borderless">
                                <tr>
                                    <th width="30%">Sale Number:</th>
                                    <td>{{ $sale->sale_number }}</td>
                                </tr>
                                <tr>
                                    <th>Date:</th>
                                    <td>{{ $sale->sale_date->format('M d, Y H:i') }}</td>
                                </tr>
                                <tr>
                                    <th>Branch:</th>
                                    <td>{{ $sale->branch->name }}</td>
                                </tr>
                                <tr>
                                    <th>POS Device:</th>
                                    <td>{{ $sale->posDevice->device_name }}</td>
                                </tr>
                                <tr>
                                    <th>Cashier:</th>
                                    <td>{{ $sale->user->name }}</td>
                                </tr>
                                <tr>
                                    <th>Payment Status:</th>
                                    <td>
                                        @switch($sale->payment_status)
                                            @case('completed')
                                                <span class="badge badge-success">Completed</span>
                                                @break
                                            @case('pending')
                                                <span class="badge badge-warning">Pending</span>
                                                @break
                                            @case('cancelled')
                                                <span class="badge badge-danger">Cancelled</span>
                                                @break
                                            @default
                                                <span class="badge badge-secondary">{{ ucfirst($sale->payment_status) }}</span>
                                        @endswitch
                                    </td>
                                </tr>
                                @if($sale->notes)
                                    <tr>
                                        <th>Notes:</th>
                                        <td>{{ $sale->notes }}</td>
                                    </tr>
                                @endif
                            </table>
                        </div>

                        <div class="col-md-6">
                            <h5>Amount Summary</h5>
                            <table class="table table-borderless">
                                <tr>
                                    <th width="30%">Subtotal:</th>
                                    <td>${{ number_format($sale->subtotal, 2) }}</td>
                                </tr>
                                <tr>
                                    <th>Tax Amount:</th>
                                    <td>${{ number_format($sale->tax_amount, 2) }}</td>
                                </tr>
                                <tr>
                                    <th>Discount:</th>
                                    <td>${{ number_format($sale->discount_amount, 2) }}</td>
                                </tr>
                                <tr class="border-top">
                                    <th><strong>Total Amount:</strong></th>
                                    <td><strong>${{ number_format($sale->total_amount, 2) }}</strong></td>
                                </tr>
                            </table>

                            @if($sale->payments->count() > 0)
                                <h5 class="mt-4">Payment Details</h5>
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Method</th>
                                            <th>Amount</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($sale->payments as $payment)
                                            <tr>
                                                <td>{{ ucfirst($payment->method) }}</td>
                                                <td>${{ number_format($payment->amount, 2) }}</td>
                                                <td>
                                                    <span class="badge badge-{{ $payment->status === 'completed' ? 'success' : 'warning' }}">
                                                        {{ ucfirst($payment->status) }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Sale Items -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Sale Items</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>SKU</th>
                                    <th>Unit Price</th>
                                    <th>Quantity</th>
                                    <th>Discount</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($sale->saleItems as $item)
                                    <tr>
                                        <td>
                                            @if($item->product->image_url)
                                                <img src="{{ $item->product->image_url }}" alt="{{ $item->product->name }}" 
                                                     class="img-thumbnail me-2" style="width: 40px; height: 40px;">
                                            @endif
                                            {{ $item->product->name }}
                                        </td>
                                        <td>{{ $item->product->sku }}</td>
                                        <td>${{ number_format($item->unit_price, 2) }}</td>
                                        <td>{{ $item->quantity }}</td>
                                        <td>${{ number_format($item->discount_amount, 2) }}</td>
                                        <td><strong>${{ number_format($item->total_price, 2) }}</strong></td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr class="table-info">
                                    <th colspan="5">Total Items: {{ $sale->saleItems->sum('quantity') }}</th>
                                    <th>${{ number_format($sale->subtotal, 2) }}</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
