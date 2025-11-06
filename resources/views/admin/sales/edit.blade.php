@extends('layouts.admin')

@section('title', 'Edit Sale')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Edit Sale - {{ $sale->sale_number }}</h3>
                    <div>
                        <a href="{{ route('admin.sales.show', $sale) }}" class="btn btn-info">
                            <i class="fas fa-eye"></i> View Sale
                        </a>
                        <a href="{{ route('admin.sales.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Sales
                        </a>
                    </div>
                </div>

                <form action="{{ route('admin.sales.update', $sale) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="card-body">
                        <div class="row">
                            <!-- Sale Information -->
                            <div class="col-md-6">
                                <h5 class="mb-3">Sale Information</h5>
                                
                                <div class="form-group">
                                    <label for="branch_id">Branch <span class="text-danger">*</span></label>
                                    <select name="branch_id" id="branch_id" class="form-control @error('branch_id') is-invalid @enderror" required>
                                        <option value="">Select Branch</option>
                                        @foreach($branches as $branch)
                                            <option value="{{ $branch->id }}" {{ old('branch_id', $sale->branch_id) == $branch->id ? 'selected' : '' }}>
                                                {{ $branch->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('branch_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="pos_device_id">POS Device <span class="text-danger">*</span></label>
                                    <select name="pos_device_id" id="pos_device_id" class="form-control @error('pos_device_id') is-invalid @enderror" required>
                                        <option value="">Select POS Device</option>
                                        @foreach($posDevices as $device)
                                            <option value="{{ $device->id }}" {{ old('pos_device_id', $sale->pos_device_id) == $device->id ? 'selected' : '' }}>
                                                {{ $device->device_name }} ({{ $device->branch->name }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('pos_device_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="payment_status">Payment Status <span class="text-danger">*</span></label>
                                    <select name="payment_status" id="payment_status" class="form-control @error('payment_status') is-invalid @enderror" required>
                                        <option value="pending" {{ old('payment_status', $sale->payment_status) == 'pending' ? 'selected' : '' }}>Pending</option>
                                        <option value="completed" {{ old('payment_status', $sale->payment_status) == 'completed' ? 'selected' : '' }}>Completed</option>
                                        <option value="cancelled" {{ old('payment_status', $sale->payment_status) == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                    </select>
                                    @error('payment_status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="notes">Notes</label>
                                    <textarea name="notes" id="notes" class="form-control @error('notes') is-invalid @enderror" 
                                              rows="3">{{ old('notes', $sale->notes) }}</textarea>
                                    @error('notes')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Payment Information -->
                            <div class="col-md-6">
                                <h5 class="mb-3">Payment Information</h5>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="tax_amount">Tax Amount</label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text">$</span>
                                                </div>
                                                <input type="number" name="tax_amount" id="tax_amount" class="form-control @error('tax_amount') is-invalid @enderror" 
                                                       value="{{ old('tax_amount', $sale->tax_amount) }}" step="0.01" min="0">
                                            </div>
                                            @error('tax_amount')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="discount_amount">Discount Amount</label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text">$</span>
                                                </div>
                                                <input type="number" name="discount_amount" id="discount_amount" class="form-control @error('discount_amount') is-invalid @enderror" 
                                                       value="{{ old('discount_amount', $sale->discount_amount) }}" step="0.01" min="0">
                                            </div>
                                            @error('discount_amount')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <!-- Current Totals (Read-only) -->
                                <div class="card border-info">
                                    <div class="card-header bg-info text-white">
                                        <h6 class="mb-0">Current Sale Totals</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-6">
                                                <strong>Subtotal:</strong><br>
                                                <span class="text-muted">${{ number_format($sale->subtotal, 2) }}</span>
                                            </div>
                                            <div class="col-6">
                                                <strong>Total:</strong><br>
                                                <span class="text-primary h5">${{ number_format($sale->total_amount, 2) }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Payment Methods -->
                                @if($sale->payments->count() > 0)
                                    <div class="mt-3">
                                        <h6>Payment Methods</h6>
                                        <div class="table-responsive">
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
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Update Sale
                        </button>
                        <a href="{{ route('admin.sales.show', $sale) }}" class="btn btn-info">
                            <i class="fas fa-eye"></i> View Sale
                        </a>
                        <a href="{{ route('admin.sales.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Sale Items (Read-only) -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Sale Items (Read-only)</h4>
                    <small class="text-muted">To modify items, create a new sale or use the POS interface</small>
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
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
