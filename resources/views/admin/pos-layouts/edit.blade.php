@extends('layouts.admin')

@section('title', 'Edit POS Layout')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Edit POS Layout</h3>
                    <div>
                        <a href="{{ route('admin.pos-layouts.show', $posLayout) }}" class="btn btn-info">
                            <i class="fas fa-eye"></i> View Layout
                        </a>
                        <a href="{{ route('admin.pos-layouts.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Layouts
                        </a>
                    </div>
                </div>

                <form action="{{ route('admin.pos-layouts.update', $posLayout) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="brand_id">Brand <span class="text-danger">*</span></label>
                                    <select name="brand_id" id="brand_id" class="form-control @error('brand_id') is-invalid @enderror" required>
                                        <option value="">Select Brand</option>
                                        @foreach($brands as $brand)
                                            <option value="{{ $brand->id }}" {{ old('brand_id', $posLayout->brand_id) == $brand->id ? 'selected' : '' }}>
                                                {{ $brand->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('brand_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name">Layout Name <span class="text-danger">*</span></label>
                                    <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" 
                                           value="{{ old('name', $posLayout->name) }}" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea name="description" id="description" class="form-control @error('description') is-invalid @enderror" 
                                      rows="3">{{ old('description', $posLayout->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <div class="form-check">
                                <input type="checkbox" name="is_default" id="is_default" class="form-check-input" 
                                       value="1" {{ old('is_default', $posLayout->is_default) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_default">
                                    Set as Default Layout
                                </label>
                            </div>
                            <small class="form-text text-muted">
                                If checked, this will become the default layout for the selected brand and unset any existing default.
                            </small>
                        </div>

                        <!-- Layout Statistics -->
                        <div class="row mt-4">
                            <div class="col-md-3">
                                <div class="info-box bg-info">
                                    <span class="info-box-icon"><i class="fas fa-layer-group"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Groups</span>
                                        <span class="info-box-number">{{ $posLayout->groups_count ?? 0 }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="info-box bg-success">
                                    <span class="info-box-icon"><i class="fas fa-calendar"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Created</span>
                                        <span class="info-box-number">{{ $posLayout->created_at->format('M d, Y') }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="info-box bg-warning">
                                    <span class="info-box-icon"><i class="fas fa-edit"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Updated</span>
                                        <span class="info-box-number">{{ $posLayout->updated_at->format('M d, Y') }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="info-box {{ $posLayout->is_default ? 'bg-primary' : 'bg-secondary' }}">
                                    <span class="info-box-icon"><i class="fas fa-star"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Status</span>
                                        <span class="info-box-number">{{ $posLayout->is_default ? 'Default' : 'Custom' }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Update Layout
                        </button>
                        <a href="{{ route('admin.pos-layouts.show', $posLayout) }}" class="btn btn-info">
                            <i class="fas fa-th-large"></i> Design Layout
                        </a>
                        <a href="{{ route('admin.pos-layouts.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
