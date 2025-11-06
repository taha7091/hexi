@extends('layouts.admin')

@section('title', 'Create Product')
@section('page-title', 'Create Product')

@section('styles')
<style>
/* ===== Two-column simplified form layout ===== */

/* Container */
.create-category-container {
    background: var(--bg-primary);
    min-height: 100vh;
    padding: 2rem;
    display: flex;
    justify-content: center;
    align-items: flex-start;
}

/* Form shell */
.form-wizard {
    background: var(--card-bg);
    border-radius: 10px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    width: 100%;
    max-width: 900px;
    margin: 1rem;
    padding: 1rem;
}

/* Header */
.wizard-header {
    padding: 1rem;
    text-align: center;
    color: var(--text-primary);
    font-weight: 700;
}

/* Sections */
.form-sections {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

/* Each section */
.form-section {
    padding: 1rem;
    border: 1px solid var(--border-color);
    border-radius: 6px;
    background: var(--bg-secondary);
}

/* Section title */
.section-title {
    font-size: 1rem;
    font-weight: 600;
    margin-bottom: 0.5rem;
}

/* Form group container (two inputs per row) */
.form-group-row {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1rem;
    margin-bottom: 1rem;
}

/* Single form group inside row */
.form-group {
    display: flex;
    flex-direction: column;
}

/* Label */
.form-label {
    font-weight: 600;
    margin-bottom: 0.25rem;
    color: var(--text-primary);
}

/* Input */
.form-control {
    padding: 0.6rem 0.8rem;
    border: 1px solid var(--border-color);
    border-radius: 6px;
    font-size: 0.95rem;
    background: var(--bg-primary);
    color: var(--text-primary);
}

/* Checkbox group */
.form-check {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

/* Checkbox input */
.form-check-input {
    width: 16px;
    height: 16px;
}

/* Checkbox label */
.form-check-label {
    font-size: 0.95rem;
    color: var(--text-primary);
}

/* Help text */
.form-help {
    font-size: 0.85rem;
    color: var(--text-secondary);
    margin-top: 0.25rem;
}

/* Buttons */
.wizard-actions {
    display: flex;
    justify-content: flex-end;
    gap: 0.5rem;
    margin-top: 1rem;
}

.btn {
    padding: 0.6rem 1rem;
    border-radius: 6px;
    border: none;
    cursor: pointer;
    font-size: 0.95rem;
}

.btn-primary {
    background-color: #4facfe;
    color: #fff;
}

.btn-secondary {
    background-color: transparent;
    color: var(--text-primary);
    border: 1px solid var(--border-color);
}

/* Responsive: stack inputs on small screens */
@media (max-width: 768px) {
    .form-group-row {
        grid-template-columns: 1fr; /* stack vertically on mobile */
    }
    .wizard-actions {
        flex-direction: column;
        align-items: stretch;
    }
    .btn { width: 100%; }
}
</style>


@endsection

@section('content')
<div class="create-product-container">
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-12">
                <div class="form-wizard">
                    <!-- Progress Indicator -->
                    <div class="progress-indicator">
                        <div class="progress-bar"></div>
                    </div>

                    <!-- Wizard Header -->
                    <div class="wizard-header">
                        <div class="wizard-icon">📦</div>
                        <h1 class="wizard-title">Create New Product</h1>
                        <p class="wizard-subtitle">Build your inventory with detailed product information</p>
                    </div>

                    <form action="{{ route('admin.products.store') }}" method="POST" enctype="multipart/form-data" id="productForm">
                        @csrf

                        <!-- Form Sections -->
                        <div class="form-sections">
                            <!-- Basic Information Section -->
                            <div class="form-section">
                                <div class="section-title">
                                    <div class="section-icon">
                                        <i class="fas fa-info-circle"></i>
                                    </div>
                                    Product Details
                                </div>

                                <div class="form-group">
                                    <label class="form-label" for="group_id">
                                        Product Group <span class="required-indicator">*</span>
                                    </label>
                                    <select name="group_id" id="group_id" class="form-control @error('group_id') is-invalid @enderror" required>
                                        <option value="">📊 Select Product Group</option>
                                        @foreach($groups as $group)
                                            <option value="{{ $group->id }}" {{ old('group_id', request('group_id')) == $group->id ? 'selected' : '' }}>
                                                {{ $group->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('group_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-help">💡 Choose the group this product belongs to</div>
                                </div>

                                <div class="form-group">
                                    <label class="form-label" for="name">
                                        Product Name <span class="required-indicator">*</span>
                                    </label>
                                    <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror"
                                           value="{{ old('name') }}" required placeholder="Enter product name...">
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-help">✨ A clear and descriptive product name</div>
                                </div>

                                <div class="form-group">
                                    <label class="form-label" for="sku">
                                        SKU Code <span class="required-indicator">*</span>
                                    </label>
                                    <input type="text" name="sku" id="sku" class="form-control @error('sku') is-invalid @enderror"
                                           value="{{ old('sku') }}" required placeholder="Auto-generated or custom SKU...">
                                    @error('sku')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-help">🏷️ Unique identifier for inventory tracking</div>
                                </div>

                                <div class="form-group">
                                    <label class="form-label" for="barcode">Barcode</label>
                                    <input type="text" name="barcode" id="barcode" class="form-control @error('barcode') is-invalid @enderror"
                                           value="{{ old('barcode') }}" placeholder="Product barcode...">
                                    @error('barcode')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-help">📊 Barcode for POS scanning (optional)</div>
                                </div>

                                <div class="form-group">
                                    <label class="form-label" for="description">Product Description</label>
                                    <textarea name="description" id="description" class="form-control @error('description') is-invalid @enderror"
                                              rows="4" placeholder="Detailed product description...">{{ old('description') }}</textarea>
                                    @error('description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-help">📝 Detailed information about the product</div>
                                </div>
                            </div>

                            <!-- Pricing & Stock Section -->
                            <div class="form-section">
                                <div class="section-title">
                                    <div class="section-icon">
                                        <i class="fas fa-dollar-sign"></i>
                                    </div>
                                    Pricing & Inventory
                                </div>

                                <div class="form-group">
                                    <label class="form-label" for="price">
                                        Selling Price <span class="required-indicator">*</span>
                                    </label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">$</span>
                                        </div>
                                        <input type="number" name="price" id="price" class="form-control @error('price') is-invalid @enderror"
                                               value="{{ old('price') }}" step="0.01" min="0" required placeholder="0.00">
                                    </div>
                                    @error('price')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-help">💰 Customer-facing selling price</div>
                                </div>

                                <div class="form-group">
                                    <label class="form-label" for="cost_price">Cost Price</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">$</span>
                                        </div>
                                        <input type="number" name="cost_price" id="cost_price" class="form-control @error('cost_price') is-invalid @enderror"
                                               value="{{ old('cost_price') }}" step="0.01" min="0" placeholder="0.00">
                                    </div>
                                    @error('cost_price')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-help">📊 Your cost for this product</div>
                                </div>

                                <div class="profit-margin-display" id="profitMarginDisplay" style="display: none;">
                                    <div>Profit Margin: <span id="profitMargin">0%</span></div>
                                </div>

                                <div class="form-group">
                                    <label class="form-label" for="stock_quantity">Current Stock</label>
                                    <input type="number" name="stock_quantity" id="stock_quantity" class="form-control @error('stock_quantity') is-invalid @enderror"
                                           value="{{ old('stock_quantity', 0) }}" min="0" placeholder="0">
                                    @error('stock_quantity')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-help">📦 Current quantity in stock</div>
                                </div>

                                <div class="form-group">
                                    <label class="form-label" for="min_stock_level">Minimum Stock Alert</label>
                                    <input type="number" name="min_stock_level" id="min_stock_level" class="form-control @error('min_stock_level') is-invalid @enderror"
                                           value="{{ old('min_stock_level') }}" min="0" placeholder="0">
                                    @error('min_stock_level')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-help">⚠️ Alert when stock falls below this level</div>
                                </div>
                            </div>

                            <!-- Display & Media Section -->
                            <div class="form-section">
                                <div class="section-title">
                                    <div class="section-icon">
                                        <i class="fas fa-palette"></i>
                                    </div>
                                    Display & Media
                                </div>

                                <div class="form-group">
                                    <label class="form-label" for="color">Product Color</label>
                                    <div class="color-input-group">
                                        <input type="color" name="color" id="color" class="form-control @error('color') is-invalid @enderror"
                                               value="{{ old('color', '#f093fb') }}" style="width: 100px; height: 60px; border-radius: 15px;">
                                        <div class="color-preview" id="colorPreview" style="background-color: {{ old('color', '#f093fb') }};"></div>
                                        <div>
                                            <strong>Color Preview</strong><br>
                                            <small>Used for visual identification</small>
                                        </div>
                                    </div>
                                    @error('color')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-help">🎨 Color theme for this product</div>
                                </div>

                                <div class="form-group">
                                    <label class="form-label" for="sort_order">Display Order</label>
                                    <input type="number" name="sort_order" id="sort_order" class="form-control @error('sort_order') is-invalid @enderror"
                                           value="{{ old('sort_order', 0) }}" min="0" placeholder="0">
                                    @error('sort_order')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-help">📊 Lower numbers appear first in lists</div>
                                </div>

                                <div class="form-group">
                                    <label class="form-label" for="image">Product Image</label>
                                    <div class="file-upload-area" onclick="document.getElementById('image').click()">
                                        <div class="file-upload-icon">📸</div>
                                        <div class="file-upload-text">Click to upload product image</div>
                                        <div class="file-upload-hint">JPG, PNG, GIF up to 2MB</div>
                                    </div>
                                    <input type="file" name="image" id="image" class="form-control @error('image') is-invalid @enderror"
                                           accept="image/*" style="display: none;">
                                    @error('image')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-help">🖼️ High-quality product image for display</div>
                                </div>

                                <div class="form-group">
                                    <div class="form-check">
                                        <input type="checkbox" name="is_active" id="is_active" class="form-check-input"
                                               value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_active">
                                            🟢 Active Product
                                        </label>
                                    </div>
                                    <div class="form-help">⚡ Inactive products won't be visible in POS</div>
                                    @error('is_active')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Wizard Actions -->
                        <div class="wizard-actions">
                            <a href="{{ route('admin.products.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Back to Products
                            </a>
                            <button type="submit" class="btn btn-primary" id="submitBtn">
                                <i class="fas fa-magic"></i> Create Product
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    // Auto-generate SKU based on product name
    $('#name').on('blur', function() {
        if (!$('#sku').val()) {
            var sku = $(this).val().toUpperCase().replace(/[^A-Z0-9]/g, '').substring(0, 10);
            if (sku) {
                $('#sku').val(sku + '-' + Math.floor(Math.random() * 1000));
            }
        }
    });

    // Color preview update with animation
    $('#color').on('change input', function() {
        const color = $(this).val();
        $('#colorPreview').css({
            'background-color': color,
            'transform': 'scale(1.1)',
            'transition': 'all 0.3s ease'
        });
        
        setTimeout(() => {
            $('#colorPreview').css('transform', 'scale(1)');
        }, 300);
    });

    // Calculate and display profit margin
    $('#price, #cost_price').on('input', function() {
        var price = parseFloat($('#price').val()) || 0;
        var cost = parseFloat($('#cost_price').val()) || 0;
        
        if (cost > 0 && price > 0) {
            var margin = ((price - cost) / cost * 100).toFixed(2);
            $('#profitMargin').text(margin + '%');
            $('#profitMarginDisplay').show();
            
            // Color code the margin
            if (margin < 10) {
                $('#profitMarginDisplay').css('background', 'linear-gradient(135deg, #e53e3e, #c53030)');
            } else if (margin < 30) {
                $('#profitMarginDisplay').css('background', 'linear-gradient(135deg, #ffc107, #e0a800)');
            } else {
                $('#profitMarginDisplay').css('background', 'linear-gradient(135deg, #43e97b, #38f9d7)');
            }
        } else {
            $('#profitMarginDisplay').hide();
        }
    });

    // File upload preview
    $('#image').on('change', function() {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                $('.file-upload-area').html(`
                    <img src="${e.target.result}" style="max-width: 100%; max-height: 200px; border-radius: 10px;">
                    <div class="file-upload-text" style="margin-top: 1rem;">${file.name}</div>
                    <div class="file-upload-hint">Click to change image</div>
                `);
            };
            reader.readAsDataURL(file);
        }
    });

    // Form validation with enhanced feedback
    $('.form-control').on('blur', function() {
        if ($(this).val() && $(this).hasClass('is-invalid')) {
            $(this).removeClass('is-invalid');
            $(this).next('.invalid-feedback').fadeOut();
        }
    });

    // Real-time validation
    $('#name').on('input', function() {
        const value = $(this).val();
        if (value.length > 0 && value.length < 3) {
            $(this).addClass('is-invalid');
        } else {
            $(this).removeClass('is-invalid');
        }
    });

    // Enhanced form submission
    $('#productForm').on('submit', function(e) {
        const submitBtn = $('#submitBtn');
        submitBtn.html('<i class="fas fa-spinner fa-spin"></i> Creating...');
        submitBtn.prop('disabled', true);
        
        // Add loading animation to form
        $('.form-wizard').css({
            'opacity': '0.8',
            'pointer-events': 'none'
        });
    });

    // Smooth scroll to error fields
    if ($('.is-invalid').length > 0) {
        $('html, body').animate({
            scrollTop: $('.is-invalid').first().offset().top - 100
        }, 500);
    }

    // Add floating labels effect
    $('.form-control').on('focus', function() {
        $(this).parent().addClass('focused');
    }).on('blur', function() {
        if (!$(this).val()) {
            $(this).parent().removeClass('focused');
        }
    });

    // Price validation
    $('#price, #cost_price').on('input', function() {
        const value = parseFloat($(this).val());
        if (value < 0) {
            $(this).val(0);
        }
    });

    // Stock validation
    $('#stock_quantity, #min_stock_level').on('input', function() {
        const value = parseInt($(this).val());
        if (value < 0) {
            $(this).val(0);
        }
    });
});
</script>
@endsection