@extends('layouts.admin')

@section('title', 'Create POS Layout')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Create New POS Layout</h3>
                    <a href="{{ route('admin.pos-layouts.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Layouts
                    </a>
                </div>

                <form action="{{ route('admin.pos-layouts.store') }}" method="POST">
                    @csrf
                    
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="brand_id">Brand <span class="text-danger">*</span></label>
                                    <select name="brand_id" id="brand_id" class="form-control @error('brand_id') is-invalid @enderror" required>
                                        <option value="">Select Brand</option>
                                        @foreach($brands as $brand)
                                            <option value="{{ $brand->id }}" {{ old('brand_id') == $brand->id ? 'selected' : '' }}>
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
                                           value="{{ old('name') }}" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea name="description" id="description" class="form-control @error('description') is-invalid @enderror" 
                                      rows="3">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <div class="form-check">
                                <input type="checkbox" name="is_default" id="is_default" class="form-check-input" 
                                       value="1" {{ old('is_default') ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_default">
                                    Set as Default Layout
                                </label>
                            </div>
                            <small class="form-text text-muted">
                                If checked, this will become the default layout for the selected brand and unset any existing default.
                            </small>
                        </div>
                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Create Layout
                        </button>
                        <a href="{{ route('admin.pos-layouts.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Info Card -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card border-info">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="fas fa-info-circle"></i> Next Steps</h5>
                </div>
                <div class="card-body">
                    <p class="mb-2">After creating the layout, you'll be able to:</p>
                    <ul class="mb-0">
                        <li>Add and position groups on the layout canvas</li>
                        <li>Drag and drop groups to arrange them</li>
                        <li>Resize groups to fit your POS screen</li>
                        <li>Set colors and icons for visual organization</li>
                        <li>Preview the layout before deploying to POS devices</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
