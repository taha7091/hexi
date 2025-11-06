@extends('layouts.admin')

@section('title', 'Create Sale')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Create New Sale</h3>
                    <div>
                        <a href="{{ route('admin.sales.pos') }}" class="btn btn-success">
                            <i class="fas fa-cash-register"></i> Use POS Interface
                        </a>
                        <a href="{{ route('admin.sales.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Sales
                        </a>
                    </div>
                </div>

                <form action="{{ route('admin.sales.store') }}" method="POST" id="saleForm">
                    @csrf
                    
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
                                            <option value="{{ $branch->id }}" {{ old('branch_id') == $branch->id ? 'selected' : '' }}>
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
                                            <option value="{{ $device->id }}" {{ old('pos_device_id') == $device->id ? 'selected' : '' }}>
                                                {{ $device->device_name }} ({{ $device->branch->name }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('pos_device_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="notes">Notes</label>
                                    <textarea name="notes" id="notes" class="form-control @error('notes') is-invalid @enderror" 
                                              rows="3">{{ old('notes') }}</textarea>
                                    @error('notes')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Payment Information -->
                            <div class="col-md-6">
                                <h5 class="mb-3">Payment Information</h5>
                                
                                <div class="form-group">
                                    <label for="payment_method">Payment Method <span class="text-danger">*</span></label>
                                    <select name="payment_method" id="payment_method" class="form-control @error('payment_method') is-invalid @enderror" required>
                                        <option value="">Select Payment Method</option>
                                        <option value="cash" {{ old('payment_method') == 'cash' ? 'selected' : '' }}>Cash</option>
                                        <option value="card" {{ old('payment_method') == 'card' ? 'selected' : '' }}>Card</option>
                                        <option value="bank" {{ old('payment_method') == 'bank' ? 'selected' : '' }}>Bank Transfer</option>
                                        <option value="wallet" {{ old('payment_method') == 'wallet' ? 'selected' : '' }}>Digital Wallet</option>
                                    </select>
                                    @error('payment_method')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="tax_amount">Tax Amount</label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text">$</span>
                                                </div>
                                                <input type="number" name="tax_amount" id="tax_amount" class="form-control @error('tax_amount') is-invalid @enderror" 
                                                       value="{{ old('tax_amount', 0) }}" step="0.01" min="0">
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
                                                       value="{{ old('discount_amount', 0) }}" step="0.01" min="0">
                                            </div>
                                            @error('discount_amount')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group" id="amountReceivedGroup" style="display: none;">
                                    <label for="amount_received">Amount Received</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">$</span>
                                        </div>
                                        <input type="number" name="amount_received" id="amount_received" class="form-control" 
                                               step="0.01" min="0">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Sale Items -->
                        <div class="row mt-4">
                            <div class="col-12">
                                <h5 class="mb-3">Sale Items</h5>
                                
                                <div class="table-responsive">
                                    <table class="table table-bordered" id="itemsTable">
                                        <thead>
                                            <tr>
                                                <th width="40%">Product</th>
                                                <th width="15%">Unit Price</th>
                                                <th width="15%">Quantity</th>
                                                <th width="15%">Discount</th>
                                                <th width="15%">Total</th>
                                                <th width="5%">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody id="itemsTableBody">
                                            <!-- Items will be added here -->
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <td colspan="6">
                                                    <button type="button" class="btn btn-primary" id="addItemBtn">
                                                        <i class="fas fa-plus"></i> Add Item
                                                    </button>
                                                </td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>

                                <!-- Totals -->
                                <div class="row">
                                    <div class="col-md-6 offset-md-6">
                                        <table class="table table-sm">
                                            <tr>
                                                <th>Subtotal:</th>
                                                <td id="subtotalDisplay">$0.00</td>
                                            </tr>
                                            <tr>
                                                <th>Tax:</th>
                                                <td id="taxDisplay">$0.00</td>
                                            </tr>
                                            <tr>
                                                <th>Discount:</th>
                                                <td id="discountDisplay">$0.00</td>
                                            </tr>
                                            <tr class="table-info">
                                                <th>Total:</th>
                                                <th id="totalDisplay">$0.00</th>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-success" id="submitBtn" disabled>
                            <i class="fas fa-save"></i> Create Sale
                        </button>
                        <a href="{{ route('admin.sales.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
let itemIndex = 0;
const products = @json($products);

$(document).ready(function() {
    // Show amount received field for cash payments
    $('#payment_method').on('change', function() {
        if ($(this).val() === 'cash') {
            $('#amountReceivedGroup').show();
        } else {
            $('#amountReceivedGroup').hide();
        }
    });

    // Add item button
    $('#addItemBtn').on('click', function() {
        addItemRow();
    });

    // Calculate totals when tax or discount changes
    $('#tax_amount, #discount_amount').on('input', function() {
        calculateTotals();
    });

    // Add first item row
    addItemRow();
});

function addItemRow() {
    const row = `
        <tr data-index="${itemIndex}">
            <td>
                <select name="items[${itemIndex}][product_id]" class="form-control product-select" required>
                    <option value="">Select Product</option>
                    ${products.map(product => `<option value="${product.id}" data-price="${product.price}">${product.name} - $${product.price}</option>`).join('')}
                </select>
            </td>
            <td>
                <input type="number" name="items[${itemIndex}][unit_price]" class="form-control unit-price" step="0.01" min="0" readonly>
            </td>
            <td>
                <input type="number" name="items[${itemIndex}][quantity]" class="form-control quantity" value="1" min="1" required>
            </td>
            <td>
                <input type="number" name="items[${itemIndex}][discount_amount]" class="form-control discount" value="0" step="0.01" min="0">
            </td>
            <td>
                <span class="item-total">$0.00</span>
            </td>
            <td>
                <button type="button" class="btn btn-sm btn-danger remove-item">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        </tr>
    `;
    
    $('#itemsTableBody').append(row);
    itemIndex++;
    
    // Bind events to new row
    bindRowEvents();
    calculateTotals();
}

function bindRowEvents() {
    // Product selection
    $('.product-select').off('change').on('change', function() {
        const price = $(this).find(':selected').data('price') || 0;
        $(this).closest('tr').find('.unit-price').val(price);
        calculateRowTotal($(this).closest('tr'));
    });

    // Quantity and discount changes
    $('.quantity, .discount').off('input').on('input', function() {
        calculateRowTotal($(this).closest('tr'));
    });

    // Remove item
    $('.remove-item').off('click').on('click', function() {
        $(this).closest('tr').remove();
        calculateTotals();
    });
}

function calculateRowTotal(row) {
    const unitPrice = parseFloat(row.find('.unit-price').val()) || 0;
    const quantity = parseInt(row.find('.quantity').val()) || 0;
    const discount = parseFloat(row.find('.discount').val()) || 0;
    
    const total = (unitPrice * quantity) - discount;
    row.find('.item-total').text('$' + total.toFixed(2));
    
    calculateTotals();
}

function calculateTotals() {
    let subtotal = 0;
    
    $('#itemsTableBody tr').each(function() {
        const unitPrice = parseFloat($(this).find('.unit-price').val()) || 0;
        const quantity = parseInt($(this).find('.quantity').val()) || 0;
        const discount = parseFloat($(this).find('.discount').val()) || 0;
        
        subtotal += (unitPrice * quantity) - discount;
    });
    
    const tax = parseFloat($('#tax_amount').val()) || 0;
    const discount = parseFloat($('#discount_amount').val()) || 0;
    const total = subtotal + tax - discount;
    
    $('#subtotalDisplay').text('$' + subtotal.toFixed(2));
    $('#taxDisplay').text('$' + tax.toFixed(2));
    $('#discountDisplay').text('$' + discount.toFixed(2));
    $('#totalDisplay').text('$' + total.toFixed(2));
    
    // Enable/disable submit button
    $('#submitBtn').prop('disabled', subtotal <= 0);
}
</script>
@endpush
@endsection
