@extends('layouts.admin')

@section('title', 'Adjust Stock')

@section('content')
<div class="container-fluid">
    <div class="row">
        
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Adjust Stock - {{ $product->name }}</h3>
                    <a href="{{ route('admin.inventory.index') }}" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left mr-1"></i> Back to Inventory
                    </a>
                </div>

                <form action="{{ route('admin.inventory.process-stock-adjustment', $product) }}" method="POST" id="stockAdjustmentForm">
                    @csrf
                    
                    <div class="card-body">
                        
                        <div class="text-center mb-4 p-3 bg-light rounded">
                            <h6 class="text-muted font-weight-bold text-uppercase mb-1">Current Stock</h6>
                            <h1 class="display-4 font-weight-bold {{ $product->isOutOfStock() ? 'text-danger' : ($product->isLowStock() ? 'text-warning' : 'text-success') }}">
                                {{ $product->stock_quantity ?? 0 }}
                            </h1>
                            <span class="text-muted">units on hand</span>
                        </div>

                        <hr>

                        <div class="form-group text-center">
                            <label class="d-block mb-3 font-weight-bold">1. Select Adjustment Type <span class="text-danger">*</span></label>
                            <div class="btn-group btn-group-toggle" data-toggle="buttons">
                                <label class="btn btn-outline-primary btn-lg px-4 mx-1 rounded">
                                    <input type="radio" name="adjustment_type" id="type_add" value="add" {{ old('adjustment_type') == 'add' ? 'checked' : '' }} required>
                                    <i class="fas fa-plus d-block mb-1 fa-fw"></i> Add
                                </label>
                                <label class="btn btn-outline-danger btn-lg px-4 mx-1 rounded">
                                    <input type="radio" name="adjustment_type" id="type_subtract" value="subtract" {{ old('adjustment_type') == 'subtract' ? 'checked' : '' }} required>
                                     <i class="fas fa-minus d-block mb-1 fa-fw"></i> Remove
                                </label>
                                <label class="btn btn-outline-info btn-lg px-4 mx-1 rounded">
                                    <input type="radio" name="adjustment_type" id="type_set" value="set" {{ old('adjustment_type') == 'set' ? 'checked' : '' }} required>
                                    <i class="fas fa-exchange-alt d-block mb-1 fa-fw"></i> Set
                                </label>
                            </div>
                            @error('adjustment_type')
                                <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                        </div>

                        <div class="row mt-4">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="quantity" class="font-weight-bold">2. Enter Quantity <span class="text-danger">*</span></label>
                                    <input type="number" name="quantity" id="quantity" class="form-control form-control-lg @error('quantity') is-invalid @enderror" 
                                           value="{{ old('quantity') }}" min="0" required placeholder="e.g., 50">
                                    @error('quantity')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="reason" class="font-weight-bold">3. Select Reason <span class="text-danger">*</span></label>
                                    <select name="reason" id="reason" class="form-control form-control-lg @error('reason') is-invalid @enderror" required>
                                        <option value="">Select Reason...</option>
                                        <option value="New Stock Received" {{ old('reason') == 'New Stock Received' ? 'selected' : '' }}>New Stock Received</option>
                                        <option value="Stock Count" {{ old('reason') == 'Stock Count' ? 'selected' : '' }}>Stock Count</option>
                                        <option value="Damaged Goods" {{ old('reason') == 'Damaged Goods' ? 'selected' : '' }}>Damaged Goods</option>
                                        <option value="Expired Items" {{ old('reason') == 'Expired Items' ? 'selected' : '' }}>Expired Items</option>
                                        <option value="Theft/Loss" {{ old('reason') == 'Theft/Loss' ? 'selected' : '' }}>Theft/Loss</option>
                                        <option value="Return to Supplier" {{ old('reason') == 'Return to Supplier' ? 'selected' : '' }}>Return to Supplier</option>
                                        <option value="Correction" {{ old('reason') == 'Correction' ? 'selected' : '' }}>Correction</option>
                                        <option value="Other" {{ old('reason') == 'Other' ? 'selected' : '' }}>Other</option>
                                    </select>
                                    @error('reason')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="notes" class="font-weight-bold">4. Additional Notes (Optional)</label>
                            <textarea name="notes" id="notes" class="form-control @error('notes') is-invalid @enderror" 
                                      rows="3" placeholder="Add any extra details, like batch numbers or specific reasons...">{{ old('notes') }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mt-4" id="previewSection" style="display: none;">
                            <h5 class="text-center text-muted mb-3">Adjustment Preview</h5>
                            <div class="row text-center d-flex align-items-center">
                                <div class="col-sm-4">
                                    <div class="card p-3">
                                        <h6 class="text-muted text-uppercase small">Current</h6>
                                        <h3 class="font-weight-bold mb-0">{{ $product->stock_quantity ?? 0 }}</h3>
                                    </div>
                                </div>
                                <div class="col-sm-4">
                                    <div id="adjustmentPreviewIcon" class="mb-1"></div>
                                    <strong id="adjustmentPreview" class="h5"></strong>
                                </div>
                                <div class="col-sm-4">
                                    <div class="card p-3 border-primary shadow-sm">
                                        <h6 class="text-primary text-uppercase small">New Stock</h6>
                                        <h3 class="font-weight-bold text-primary mb-0" id="newStockPreview">-</h3>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                    </div>

                    <div class="card-footer text-right">
                        <a href="{{ route('admin.inventory.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times mr-1"></i> Cancel
                        </a>
                        <button type="submit" class="btn btn-primary btn-lg" id="submitBtn" disabled>
                            <i class="fas fa-save mr-1"></i> Apply Adjustment
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-box-open mr-2 text-primary"></i>Product Details
                    </h5>
                </div>
                
                <div class="card-body">
                    @if($product->image_url)
                        <div class="text-center mb-3">
                            <img src="{{ $product->image_url }}" alt="{{ $product->name }}" 
                                 class="img-fluid rounded border" style="max-height: 200px;">
                        </div>
                    @endif

                    <h4 class="h5">{{ $product->name }}</h4>
                    <p class="text-muted small">
                        SKU: {{ $product->sku }} | Group: {{ $product->group->name }}
                    </p>
                    
                    <hr>

                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <strong>Current Stock:</strong>
                            <span class="badge badge-lg {{ $product->isOutOfStock() ? 'badge-danger' : ($product->isLowStock() ? 'badge-warning' : 'badge-success') }}">
                                {{ $product->stock_quantity ?? 0 }} units
                            </span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span>Min Stock Level:</span>
                            <span class="text-muted">{{ $product->min_stock_level ?? 'Not set' }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span>Cost Price:</span>
                            <span class="text-muted">${{ number_format($product->cost_price ?? 0, 2) }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span>Selling Price:</span>
                            <strong>${{ number_format($product->price, 2) }}</strong>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    const currentStock = {{ $product->stock_quantity ?? 0 }};
    
    function updatePreview() {
        const adjustmentType = $('input[name="adjustment_type"]:checked').val();
        const quantityStr = $('#quantity').val();
        const quantity = parseInt(quantityStr) || 0;
        const reason = $('#reason').val();

        // Validate all required fields are filled
        const isValid = adjustmentType && reason && quantityStr !== '' && quantity >= 0;

        if (isValid) {
            let newStock = currentStock;
            let adjustmentText = '';
            let adjustmentIcon = '';
            let adjustmentClass = '';

            switch (adjustmentType) {
                case 'add':
                    newStock = currentStock + quantity;
                    adjustmentText = '+' + quantity;
                    adjustmentIcon = '<i class="fas fa-long-arrow-alt-right fa-3x text-success"></i>';
                    adjustmentClass = 'text-success';
                    break;
                case 'subtract':
                    newStock = Math.max(0, currentStock - quantity); // Don't go below zero
                    adjustmentText = '-' + quantity;
                    adjustmentIcon = '<i class="fas fa-long-arrow-alt-right fa-3x text-danger"></i>';
                    adjustmentClass = 'text-danger';
                    break;
                case 'set':
                    newStock = quantity;
                    adjustmentText = 'Set to ' + quantity;
                    adjustmentIcon = '<i class="fas fa-long-arrow-alt-right fa-3x text-info"></i>';
                    adjustmentClass = 'text-info';
                    break;
            }
            
            // Update preview elements
            $('#adjustmentPreview').text(adjustmentText).removeClass('text-success text-danger text-info').addClass(adjustmentClass);
            $('#adjustmentPreviewIcon').html(adjustmentIcon);
            $('#newStockPreview').text(newStock); // Just the number
            
            $('#previewSection').slideDown(200); // Animate in
            $('#submitBtn').prop('disabled', false);

        } else {
            $('#previewSection').slideUp(200); // Animate out
            $('#submitBtn').prop('disabled', true);
        }
    }
    
    // Listen to changes on all required fields
    $('input[name="adjustment_type"], #quantity, #reason').on('change input', updatePreview);

    // Run on page load to account for 'old()' values from validation errors
    updatePreview();
});
</script>
@endpush
@endsection