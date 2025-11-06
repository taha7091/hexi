@extends('layouts.admin')

@section('title', 'Recipes')

@section('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    .card-custom {
        box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        border: none;
        border-radius: 12px;
        margin-bottom: 24px;
    }
    .card-header-custom {
        background: #fff;
        border-bottom: 1px solid #f0f0f0;
        padding: 20px 24px;
        border-top-left-radius: 12px;
        border-top-right-radius: 12px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 10px;
    }
    .form-label {
        font-weight: 600;
        margin-bottom: 6px;
    }
    .select2-container .select2-selection--single {
        height: 38px;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 38px;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 38px;
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
        border-bottom: 1px solid #e2e8f0;
        text-align: left;
    }
    tbody tr:hover {
        background: #f7f9fc;
    }
    .badge-custom {
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
    }
    .badge-success {
        background: #c6f6d5;
        color: #22543d;
    }
    .badge-secondary {
        background: #e2e8f0;
        color: #4a5568;
    }
    .btn-sm {
        padding: 5px 10px;
        font-size: 12px;
        border-radius: 6px;
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    {{-- Quick Add Card --}}
    <div class="card card-custom">
        <div class="card-header card-header-custom">
            <h3><i class="fas fa-book-open text-primary"></i> Quick Add Recipe</h3>
        </div>
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success d-flex justify-content-between align-items-center">
                    <div>{{ session('success') }}</div>
                    @if(session('recent_product_id'))
                        <a class="btn btn-outline-primary btn-sm" href="{{ route('admin.products.recipes.edit', session('recent_product_id')) }}">Manage full recipe</a>
                    @endif
                </div>
            @endif

            <form method="POST" action="{{ route('admin.recipes.quick-add') }}" id="quickAddForm">
                @csrf
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Sales Item</label>
                        <select name="product_id" id="product_select" class="form-control" required></select>
                        @error('product_id')<div class="text-danger small">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Inventory Item</label>
                        <select name="inventory_item_id" id="inventory_select" class="form-control" disabled required></select>
                        @error('inventory_item_id')<div class="text-danger small">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Usage Quantity</label>
                        <input type="number" name="usage_quantity" class="form-control" step="0.0001" min="0.0001" value="{{ old('usage_quantity', '') }}" required>
                        @error('usage_quantity')<div class="text-danger small">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-1 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">Add</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Products Table Card --}}
    <div class="card card-custom">
        <div class="card-header card-header-custom">
            <h3><i class="fas fa-search text-primary"></i> Browse Products</h3>
            <form method="GET" action="{{ route('admin.recipes.index') }}" class="filter-form d-flex align-items-center gap-2 flex-wrap">
                <input type="text" name="q" value="{{ $q ?? '' }}" placeholder="Search products (name, SKU, barcode)">
                <select name="status">
                    <option value="all" {{ ($status ?? 'all')==='all' ? 'selected' : '' }}>All ({{ $allCount ?? 0 }})</option>
                    <option value="with" {{ ($status ?? 'all')==='with' ? 'selected' : '' }}>With Recipe ({{ $withCount ?? 0 }})</option>
                    <option value="without" {{ ($status ?? 'all')==='without' ? 'selected' : '' }}>Without Recipe ({{ $withoutCount ?? 0 }})</option>
                </select>
                <button type="submit">Filter</button>
            </form>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>SKU</th>
                            <th>Barcode</th>
                            <th class="text-center">Recipe Items</th>
                            <th class="text-center">Status</th>
                            <th style="width:160px;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse(($products ?? collect()) as $p)
                            <tr>
                                <td>{{ $p->name }}</td>
                                <td>{{ $p->sku }}</td>
                                <td>{{ $p->barcode }}</td>
                                <td class="text-center">{{ $p->recipe_items_count }}</td>
                                <td class="text-center">
                                    @if(($p->recipe_items_count ?? 0) > 0)
                                        <span class="badge-custom badge-success">Has Recipe</span>
                                    @else
                                        <span class="badge-custom badge-secondary">No Recipe</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('admin.products.recipes.edit', $p) }}" class="btn btn-primary btn-sm">Manage</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted">No products found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if(isset($products))
                <div class="mt-3">
                    {{ $products->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    let selectedProductBrandId = null;
    $('#inventory_select').prop('disabled', true);

    $('#product_select').select2({
        theme: 'default',
        placeholder: 'Type to search product... (name, SKU, barcode)',
        allowClear: true,
        minimumInputLength: 1,
        ajax: {
            url: '{{ route('admin.recipes.search-products') }}',
            dataType: 'json',
            delay: 250,
            data: function(params) { return { q: params.term } },
            processResults: function(data) { return data; },
            cache: true
        }
    }).on('select2:select', function(e){
        const d = e.params.data || {};
        selectedProductBrandId = d.brand_id || null;
        $('#inventory_select').val(null).trigger('change');
        $('#inventory_select').prop('disabled', false);
    }).on('select2:clear', function(){
        selectedProductBrandId = null;
        $('#inventory_select').val(null).trigger('change');
        $('#inventory_select').prop('disabled', true);
    });

    $('#inventory_select').select2({
        theme: 'default',
        placeholder: 'Type to search inventory item...',
        allowClear: true,
        minimumInputLength: 1,
        ajax: {
            url: '{{ route('admin.recipes.search-inventory-items') }}',
            dataType: 'json',
            delay: 250,
            data: function(params) { return { q: params.term, brand_id: selectedProductBrandId } },
            processResults: function(data) { return data; },
            cache: true
        }
    });
</script>
@endsection
