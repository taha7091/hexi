@extends('layouts.admin')

@section('title', 'Group Details')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Group Details</h3>
                    <div>
                        <a href="{{ route('admin.groups.edit', $group) }}" class="btn btn-warning">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                        <a href="{{ route('admin.groups.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Groups
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <th width="30%">Name:</th>
                                    <td>
                                        @if($group->icon)
                                            <i class="{{ $group->icon }}" style="color: {{ $group->color ?? '#007bff' }}"></i>
                                        @endif
                                        {{ $group->name }}
                                    </td>
                                </tr>
                                <tr>
                                    <th>Division:</th>
                                    <td>{{ $group->division->name }}</td>
                                </tr>
                                <tr>
                                    <th>Category:</th>
                                    <td>{{ $group->division->category->name }}</td>
                                </tr>
                                <tr>
                                    <th>Brand:</th>
                                    <td>{{ $group->division->category->brand->name }}</td>
                                </tr>
                                <tr>
                                    <th>Status:</th>
                                    <td>
                                        @if($group->is_active)
                                            <span class="badge badge-success">Active</span>
                                        @else
                                            <span class="badge badge-danger">Inactive</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Color:</th>
                                    <td>
                                        <span class="badge" style="background-color: {{ $group->color ?? '#007bff' }}">
                                            {{ $group->color ?? '#007bff' }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Sort Order:</th>
                                    <td>{{ $group->sort_order ?? 0 }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h5>POS Layout Settings</h5>
                            <table class="table table-borderless">
                                <tr>
                                    <th width="30%">Position:</th>
                                    <td>X: {{ $group->pos_x ?? 0 }}, Y: {{ $group->pos_y ?? 0 }}</td>
                                </tr>
                                <tr>
                                    <th>Size:</th>
                                    <td>{{ $group->width ?? 150 }}px × {{ $group->height ?? 100 }}px</td>
                                </tr>
                            </table>

                            @if($group->description)
                                <h5>Description</h5>
                                <p class="text-muted">{{ $group->description }}</p>
                            @endif

                            <h5>Timestamps</h5>
                            <table class="table table-borderless">
                                <tr>
                                    <th width="30%">Created:</th>
                                    <td>{{ $group->created_at->format('M d, Y H:i') }}</td>
                                </tr>
                                <tr>
                                    <th>Updated:</th>
                                    <td>{{ $group->updated_at->format('M d, Y H:i') }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Products Section -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title">Products in this Group</h4>
                    <a href="{{ route('admin.products.create', ['group_id' => $group->id]) }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add Product
                    </a>
                </div>
                <div class="card-body">
                    @if($group->products->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>SKU</th>
                                        <th>Price</th>
                                        <th>Stock</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($group->products as $product)
                                        <tr>
                                            <td>
                                                @if($product->image_url)
                                                    <img src="{{ $product->image_url }}" alt="{{ $product->name }}" 
                                                         class="img-thumbnail me-2" style="width: 40px; height: 40px;">
                                                @endif
                                                {{ $product->name }}
                                            </td>
                                            <td>{{ $product->sku }}</td>
                                            <td>${{ number_format($product->price, 2) }}</td>
                                            <td>
                                                <span class="badge {{ $product->isLowStock() ? 'badge-warning' : ($product->isOutOfStock() ? 'badge-danger' : 'badge-success') }}">
                                                    {{ $product->stock_quantity ?? 0 }}
                                                </span>
                                            </td>
                                            <td>
                                                @if($product->is_active)
                                                    <span class="badge badge-success">Active</span>
                                                @else
                                                    <span class="badge badge-danger">Inactive</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('admin.products.show', $product) }}" class="btn btn-sm btn-info">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="{{ route('admin.products.edit', $product) }}" class="btn btn-sm btn-warning">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-box fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No Products Found</h5>
                            <p class="text-muted">This group doesn't have any products yet.</p>
                            <a href="{{ route('admin.products.create', ['group_id' => $group->id]) }}" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Create First Product
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
