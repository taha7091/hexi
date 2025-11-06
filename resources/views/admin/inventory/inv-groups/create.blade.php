@extends('layouts.admin')
@section('title', isset($group) ? 'Edit Inventory Group' : 'New Inventory Group')
@section('content')
<div class="container">
  <h2 class="mb-3">{{ isset($group) ? 'Edit Inventory Group' : 'Create Inventory Group' }}</h2>
  <form method="POST" action="{{ isset($group) ? route('admin.inv-groups.update',$group) : route('admin.inv-groups.store') }}">
    @csrf
    @if(isset($group)) @method('PUT') @endif
    <div class="mb-3">
      <label class="form-label">Division</label>
      <select name="inv_division_id" class="form-select" required>
        <option value="">Select Division</option>
        @foreach($divisions as $div)
          <option value="{{ $div->id }}" {{ old('inv_division_id', $group->inv_division_id ?? '') == $div->id ? 'selected' : '' }}>{{ $div->name }} ({{ $div->category->name ?? '-' }})</option>
        @endforeach
      </select>
    </div>
    <div class="mb-3">
      <label class="form-label">Name</label>
      <input type="text" name="name" value="{{ old('name',$group->name ?? '') }}" class="form-control" required>
    </div>
    <div class="mb-3">
      <label class="form-label">Description</label>
      <input type="text" name="description" value="{{ old('description',$group->description ?? '') }}" class="form-control">
    </div>
    <div class="row">
      <div class="col-md-4 mb-3"><label class="form-label">Color</label>
        <input type="text" name="color" value="{{ old('color',$group->color ?? '') }}" class="form-control"></div>
      <div class="col-md-4 mb-3"><label class="form-label">Icon</label>
        <input type="text" name="icon" value="{{ old('icon',$group->icon ?? '') }}" class="form-control"></div>
      <div class="col-md-4 mb-3"><label class="form-label">Sort Order</label>
        <input type="number" name="sort_order" value="{{ old('sort_order',$group->sort_order ?? 0) }}" class="form-control"></div>
    </div>
    <div class="form-check mb-3">
      <input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active" {{ old('is_active', $group->is_active ?? true) ? 'checked' : '' }}>
      <label class="form-check-label" for="is_active">Active</label>
    </div>
    <button class="btn btn-primary">{{ isset($group) ? 'Update' : 'Create' }}</button>
    <a href="{{ route('admin.inv-groups.index') }}" class="btn btn-secondary">Cancel</a>
  </form>
</div>
@endsection
