@extends('layouts.admin')

@section('title', 'Product Details')
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
    padding: 0.5rem 1rem;
    border-radius: 8px;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    transition: background 0.15s ease, transform 0.12s ease;
}

.card-header a.btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 2px 8px rgba(0,0,0,0.12);
}

.btn-warning {
    background: #ffb700;
    color: #333;
    border: 1px solid #e6a200;
}

/* ====== Tables ====== */
.table {
    width: 100%;
    margin-bottom: 1rem;
    color: #333;
}

.table th {
    font-weight: 600;
    width: 35%;
}

.table td {
    color: #555;
}

.table-borderless th,
.table-borderless td {
    border: none;
    padding: 0.5rem 0.75rem;
}

/* ====== Badges ====== */
.badge {
    display: inline-block;
    padding: 0.35rem 0.6rem;
    border-radius: 6px;
    font-size: 0.85rem;
    font-weight: 600;
    text-align: center;
    min-width: 40px;
    color: #fff;
}

.badge-success { background: #28a745; }
.badge-warning { background: #ffc107; color: #212529; }
.badge-danger  { background: #dc3545; }

/* ====== Info Boxes ====== */
.info-box {
    display: flex;
    align-items: center;
    background: #f5f5f5;
    padding: 0.75rem 1rem;
    border-radius: 10px;
    margin-bottom: 1rem;
    color: #fff;
}

.info-box.bg-info { background: #17a2b8; }
.info-box.bg-success { background: #28a745; }
.info-box.bg-warning { background: #ffc107; color: #212529; }
.info-box.bg-danger { background: #dc3545; }

.info-box-icon {
    font-size: 1.5rem;
    margin-right: 0.75rem;
}

.info-box-content .info-box-text {
    font-weight: 600;
    font-size: 0.9rem;
}

.info-box-content .info-box-number {
    font-size: 1.1rem;
    font-weight: 700;
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

.btn-primary { background: linear-gradient(90deg, #4facfe, #00f2fe); color: #943131ff; border: 2px solid #943131ff; }
.btn-secondary { background: #992929ff; color: #fff; border: 1px solid #471c1cff; }

/* ====== Product Image ====== */
.img-fluid {
    max-width: 100%;
    border-radius: 8px;
}

/* Placeholder for no image */
.bg-light {
    background: #f0f0f0;
}

/* ====== Stock Management Form ====== */
.form-inline {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    align-items: center;
}

.form-inline .form-group {
    margin-bottom: 0.5rem;
}

.form-inline .form-control {
    min-width: 120px;
    border-radius: 8px;
    border: 1px solid #ddd;
    padding: 0.5rem;
}

.form-inline select.form-control {
    min-width: 140px;
}

.form-inline button.btn {
    margin-top: 0;
}

/* ====== Responsive ====== */
@media (max-width: 768px) {
    .card-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.5rem;
    }
    .form-inline {
        flex-direction: column;
        align-items: flex-start;
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
                    <h3 class="card-title">Product Details</h3>
                    <div>
                        <a href="{{ route('admin.products.edit', $product) }}" class="btn btn-warning">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                        <a href="{{ route('admin.products.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Products
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <!-- Product Image -->
                            <div class="text-center mb-4">
                                @if($product->image_url)
                                    <img src="{{ $product->image_url }}" alt="{{ $product->name }}" 
                                         class="img-fluid rounded" style="max-height: 300px;">
                                @else
                                    <div class="bg-light d-flex align-items-center justify-content-center rounded" 
                                         style="height: 300px;">
                                        <i class="fas fa-image fa-5x text-muted"></i>
                                    </div>
                                @endif
                            </div>

                            <!-- Quick Stats -->
                            <div class="row">
                                <div class="col-6">
                                    <div class="info-box bg-info">
                                        <span class="info-box-icon"><i class="fas fa-dollar-sign"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Price</span>
                                            <span class="info-box-number">${{ number_format($product->price, 2) }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="info-box {{ $product->isLowStock() ? 'bg-warning' : ($product->isOutOfStock() ? 'bg-danger' : 'bg-success') }}">
                                        <span class="info-box-icon"><i class="fas fa-boxes"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Stock</span>
                                            <span class="info-box-number">{{ $product->stock_quantity ?? 0 }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-8">
                            <div class="row">
                                <div class="col-md-6">
                                    <h5>Basic Information</h5>
                                    <table class="table table-borderless">
                                        <tr>
                                            <th width="30%">Name:</th>
                                            <td>{{ $product->name }}</td>
                                        </tr>
                                        <tr>
                                            <th>SKU:</th>
                                            <td>{{ $product->sku }}</td>
                                        </tr>
                                        <tr>
                                            <th>Barcode:</th>
                                            <td>{{ $product->barcode ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Group:</th>
                                            <td>{{ $product->group->name }}</td>
                                        </tr>
                                        <tr>
                                            <th>Division:</th>
                                            <td>{{ $product->group->division->name }}</td>
                                        </tr>
                                        <tr>
                                            <th>Category:</th>
                                            <td>{{ $product->group->division->category->name }}</td>
                                        </tr>
                                        <tr>
                                            <th>Brand:</th>
                                            <td>{{ $product->group->division->category->brand->name }}</td>
                                        </tr>
                                        <tr>
                                            <th>Status:</th>
                                            <td>
                                                @if($product->is_active)
                                                    <span class="badge badge-success">Active</span>
                                                @else
                                                    <span class="badge badge-danger">Inactive</span>
                                                @endif
                                            </td>
                                        </tr>
                                    </table>
                                </div>

                                <div class="col-md-6">
                                    <h5>Pricing & Stock</h5>
                                    <table class="table table-borderless">
                                        <tr>
                                            <th width="30%">Selling Price:</th>
                                            <td>${{ number_format($product->price, 2) }}</td>
                                        </tr>
                                        <tr>
                                            <th>Cost Price:</th>
                                            <td>${{ number_format($product->cost_price, 2) }}</td>
                                        </tr>
                                        <tr>
                                            <th>Profit Margin:</th>
                                            <td>
                                                @if($product->getProfitMargin())
                                                    <span class="badge {{ $product->getProfitMargin() > 20 ? 'badge-success' : ($product->getProfitMargin() > 10 ? 'badge-warning' : 'badge-danger') }}">
                                                        {{ number_format($product->getProfitMargin(), 2) }}%
                                                    </span>
                                                @else
                                                    N/A
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Stock Quantity:</th>
                                            <td>
                                                <span class="badge {{ $product->isLowStock() ? 'badge-warning' : ($product->isOutOfStock() ? 'badge-danger' : 'badge-success') }}">
                                                    {{ $product->stock_quantity ?? 0 }}
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Min Stock Level:</th>
                                            <td>{{ $product->min_stock_level ?? 'Not set' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Stock Status:</th>
                                            <td>
                                                @if($product->isOutOfStock())
                                                    <span class="badge badge-danger">Out of Stock</span>
                                                @elseif($product->isLowStock())
                                                    <span class="badge badge-warning">Low Stock</span>
                                                @else
                                                    <span class="badge badge-success">In Stock</span>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Color:</th>
                                            <td>
                                                <span class="badge" style="background-color: {{ $product->color ?? '#007bff' }}">
                                                    {{ $product->color ?? '#007bff' }}
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Sort Order:</th>
                                            <td>{{ $product->sort_order ?? 0 }}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>

                            @if($product->description)
                                <div class="mt-4">
                                    <h5>Description</h5>
                                    <p class="text-muted">{{ $product->description }}</p>
                                </div>
                            @endif

                            <div class="mt-4">
                                <h5>Timestamps</h5>
                                <table class="table table-borderless">
                                    <tr>
                                        <th width="15%">Created:</th>
                                        <td>{{ $product->created_at->format('M d, Y H:i') }}</td>
                                    </tr>
                                    <tr>
                                        <th>Updated:</th>
                                        <td>{{ $product->updated_at->format('M d, Y H:i') }}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Stock Management -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Stock Management</h4>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.products.update-stock', $product) }}" method="POST" class="form-inline">
                        @csrf
                        @method('PUT')
                        <div class="form-group mr-3">
                            <label for="stock_adjustment" class="mr-2">Adjust Stock:</label>
                            <input type="number" name="stock_adjustment" id="stock_adjustment" class="form-control" 
                                   placeholder="Enter adjustment" required>
                        </div>
                        <div class="form-group mr-3">
                            <select name="adjustment_type" class="form-control" required>
                                <option value="add">Add Stock</option>
                                <option value="subtract">Remove Stock</option>
                                <option value="set">Set Stock To</option>
                            </select>
                        </div>
                        <div class="form-group mr-3">
                            <input type="text" name="reason" class="form-control" placeholder="Reason (optional)">
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Update Stock
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
