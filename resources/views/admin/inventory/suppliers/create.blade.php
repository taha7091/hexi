@extends('layouts.admin')
@section('title', isset($supplier) ? 'Edit Supplier' : 'New Supplier')
@section('content')
<div class="container">
  <h2 class="mb-3">{{ isset($supplier) ? 'Edit Supplier' : 'Create Supplier' }}</h2>
  <form method="POST" action="{{ isset($supplier) ? route('admin.inventory-suppliers.update',$supplier) : route('admin.inventory-suppliers.store') }}">
    @csrf
    @if(isset($supplier)) @method('PUT') @endif
    <div class="mb-3"><label class="form-label">Name</label>
      <input type="text" name="name" class="form-control" value="{{ old('name',$supplier->name ?? '') }}" required></div>
    <div class="row">
      <div class="col-md-6 mb-3"><label class="form-label">Contact Person</label>
        <input type="text" name="contact_person" class="form-control" value="{{ old('contact_person',$supplier->contact_person ?? '') }}"></div>
      <div class="col-md-6 mb-3"><label class="form-label">Email</label>
        <input type="email" name="email" class="form-control" value="{{ old('email',$supplier->email ?? '') }}"></div>
    </div>
    <div class="row">
      <div class="col-md-6 mb-3"><label class="form-label">Phone</label>
        <input type="text" name="phone" class="form-control" value="{{ old('phone',$supplier->phone ?? '') }}"></div>
      <div class="col-md-6 mb-3"><label class="form-label">Address</label>
        <input type="text" name="address" class="form-control" value="{{ old('address',$supplier->address ?? '') }}"></div>
    </div>
    <div class="form-check mb-3">
      <input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active" {{ old('is_active', $supplier->is_active ?? true) ? 'checked' : '' }}>
      <label class="form-check-label" for="is_active">Active</label>
    </div>
    <button class="btn btn-primary">{{ isset($supplier) ? 'Update' : 'Create' }}</button>
    <a href="{{ route('admin.inventory-suppliers.index') }}" class="btn btn-secondary">Cancel</a>
  </form>
</div>
@endsection
