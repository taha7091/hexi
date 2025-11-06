@extends('layouts.admin')
@section('title', isset($item) ? 'Edit Inventory Item' : 'New Inventory Item')
@section('content')
<div class="container">
  <h2 class="mb-3">{{ isset($item) ? 'Edit Inventory Item' : 'Create Inventory Item' }}</h2>
  <form method="POST" action="{{ isset($item) ? route('admin.inventory-items.update',$item) : route('admin.inventory-items.store') }}">
    @csrf
    @if(isset($item)) @method('PUT') @endif
    <div class="row">
      <div class="col-md-3 mb-3"><label class="form-label">Brand</label>
        <select name="brand_id" class="form-select">
          <option value="">Select Brand</option>
          @foreach($brands as $brand)
            <option value="{{ $brand->id }}" {{ old('brand_id', $item->brand_id ?? '') == $brand->id ? 'selected' : '' }}>{{ $brand->name }}</option>
          @endforeach
        </select></div>
      <div class="col-md-3 mb-3"><label class="form-label">Category</label>
        <select name="inv_category_id" class="form-select" required>
          <option value="">Select Category</option>
          @foreach($categories as $cat)
            <option value="{{ $cat->id }}" {{ old('inv_category_id', $item->inv_category_id ?? '') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
          @endforeach
        </select></div>
      <div class="col-md-3 mb-3"><label class="form-label">Division</label>
        <select name="inv_division_id" class="form-select" required>
          <option value="">Select Division</option>
          @foreach($divisions as $div)
            <option value="{{ $div->id }}" {{ old('inv_division_id', $item->inv_division_id ?? '') == $div->id ? 'selected' : '' }}>{{ $div->name }}</option>
          @endforeach
        </select></div>
      <div class="col-md-3 mb-3"><label class="form-label">Group</label>
        <select name="inv_group_id" class="form-select" required>
          <option value="">Select Group</option>
          @foreach($groups as $grp)
            <option value="{{ $grp->id }}" {{ old('inv_group_id', $item->inv_group_id ?? '') == $grp->id ? 'selected' : '' }}>{{ $grp->name }}</option>
          @endforeach
        </select></div>
    </div>

    <div class="row">
      <div class="col-md-3 mb-3"><label class="form-label">Code</label>
        <input type="text" name="code" class="form-control" value="{{ old('code',$item->code ?? '') }}"></div>
      <div class="col-md-5 mb-3"><label class="form-label">Name</label>
        <input type="text" name="name" class="form-control" value="{{ old('name',$item->name ?? '') }}" required></div>
      <div class="col-md-4 mb-3"><label class="form-label">Barcode</label>
        <input type="text" name="barcode" class="form-control" value="{{ old('barcode',$item->barcode ?? '') }}"></div>
    </div>

    <div class="row">
      <div class="col-md-4 mb-3"><label class="form-label">Buying Unit</label>
        <select name="buying_unit_id" class="form-select" required>
          @foreach($units as $u)
            <option value="{{ $u->id }}" {{ old('buying_unit_id', $item->buying_unit_id ?? '') == $u->id ? 'selected' : '' }}>{{ $u->name }} ({{ $u->symbol }})</option>
          @endforeach
        </select></div>
      <div class="col-md-4 mb-3"><label class="form-label">Stock Unit</label>
        <select name="stock_unit_id" class="form-select" required>
          @foreach($units as $u)
            <option value="{{ $u->id }}" {{ old('stock_unit_id', $item->stock_unit_id ?? '') == $u->id ? 'selected' : '' }}>{{ $u->name }} ({{ $u->symbol }})</option>
          @endforeach
        </select></div>
      <div class="col-md-4 mb-3"><label class="form-label">Usage Unit</label>
        <select name="usage_unit_id" class="form-select" required>
          @foreach($units as $u)
            <option value="{{ $u->id }}" {{ old('usage_unit_id', $item->usage_unit_id ?? '') == $u->id ? 'selected' : '' }}>{{ $u->name }} ({{ $u->symbol }})</option>
          @endforeach
        </select></div>
    </div>

    <div class="row">
      <div class="col-md-6 mb-3"><label class="form-label">Buy -> Stock factor</label>
        <input type="number" step="0.000001" min="0.000001" name="buy_to_stock_factor" value="{{ old('buy_to_stock_factor', $item->buy_to_stock_factor ?? 1) }}" class="form-control" required></div>
      <div class="col-md-6 mb-3"><label class="form-label">Stock -> Usage factor</label>
        <input type="number" step="0.000001" min="0.000001" name="stock_to_usage_factor" value="{{ old('stock_to_usage_factor', $item->stock_to_usage_factor ?? 1) }}" class="form-control" required></div>
    </div>

    <div class="row">
      <div class="col-md-4 mb-3"><label class="form-label">Supplier</label>
        <select name="supplier_id" class="form-select">
          <option value="">Select Supplier</option>
          @foreach($suppliers as $s)
            <option value="{{ $s->id }}" {{ old('supplier_id', $item->supplier_id ?? '') == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
          @endforeach
        </select></div>
      <div class="col-md-4 mb-3"><label class="form-label">Location</label>
        <select name="location_id" class="form-select">
          <option value="">Select Location</option>
          @foreach($locations as $l)
            <option value="{{ $l->id }}" {{ old('location_id', $item->location_id ?? '') == $l->id ? 'selected' : '' }}>{{ $l->name }}</option>
          @endforeach
        </select></div>
      <div class="col-md-4 mb-3"><label class="form-label">Cost Price</label>
        <input type="number" step="0.0001" name="cost_price" value="{{ old('cost_price', $item->cost_price ?? 0) }}" class="form-control"></div>
    </div>

    <div class="row">
      <div class="col-md-4 mb-3"><label class="form-label">Current Stock (in Stock Unit)</label>
        <input type="number" step="0.0001" name="current_stock" value="{{ old('current_stock', $item->current_stock ?? 0) }}" class="form-control"></div>
      <div class="col-md-4 mb-3"><label class="form-label">Minimum Stock</label>
        <input type="number" step="0.0001" name="minimum_stock" value="{{ old('minimum_stock', $item->minimum_stock ?? 0) }}" class="form-control"></div>
      <div class="col-md-4 mb-3 form-check mt-4">
        <input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active" {{ old('is_active', $item->is_active ?? true) ? 'checked' : '' }}>
        <label class="form-check-label" for="is_active">Active</label>
      </div>
    </div>

    <div class="mb-3"><label class="form-label">Notes</label>
      <textarea name="notes" class="form-control" rows="3">{{ old('notes', $item->notes ?? '') }}</textarea></div>

    <button class="btn btn-primary">{{ isset($item) ? 'Update' : 'Create' }}</button>
    <a href="{{ route('admin.inventory-items.index') }}" class="btn btn-secondary">Cancel</a>
  </form>
</div>
@endsection
