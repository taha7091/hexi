@extends('layouts.admin')
@section('title', isset($division) ? 'Edit Inventory Division' : 'New Inventory Division')
@section('content')
<div class="container">
  <h2 class="mb-3">{{ isset($division) ? 'Edit Inventory Division' : 'Create Inventory Division' }}</h2>
  <form method="POST" action="{{ isset($division) ? route('admin.inv-divisions.update',$division) : route('admin.inv-divisions.store') }}">
    @csrf
    @if(isset($division)) @method('PUT') @endif
    <div class="mb-3">
      <label class="form-label">Category</label>
      <select name="inv_category_id" class="form-select" required>
        <option value="">Select Category</option>
        @foreach($categories as $cat)
          <option value="{{ $cat->id }}" {{ old('inv_category_id', $division->inv_category_id ?? '') == $cat->id ? 'selected' : '' }}>{{ $cat->name }} ({{ $cat->brand->name ?? '-' }})</option>
        @endforeach
      </select>
    </div>
    <div class="mb-3">
      <label class="form-label">Name</label>
      <input type="text" name="name" value="{{ old('name',$division->name ?? '') }}" class="form-control" required>
    </div>
    <div class="mb-3">
      <label class="form-label">Description</label>
      <input type="text" name="description" value="{{ old('description',$division->description ?? '') }}" class="form-control">
    </div>
    <div class="row">
      <div class="col-md-4 mb-3"><label class="form-label">Color</label>
        <input type="text" name="color" value="{{ old('color',$division->color ?? '') }}" class="form-control"></div>
      <div class="col-md-4 mb-3"><label class="form-label">Icon</label>
        <input type="text" name="icon" value="{{ old('icon',$division->icon ?? '') }}" class="form-control"></div>
      <div class="col-md-4 mb-3"><label class="form-label">Sort Order</label>
        <input type="number" name="sort_order" value="{{ old('sort_order',$division->sort_order ?? 0) }}" class="form-control"></div>
    </div>
    <div class="form-check mb-3">
      <input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active" {{ old('is_active', $division->is_active ?? true) ? 'checked' : '' }}>
      <label class="form-check-label" for="is_active">Active</label>
    </div>
    <button class="btn btn-primary">{{ isset($division) ? 'Update' : 'Create' }}</button>
    <a href="{{ route('admin.inv-divisions.index') }}" class="btn btn-secondary">Cancel</a>
  </form>
</div>
@endsection
