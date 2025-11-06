@extends('layouts.admin')

@section('page-title', 'Edit Screen - ' . $screen->name)

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Edit Screen: {{ $screen->name }}</h3>
                </div>

                <form action="{{ route('admin.screens.update', $screen) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="card-body">
                        <!-- Screen Name -->
                        <div class="form-group">
                            <label for="name">Screen Name <span class="text-danger">*</span></label>
                            <input type="text" 
                                   class="form-control @error('name') is-invalid @enderror" 
                                   id="name" 
                                   name="name" 
                                   value="{{ old('name', $screen->name) }}" 
                                   required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Description -->
                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" 
                                      name="description" 
                                      rows="3">{{ old('description', $screen->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Grid Settings -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="grid_rows">Grid Rows <span class="text-danger">*</span></label>
                                    <select class="form-control @error('grid_rows') is-invalid @enderror" 
                                            id="grid_rows" 
                                            name="grid_rows" 
                                            required>
                                        @for($i = 4; $i <= 12; $i++)
                                            <option value="{{ $i }}" {{ old('grid_rows', $screen->grid_rows) == $i ? 'selected' : '' }}>
                                                {{ $i }} rows
                                            </option>
                                        @endfor
                                    </select>
                                    @error('grid_rows')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    @if($screen->screenItems->count() > 0)
                                        <small class="form-text text-warning">
                                            <i class="fas fa-exclamation-triangle"></i>
                                            Changing grid size may affect existing items on the screen.
                                        </small>
                                    @endif
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="grid_columns">Grid Columns <span class="text-danger">*</span></label>
                                    <select class="form-control @error('grid_columns') is-invalid @enderror" 
                                            id="grid_columns" 
                                            name="grid_columns" 
                                            required>
                                        @for($i = 4; $i <= 10; $i++)
                                            <option value="{{ $i }}" {{ old('grid_columns', $screen->grid_columns) == $i ? 'selected' : '' }}>
                                                {{ $i }} columns
                                            </option>
                                        @endfor
                                    </select>
                                    @error('grid_columns')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Background Color -->
                        <div class="form-group">
                            <label for="background_color">Background Color</label>
                            <div class="input-group">
                                <input type="color" 
                                       class="form-control @error('background_color') is-invalid @enderror" 
                                       id="background_color" 
                                       name="background_color" 
                                       value="{{ old('background_color', $screen->background_color) }}">
                                <div class="input-group-append">
                                    <span class="input-group-text">{{ old('background_color', $screen->background_color) }}</span>
                                </div>
                            </div>
                            @error('background_color')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Default Screen -->
                        <div class="form-check">
                            <input type="checkbox" 
                                   class="form-check-input" 
                                   id="is_default" 
                                   name="is_default" 
                                   value="1" 
                                   {{ old('is_default', $screen->is_default) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_default">
                                Set as Default Screen
                            </label>
                            <small class="form-text text-muted">
                                The default screen will be loaded automatically in the POS application.
                            </small>
                        </div>

                        <!-- Info -->
                        <div class="alert alert-info mt-4">
                            <h6><i class="fas fa-info-circle"></i> Current Screen Information</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <strong>Items on Screen:</strong> {{ $screen->screenItems->count() }}<br>
                                    <strong>Created:</strong> {{ $screen->created_at->format('M d, Y') }}<br>
                                    <strong>Last Modified:</strong> {{ $screen->updated_at->format('M d, Y g:i A') }}
                                </div>
                                <div class="col-md-6">
                                    <strong>Current Grid:</strong> {{ $screen->grid_rows }}×{{ $screen->grid_columns }}<br>
                                    <strong>Status:</strong> 
                                    @if($screen->is_default)
                                        <span class="badge badge-success">Default Screen</span>
                                    @else
                                        <span class="badge badge-secondary">Regular Screen</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Preview -->
                        <div class="form-group">
                            <label>Preview</label>
                            <div class="screen-preview" id="screenPreview">
                                <div class="grid-preview" id="gridPreview"></div>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer">
                        <div class="d-flex justify-content-between">
                            <div>
                                <a href="{{ route('admin.screens.show', $screen) }}" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left"></i> Back to Designer
                                </a>
                                <a href="{{ route('admin.screens.index') }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-list"></i> All Screens
                                </a>
                            </div>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Update Screen
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@php
    // Prepare items array safely for JSON output
    $screenItems = $screen->screenItems->map(function($item) {
        return [
            'display_name' => $item->display_name,
            'background_color' => $item->getBackgroundColor(),
            'text_color' => $item->text_color,
            'grid_x' => $item->grid_x,
            'grid_y' => $item->grid_y,
            'width' => $item->width,
            'height' => $item->height,
        ];
    });
@endphp

@push('styles')
<style>
.screen-preview {
    height: 200px;
    border: 2px solid #dee2e6;
    border-radius: 8px;
    padding: 8px;
    transition: background-color 0.3s;
}

.grid-preview {
    height: 100%;
    display: grid;
    gap: 2px;
}

.grid-cell-preview {
    background: rgba(255,255,255,0.5);
    border: 1px dashed rgba(0,0,0,0.2);
    border-radius: 2px;
}

.grid-item-preview {
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 8px;
    font-weight: bold;
    border-radius: 2px;
    color: white;
    text-align: center;
    line-height: 1;
}

.input-group-text {
    min-width: 80px;
    font-family: monospace;
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const rowsSelect = document.getElementById('grid_rows');
    const columnsSelect = document.getElementById('grid_columns');
    const backgroundColorInput = document.getElementById('background_color');
    const screenPreview = document.getElementById('screenPreview');
    const gridPreview = document.getElementById('gridPreview');
    const colorDisplay = document.querySelector('.input-group-text');

    const screenItems = @json($screenItems);

    function updatePreview() {
        const rows = parseInt(rowsSelect.value) || {{ $screen->grid_rows }};
        const columns = parseInt(columnsSelect.value) || {{ $screen->grid_columns }};
        const backgroundColor = backgroundColorInput.value;

        screenPreview.style.backgroundColor = backgroundColor;
        colorDisplay.textContent = backgroundColor;

        gridPreview.style.gridTemplateRows = `repeat(${rows}, 1fr)`;
        gridPreview.style.gridTemplateColumns = `repeat(${columns}, 1fr)`;
        gridPreview.innerHTML = '';

        // Draw the base grid
        for (let i = 0; i < rows * columns; i++) {
            const cell = document.createElement('div');
            cell.className = 'grid-cell-preview';
            gridPreview.appendChild(cell);
        }

        // Add items
        screenItems.forEach(item => {
            const itemElement = document.createElement('div');
            itemElement.className = 'grid-item-preview';
            itemElement.style.backgroundColor = item.background_color;
            itemElement.style.color = item.text_color;
            itemElement.textContent = item.display_name;
            itemElement.style.gridRow = `${item.grid_y + 1} / span ${item.height}`;
            itemElement.style.gridColumn = `${item.grid_x + 1} / span ${item.width}`;
            gridPreview.appendChild(itemElement);
        });
    }

    rowsSelect.addEventListener('change', updatePreview);
    columnsSelect.addEventListener('change', updatePreview);
    backgroundColorInput.addEventListener('input', updatePreview);

    updatePreview();
});
</script>
@endpush
