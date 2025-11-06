@extends('layouts.admin')
@section('title', isset($category) ? 'Edit Inventory Category' : 'New Inventory Category')
@section('content')
<div class="container">
  <h2 class="mb-3">{{ isset($category) ? 'Edit Inventory Category' : 'Create Inventory Category' }}</h2>
  <form method="POST" action="{{ isset($category) ? route('admin.inv-categories.update',$category) : route('admin.inv-categories.store') }}">
    @csrf
    @if(isset($category)) @method('PUT') @endif
    <div class="mb-3">
      <label class="form-label">Brand</label>
      <select name="brand_id" class="form-select" required>
        <option value="">Select Brand</option>
        @foreach($brands as $brand)
          <option value="{{ $brand->id }}" {{ old('brand_id', $category->brand_id ?? '') == $brand->id ? 'selected' : '' }}>{{ $brand->name }}</option>
        @endforeach
      </select>
    </div>
    <div class="mb-3">
      <label class="form-label">Name</label>
      <input type="text" name="name" value="{{ old('name',$category->name ?? '') }}" class="form-control" required>
    </div>
    <div class="mb-3">
      <label class="form-label">Description</label>
      <input type="text" name="description" value="{{ old('description',$category->description ?? '') }}" class="form-control">
    </div>
    <div class="row">
      <div class="col-md-4 mb-3">
        <label class="form-label">Color</label>
        <input type="text" name="color" value="{{ old('color',$category->color ?? '') }}" class="form-control">
      </div>
      <div class="col-md-4 mb-3">
        <label class="form-label">Icon</label>
        <input type="text" name="icon" value="{{ old('icon',$category->icon ?? '') }}" class="form-control">
      </div>
      <div class="col-md-4 mb-3">
        <label class="form-label">Sort Order</label>
        <input type="number" name="sort_order" value="{{ old('sort_order',$category->sort_order ?? 0) }}" class="form-control">
      </div>
    </div>
    <div class="form-check mb-3">
      <input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active" {{ old('is_active', $category->is_active ?? true) ? 'checked' : '' }}>
      <label class="form-check-label" for="is_active">Active</label>
    </div>
    <button class="btn btn-primary">{{ isset($category) ? 'Update' : 'Create' }}</button>
    <a href="{{ route('admin.inv-categories.index') }}" class="btn btn-secondary">Cancel</a>
  </form>
</div>
@endsection
