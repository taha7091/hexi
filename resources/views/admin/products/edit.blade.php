@extends('layouts.admin')

@section('title', 'Edit Product')
@section('styles')
<style>
/* ====== General Page Styles ====== */
body {
    font-family: 'Inter', sans-serif;
    background: #f5f6fa;
    color: #333;
}

.container-fluid {
    padding: 1rem 2rem;
}

/* ====== Card Styles ====== */
.card {
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 6px 18px rgba(0,0,0,0.06);
    margin-bottom: 2rem;
}

.card-header {
    background: linear-gradient(90deg, #4facfe, #00f2fe);
    color: #fff;
    font-weight: 600;
    font-size: 1.2rem;
    padding: 1rem 1.5rem;
    border-radius: 12px 12px 0 0;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.card-header a.btn {
    background: rgba(0,0,0,0.15);
    color: #fff;
    padding: 0.5rem 1rem;
    border-radius: 8px;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    transition: background 0.15s;
}

.card-header a.btn:hover {
    background: rgba(0,0,0,0.25);
}

/* ====== Form Fields ====== */
.form-group {
    margin-bottom: 1rem;
}

.form-group label {
    font-weight: 600;
    margin-bottom: 0.3rem;
    display: inline-block;
}

.form-control,
.form-control-file,
.form-check-input {
    width: 100%;
    padding: 0.55rem 0.75rem;
    border: 1px solid #ddd;
    border-radius: 8px;
    font-size: 0.9rem;
    outline: none;
    transition: border 0.18s, box-shadow 0.18s;
}

.form-control:focus,
.form-control-file:focus {
    border-color: #4facfe;
    box-shadow: 0 0 0 3px rgba(79,172,254,0.1);
}

textarea.form-control {
    resize: vertical;
}

.input-group .input-group-prepend .input-group-text {
    background: #eee;
    border-radius: 8px 0 0 8px;
    border: 1px solid #ddd;
}

/* ====== Checkboxes ====== */
.form-check {
    margin-top: 0.5rem;
}

.form-check-input {
    margin-top: 0.3rem;
}

.form-check-label {
    font-weight: 500;
}

/* ====== Buttons ====== */
.btn {
    padding: 0.5rem 1rem;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.4rem;
    font-size: 0.9rem;
    transition: background 0.15s ease, transform 0.12s ease;
}

.btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 2px 8px rgba(0,0,0,0.12);
}

.btn-primary {
    background: linear-gradient(90deg, #4facfe, #00f2fe);
    color: #943131ff;
    border: 2px solid #943131ff;
}

.btn-secondary {
    background: #992929ff;
    color: #fff;
    border: 1px solid #471c1cff;
}

/* ====== Card Footer ====== */
.card-footer {
    padding: 1rem 1.5rem;
    display: flex;
    gap: 0.75rem;
    justify-content: flex-start;
    background: #f8f9fa;
    border-top: 1px solid #e0e0e0;
    border-radius: 0 0 12px 12px;
}

/* ====== Image Preview ====== */
.img-thumbnail {
    max-height: 150px;
    border-radius: 8px;
    margin-bottom: 0.5rem;
}

/* ====== Responsive ====== */
@media (max-width: 768px) {
    .card-header, .card-footer {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.5rem;
    }

    .card-footer {
        justify-content: flex-start;
    }
}

</style>
@endsection


@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Edit Product</h3>
                    <a href="{{ route('admin.products.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Products
                    </a>
                </div>

                <form action="{{ route('admin.products.update', $product) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    
                    <div class="card-body">
                        <div class="row">
                            <!-- Basic Information -->
                            <div class="col-md-6">
                                <h5 class="mb-3">Basic Information</h5>
                                
                                <div class="form-group">
                                    <label for="group_id">Group <span class="text-danger">*</span></label>
                                    <select name="group_id" id="group_id" class="form-control @error('group_id') is-invalid @enderror" required>
                                        <option value="">Select Group</option>
                                        @foreach($groups as $group)
                                            <option value="{{ $group->id }}" {{ old('group_id', $product->group_id) == $group->id ? 'selected' : '' }}>
                                                {{ $group->division->category->brand->name }} - {{ $group->division->name }} - {{ $group->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('group_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="name">Product Name <span class="text-danger">*</span></label>
                                    <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" 
                                           value="{{ old('name', $product->name) }}" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="sku">SKU <span class="text-danger">*</span></label>
                                    <input type="text" name="sku" id="sku" class="form-control @error('sku') is-invalid @enderror" 
                                           value="{{ old('sku', $product->sku) }}" required>
                                    @error('sku')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="barcode">Barcode</label>
                                    <input type="text" name="barcode" id="barcode" class="form-control @error('barcode') is-invalid @enderror" 
                                           value="{{ old('barcode', $product->barcode) }}">
                                    @error('barcode')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="description">Description</label>
                                    <textarea name="description" id="description" class="form-control @error('description') is-invalid @enderror" 
                                              rows="3">{{ old('description', $product->description) }}</textarea>
                                    @error('description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Pricing & Stock -->
                            <div class="col-md-6">
                                <h5 class="mb-3">Pricing & Stock</h5>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="price">Selling Price <span class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text">$</span>
                                                </div>
                                                <input type="number" name="price" id="price" class="form-control @error('price') is-invalid @enderror" 
                                                       value="{{ old('price', $product->price) }}" step="0.01" min="0" required>
                                            </div>
                                            @error('price')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="cost_price">Cost Price</label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text">$</span>
                                                </div>
                                                <input type="number" name="cost_price" id="cost_price" class="form-control @error('cost_price') is-invalid @enderror" 
                                                       value="{{ old('cost_price', $product->cost_price) }}" step="0.01" min="0">
                                            </div>
                                            @error('cost_price')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="stock_quantity">Stock Quantity</label>
                                            <input type="number" name="stock_quantity" id="stock_quantity" class="form-control @error('stock_quantity') is-invalid @enderror" 
                                                   value="{{ old('stock_quantity', $product->stock_quantity) }}" min="0">
                                            @error('stock_quantity')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="min_stock_level">Min Stock Level</label>
                                            <input type="number" name="min_stock_level" id="min_stock_level" class="form-control @error('min_stock_level') is-invalid @enderror" 
                                                   value="{{ old('min_stock_level', $product->min_stock_level) }}" min="0">
                                            @error('min_stock_level')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <!-- Display Settings -->
                                <h5 class="mb-3 mt-4">Display Settings</h5>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="color">Color</label>
                                            <input type="color" name="color" id="color" class="form-control @error('color') is-invalid @enderror" 
                                                   value="{{ old('color', $product->color ?? '#007bff') }}">
                                            @error('color')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="sort_order">Sort Order</label>
                                            <input type="number" name="sort_order" id="sort_order" class="form-control @error('sort_order') is-invalid @enderror" 
                                                   value="{{ old('sort_order', $product->sort_order ?? 0) }}" min="0">
                                            @error('sort_order')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="image">Product Image</label>
                                    @if($product->image_url)
                                        <div class="mb-2">
                                            <img src="{{ $product->image_url }}" alt="{{ $product->name }}" 
                                                 class="img-thumbnail" style="max-width: 150px;">
                                        </div>
                                    @endif
                                    <input type="file" name="image" id="image" class="form-control-file @error('image') is-invalid @enderror" 
                                           accept="image/*">
                                    @error('image')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">Accepted formats: JPG, PNG, GIF. Max size: 2MB</small>
                                </div>

                                <div class="form-group">
                                    <div class="form-check">
                                        <input type="checkbox" name="is_active" id="is_active" class="form-check-input" 
                                               value="1" {{ old('is_active', $product->is_active) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_active">
                                            Active
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Update Product
                        </button>
                        <a href="{{ route('admin.products.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
