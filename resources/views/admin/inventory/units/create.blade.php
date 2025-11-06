@extends('layouts.admin')
@section('title', isset($unit) ? 'Edit Unit' : 'New Unit')
@section('content')
<div class="container">
    <h2 class="mb-3">{{ isset($unit) ? 'Edit Unit' : 'Create Unit' }}</h2>
    <form method="POST" action="{{ isset($unit) ? route('admin.inventory-units.update',$unit) : route('admin.inventory-units.store') }}">
        @csrf
        @if(isset($unit)) @method('PUT') @endif
        <div class="mb-3">
            <label class="form-label">Name</label>
            <input type="text" name="name" value="{{ old('name', $unit->name ?? '') }}" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Symbol</label>
            <input type="text" name="symbol" value="{{ old('symbol', $unit->symbol ?? '') }}" class="form-control">
        </div>
        <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active" {{ old('is_active', $unit->is_active ?? true) ? 'checked' : '' }}>
            <label class="form-check-label" for="is_active">Active</label>
        </div>
        <button class="btn btn-primary">{{ isset($unit) ? 'Update' : 'Create' }}</button>
        <a href="{{ route('admin.inventory-units.index') }}" class="btn btn-secondary">Cancel</a>
    </form>
</div>
@endsection

