@extends('layouts.admin')

@section('title', 'Edit Group')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Edit Group</h3>
                    <a href="{{ route('admin.groups.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Groups
                    </a>
                </div>

                <form action="{{ route('admin.groups.update', $group) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="division_id">Division <span class="text-danger">*</span></label>
                                    <select name="division_id" id="division_id" class="form-control @error('division_id') is-invalid @enderror" required>
                                        <option value="">Select Division</option>
                                        @foreach($divisions as $division)
                                            <option value="{{ $division->id }}" {{ old('division_id', $group->division_id) == $division->id ? 'selected' : '' }}>
                                               {{ $division->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('division_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name">Group Name <span class="text-danger">*</span></label>
                                    <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" 
                                           value="{{ old('name', $group->name) }}" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="color">Color</label>
                                    <input type="color" name="color" id="color" class="form-control @error('color') is-invalid @enderror" 
                                           value="{{ old('color', $group->color ?? '#007bff') }}">
                                    @error('color')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="icon">Icon (Font Awesome class)</label>
                                    <input type="text" name="icon" id="icon" class="form-control @error('icon') is-invalid @enderror" 
                                           value="{{ old('icon', $group->icon) }}" placeholder="fas fa-layer-group">
                                    @error('icon')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="sort_order">Sort Order</label>
                                    <input type="number" name="sort_order" id="sort_order" class="form-control @error('sort_order') is-invalid @enderror" 
                                           value="{{ old('sort_order', $group->sort_order ?? 0) }}" min="0">
                                    @error('sort_order')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <div class="form-check">
                                        <input type="checkbox" name="is_active" id="is_active" class="form-check-input" 
                                               value="1" {{ old('is_active', $group->is_active) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_active">
                                            Active
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- POS Layout Settings -->
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="pos_x">POS X Position</label>
                                    <input type="number" name="pos_x" id="pos_x" class="form-control @error('pos_x') is-invalid @enderror" 
                                           value="{{ old('pos_x', $group->pos_x ?? 0) }}" min="0">
                                    @error('pos_x')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="pos_y">POS Y Position</label>
                                    <input type="number" name="pos_y" id="pos_y" class="form-control @error('pos_y') is-invalid @enderror" 
                                           value="{{ old('pos_y', $group->pos_y ?? 0) }}" min="0">
                                    @error('pos_y')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="width">Width (px)</label>
                                    <input type="number" name="width" id="width" class="form-control @error('width') is-invalid @enderror" 
                                           value="{{ old('width', $group->width ?? 150) }}" min="50">
                                    @error('width')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="height">Height (px)</label>
                                    <input type="number" name="height" id="height" class="form-control @error('height') is-invalid @enderror" 
                                           value="{{ old('height', $group->height ?? 100) }}" min="50">
                                    @error('height')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea name="description" id="description" class="form-control @error('description') is-invalid @enderror" 
                                      rows="3">{{ old('description', $group->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Update Group
                        </button>
                        <a href="{{ route('admin.groups.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
