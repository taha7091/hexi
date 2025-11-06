@extends('layouts.admin')

@section('title', isset($group) ? 'Edit Customer Group' : 'Create Customer Group')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">{{ isset($group) ? 'Edit Customer Group' : 'Create Customer Group' }}</h1>
        <div>
            <a href="{{ route('admin.customer-groups.index') }}" class="btn btn-secondary shadow-sm">
                <i class="fas fa-arrow-left mr-1"></i> Back to Groups
            </a>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <form action="{{ isset($group) ? route('admin.customer-groups.update', $group) : route('admin.customer-groups.store') }}" method="POST">
                @csrf
                @if(isset($group))
                    @method('PUT')
                @endif

                <div class="row">
                    <div class="col-md-6 form-group">
                        <label for="name">Name</label>
                        <input type="text" name="name" id="name" value="{{ old('name', $group->name ?? '') }}" class="form-control" required>
                        @error('name')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6 form-group">
                        <label for="is_active">Status</label>
                        <select name="is_active" id="is_active" class="form-control">
                            <option value="1" {{ old('is_active', $group->is_active ?? true) ? 'selected' : '' }}>Active</option>
                            <option value="0" {{ old('is_active', $group->is_active ?? true) ? '' : 'selected' }}>Inactive</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea name="description" id="description" rows="3" class="form-control">{{ old('description', $group->description ?? '') }}</textarea>
                    @error('description')
                        <div class="text-danger small">{{ $message }}</div>
                    @enderror
                </div>

                <div class="text-right">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save mr-1"></i> {{ isset($group) ? 'Update Group' : 'Create Group' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

