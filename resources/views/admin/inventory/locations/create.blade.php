@extends('layouts.admin')
@section('title', isset($location) ? 'Edit Location' : 'New Location')
@section('content')
<div class="container">
  <h2 class="mb-3">{{ isset($location) ? 'Edit Location' : 'Create Location' }}</h2>
  <form method="POST" action="{{ isset($location) ? route('admin.inventory-locations.update',$location) : route('admin.inventory-locations.store') }}">
    @csrf
    @if(isset($location)) @method('PUT') @endif
    <div class="mb-3">
      <label class="form-label">Name</label>
      <input type="text" name="name" value="{{ old('name',$location->name ?? '') }}" class="form-control" required>
    </div>
    <div class="mb-3">
      <label class="form-label">Type</label>
      <input type="text" name="type" value="{{ old('type',$location->type ?? '') }}" class="form-control">
    </div>
    <div class="mb-3">
      <label class="form-label">Description</label>
      <input type="text" name="description" value="{{ old('description',$location->description ?? '') }}" class="form-control">
    </div>
    <div class="form-check mb-3">
      <input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active" {{ old('is_active', $location->is_active ?? true) ? 'checked' : '' }}>
      <label class="form-check-label" for="is_active">Active</label>
    </div>
    <button class="btn btn-primary">{{ isset($location) ? 'Update' : 'Create' }}</button>
    <a href="{{ route('admin.inventory-locations.index') }}" class="btn btn-secondary">Cancel</a>
  </form>
</div>
@endsection
