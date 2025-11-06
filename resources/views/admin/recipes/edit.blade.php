@extends('layouts.admin')

@section('title', 'Manage Recipe')

@push('styles')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        /* Small style for Select2 to match the form-control */
        .select2-container .select2-selection--single {
            height: calc(1.5em + .75rem + 8px) !important; /* Match form-control padding */
            border: 1px solid #ddd !important;
            border-radius: 8px !important;
        }
        .select2-container .select2-selection--single .select2-selection__rendered {
            line-height: 2.4rem !important;
            padding-left: 14px !important;
        }
        .select2-container .select2-selection--single .select2-selection__arrow {
            height: calc(1.5em + .75rem + 8px) !important;
        }
    </style>
@endpush

@php
    // Calculate costs for the new "Cost Analysis" card
    $totalCost = $recipeItems->sum(function($ri) {
        // Ensure inventoryItem and its cost_price exist to avoid errors
        return $ri->usage_quantity * ($ri->inventoryItem->cost_price ?? 0);
    });
    $sellingPrice = $product->price ?? 0;
    $profit = $sellingPrice - $totalCost;
    $margin = ($sellingPrice > 0) ? ($profit / $sellingPrice) * 100 : 0;
@endphp

@section('content')
<div class="container-fluid">
    
    <div class="card" style="box-shadow: 0 4px 12px rgba(0,0,0,0.05); border: none; border-radius: 12px; margin-bottom: 24px;">
        <div class="card-header d-flex justify-content-between align-items-center" style="background-color: #fff; border-bottom: 1px solid #f0f0f0; padding: 20px 24px; border-top-left-radius: 12px; border-top-right-radius: 12px;">
            <h3 class="card-title" style="margin: 0; font-size: 1.25rem; font-weight: 600;">
                <i class="fas fa-book-open text-primary" style="margin-right: 10px;"></i>
                Recipe for: {{ $product->name }}
            </h3>
            <div>
                <a href="{{ route('admin.products.index') }}" class="btn btn-secondary">Back to Products</a>
                <a href="{{ route('admin.products.edit', $product) }}" class="btn btn-primary">Edit Product</a>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="row">
        <div class="col-md-4">
            <div class="card" style="box-shadow: 0 4px 12px rgba(0,0,0,0.05); border: none; border-radius: 12px; margin-bottom: 24px;">
                <div class="card-header" style="background-color: #fff; border-bottom: 1px solid #f0f0f0; padding: 20px 24px; border-top-left-radius: 12px; border-top-right-radius: 12px;">
                    <h5 class="card-title" style="margin: 0; font-size: 1.1rem; font-weight: 600;">Add Ingredient</h5>
                </div>
                <div class="card-body" style="padding: 24px;">
                    <form action="{{ route('admin.products.recipes.store', $product) }}" method="POST">
                        @csrf
                        <div style="margin-bottom: 1.25rem;">
                            <label class="form-label" style="font-weight: 600; color: #555; font-size: 0.9rem; margin-bottom: 8px; display: block;">Inventory Item <span class="text-danger">*</span></label>
                            <select name="inventory_item_id" class="form-control select2-dropdown" required style="width: 100%;">
                                <option value="">Type to search inventory item...</option>
                            </select>
                        </div>
                        <div style="margin-bottom: 1.25rem;">
                            <label class="form-label" style="font-weight: 600; color: #555; font-size: 0.9rem; margin-bottom: 8px; display: block;">Usage Quantity <span class="text-danger">*</span></label>
                            <input type="number" step="0.0001" min="0.0001" name="usage_quantity" class="form-control" placeholder="e.g., 150 (in base unit)" required style="border-radius: 8px; border: 1px solid #ddd; padding: 10px 14px;">
                        </div>
                        <div>
                            <button type="submit" class="btn btn-primary" style="width: 100%; background-color: #007bff; border: none; color: #fff; padding: 12px; border-radius: 8px; font-weight: 600; cursor: pointer; font-size: 1rem;">
                                <i class="fas fa-plus" style="margin-right: 8px;"></i>Add to Recipe
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card" style="box-shadow: 0 4px 12px rgba(0,0,0,0.05); border: none; border-radius: 12px; margin-bottom: 24px;">
                <div class="card-header" style="background-color: #fff; border-bottom: 1px solid #f0f0f0; padding: 20px 24px; border-top-left-radius: 12px; border-top-right-radius: 12px;">
                    <h5 class="card-title" style="margin: 0; font-size: 1.1rem; font-weight: 600;">Current Recipe</h5>
                </div>
                <div class="card-body" style="padding: 24px;">
                    <ul style="list-style: none; padding: 0; margin: 0;">
                        <li style="display: flex; justify-content: space-between; padding: 12px 20px; background-color: #f8f9fa; font-weight: 600; color: #333; border-radius: 8px; margin-bottom: 8px; font-size: 0.9rem;">
                            <span style="flex: 3;">Inventory Item</span>
                            <span style="flex: 3;">Usage Quantity</span>
                            <span style="flex: 1; text-align: center;">Unit</span>
                            <span style="flex: 1; text-align: right;">Action</span>
                        </li>

                        @forelse($recipeItems as $ri)
                            <li style="display: flex; align-items: center; padding: 16px 20px; border: 1px solid #eee; border-radius: 8px; margin-bottom: 8px; background: #fff;">
                                <span style="flex: 3; font-weight: 500;">{{ $ri->inventoryItem->name }}</span>
                                
                                <span style="flex: 3;">
                                    <form action="{{ route('admin.products.recipes.update', [$product, $ri]) }}" method="POST" style="display: flex; align-items: center; gap: 8px; margin: 0;">
                                        @csrf
                                        @method('PUT')
                                        <input type="number" name="usage_quantity" value="{{ $ri->usage_quantity }}" step="0.0001" min="0.0001" class="form-control" style="max-width: 120px; border-radius: 5px; border: 1px solid #ddd; padding: 6px 10px; font-size: 0.9rem;">
                                        <button type="submit" style="background-color: #28a745; color: white; border: none; padding: 6px 12px; font-size: 0.8rem; border-radius: 5px; cursor: pointer; font-weight: 500;">Update</button>
                                    </form>
                                </span>

                                <span style="flex: 1; text-align: center; color: #777; font-size: 0.9rem;">
                                    {{ optional($ri->inventoryItem->usageUnit)->symbol ?? optional($ri->inventoryItem->usageUnit)->name ?? '-' }}
                                </span>
                                
                                <span style="flex: 1; text-align: right;">
                                    <form action="{{ route('admin.products.recipes.destroy', [$product, $ri]) }}" method="POST" onsubmit="return confirm('Remove this item from the recipe?')" style="display: inline; margin: 0;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" style="background: none; border: none; color: #dc3545; font-size: 1.1rem; cursor: pointer; padding: 4px;" title="Remove Item">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </form>
                                </span>
                            </li>
                        @empty
                            <li style="text-align: center; color: #888; padding: 30px; border: 1px dashed #ddd; border-radius: 8px;">
                                <i class="fas fa-clipboard-list" style="font-size: 2rem; margin-bottom: 10px; display: block;"></i>
                                No items in recipe yet.
                            </li>
                        @endforelse
                    </ul>
                </div>
            </div>

            <div class="card" style="box-shadow: 0 4px 12px rgba(0,0,0,0.05); border: none; border-radius: 12px; margin-bottom: 24px;">
                <div class="card-header" style="background-color: #fff; border-bottom: 1px solid #f0f0f0; padding: 20px 24px; border-top-left-radius: 12px; border-top-right-radius: 12px;">
                    <h5 class="card-title" style="margin: 0; font-size: 1.1rem; font-weight: 600;">
                        <i class="fas fa-calculator text-success" style="margin-right: 10px;"></i>
                        Recipe Cost Analysis
                    </h5>
                </div>
                <div class="card-body" style="padding: 24px;">
                    <div style="display: flex; justify-content: space-around; text-align: center;">
                        <div>
                            <h6 style="font-size: 0.8rem; color: #888; text-transform: uppercase; margin-bottom: 8px;">Total Ingredient Cost</h6>
                            <h4 style="font-weight: 700; color: #dc3545; margin: 0;">${{ number_format($totalCost, 2) }}</h4>
                        </div>
                        <div>
                            <h6 style="font-size: 0.8rem; color: #888; text-transform: uppercase; margin-bottom: 8px;">Selling Price</h6>
                            <h4 style="font-weight: 700; color: #007bff; margin: 0;">${{ number_format($sellingPrice, 2) }}</h4>
                        </div>
                        <div>
                            <h6 style="font-size: 0.8rem; color: #888; text-transform: uppercase; margin-bottom: 8px;">Profit Margin</h6>
                            <h4 style="font-weight: 700; color: {{ $margin > 0 ? '#28a745' : '#dc3545' }}; margin: 0;">{{ number_format($margin, 2) }}%</h4>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection

@push('scripts')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(function() {
            // Initialize Select2 with AJAX search (brand-restricted)
            $('.select2-dropdown').select2({
                width: '100%',
                placeholder: 'Type to search inventory items',
                minimumInputLength: 1,
                ajax: {
                    url: '{{ route('admin.recipes.search-inventory-items') }}',
                    dataType: 'json',
                    delay: 250,
                    data: function (params) {
                        return {
                            q: params.term,
                            brand_id: {{ $product->group->division->category->brand->id }}
                        };
                    },
                    processResults: function (data) {
                        return data; // expects { results: [ {id, text}, ... ] }
                    },
                    cache: true
                }
            });
        });
    </script>
@endpush