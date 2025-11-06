@extends('layouts.admin')

@section('title', 'Create New Screen')
@section('page-title', 'Create New Screen')
@section('breadcrumb', 'Home > Admin > Screens > Create')

@section('styles')
<style>
    .page-container {
        background: white;
        border-radius: 15px;
        padding: 25px 30px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        margin-top: 25px;
    }

    .header-actions {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }

    .btn {
        padding: 8px 15px;
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
        font-size: 14px;
        text-decoration: none;
        border: none;
    }

    .btn-primary {
        background: linear-gradient(135deg,#667eea,#764ba2);
        color: #fff;
    }

    .btn-secondary {
        background: #f1f1f1;
        color: #2c3e50;
        border: 1px solid #ccc;
    }

    .btn-info {
        background: linear-gradient(135deg,#36cfc9,#007bff);
        color: #fff;
    }

    .btn-warning {
        background: linear-gradient(135deg,#f6d365,#fda085);
        color: #fff;
    }

    .btn-sm {
        padding: 5px 10px;
        font-size: 12px;
    }

    .form-group label {
        font-weight: 600;
        color: #2d3748;
    }

    .form-control {
        border-radius: 8px;
        border: 1px solid #cbd5e0;
        box-shadow: none;
    }

    .screen-preview {
        border: 2px solid #e2e8f0;
        border-radius: 12px;
        padding: 10px;
        background-color: #f8f9fa;
        height: 400px;
        overflow: hidden;
        margin-top: 15px;
        box-shadow: inset 0 2px 5px rgba(0,0,0,0.05);
    }

    .grid-preview {
        display: grid;
        width: 100%;
        height: 100%;
        gap: 6px;
    }

    .grid-cell-preview {
        background: rgba(255,255,255,0.8);
        border: 1px dashed #cbd5e0;
        border-radius: 6px;
        transition: all 0.2s ease;
        cursor: pointer;
    }

    .grid-cell-preview:hover {
        background: rgba(102,126,234,0.2);
        border-color: #667eea;
    }

    .modal-header {
        background: linear-gradient(135deg,#667eea,#764ba2);
        color: white;
    }

    .card-footer {
        background: transparent;
        border-top: none;
        text-align: right;
    }

    .card-footer button {
        min-width: 120px;
    }
</style>
@endsection

@section('content')
<div class="page-container">
    <div class="header-actions">
        <h3 class="mb-0">Create New Screen</h3>
        <div>
            <button type="button" id="btnLoadGroups" class="btn btn-info btn-sm">
                <i class="fas fa-layer-group"></i> Load Groups
            </button>
            <button type="button" id="btnLoadItems" class="btn btn-primary btn-sm" disabled>
                <i class="fas fa-box"></i> Load Items
            </button>
            <button type="button" id="btnAutoSetup" class="btn btn-warning btn-sm">
                <i class="fas fa-magic"></i> Auto Setup
            </button>
        </div>
    </div>

    <form action="{{ route('admin.screens.store') }}" method="POST" id="screenForm">
        @csrf
        @if(Auth::user()->isMasterAdmin())
            <input type="hidden" name="company_id" value="{{ $companyId }}">
        @endif

        <div class="form-group mb-3">
            <label for="name">Screen Name <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="name" name="name" required>
        </div>

        <div class="row mb-3">
            <div class="col-md-6">
                <label>Grid Rows</label>
                <select id="grid_rows" name="grid_rows" class="form-control">
                    @for($i=4; $i<=12; $i++)
                        <option value="{{ $i }}" {{ $i==8?'selected':'' }}>{{ $i }} rows</option>
                    @endfor
                </select>
            </div>
            <div class="col-md-6">
                <label>Grid Columns</label>
                <select id="grid_columns" name="grid_columns" class="form-control">
                    @for($i=4; $i<=10; $i++)
                        <option value="{{ $i }}" {{ $i==6?'selected':'' }}>{{ $i }} columns</option>
                    @endfor
                </select>
            </div>
        </div>

        <div class="form-group mb-3">
            <label for="background_color">Background Color</label>
            <input type="color" id="background_color" name="background_color" class="form-control" value="#f8f9fa">
        </div>

        <div class="form-group">
            <label>Screen Layout Preview</label>
            <div id="screenPreview" class="screen-preview">
                <div id="gridPreview" class="grid-preview"></div>
            </div>
        </div>

        <div class="card-footer mt-4">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Save Screen
            </button>
        </div>
    </form>
</div>

<!-- Modals -->
<div class="modal fade" id="groupModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header"><h5>Select Group</h5></div>
      <div class="modal-body" id="groupList">Loading groups...</div>
    </div>
  </div>
</div>

<div class="modal fade" id="itemModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header"><h5>Select Item</h5></div>
      <div class="modal-body" id="itemList">Select a group first.</div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const rowsSelect = document.getElementById('grid_rows');
    const colsSelect = document.getElementById('grid_columns');
    const bgColorInput = document.getElementById('background_color');
    const grid = document.getElementById('gridPreview');
    const screenPreview = document.getElementById('screenPreview');
    const btnGroups = document.getElementById('btnLoadGroups');
    const btnItems = document.getElementById('btnLoadItems');
    const btnAuto = document.getElementById('btnAutoSetup');
    let selectedGroup = null;

    function generateGrid() {
        grid.innerHTML = '';
        const rows = parseInt(rowsSelect.value);
        const cols = parseInt(colsSelect.value);
        grid.style.gridTemplateRows = `repeat(${rows}, 1fr)`;
        grid.style.gridTemplateColumns = `repeat(${cols}, 1fr)`;
        screenPreview.style.backgroundColor = bgColorInput.value;

        for (let i = 0; i < rows * cols; i++) {
            const cell = document.createElement('div');
            cell.classList.add('grid-cell-preview');
            cell.dataset.index = i;
            cell.addEventListener('click', () => {
                alert(`Clicked cell ${i + 1}`);
            });
            grid.appendChild(cell);
        }
    }

    [rowsSelect, colsSelect, bgColorInput].forEach(e => e.addEventListener('change', generateGrid));
    generateGrid();

    btnGroups.addEventListener('click', function() {
        $('#groupList').html('<div class="text-center">Loading...</div>');
        $('#groupModal').modal('show');

        fetch('/admin/screens/groups/json')
            .then(res => res.json())
            .then(data => {
                const groups = data?.screen_groups || [];
                const html = groups.map(g => `
                    <button class="btn btn-outline-primary btn-sm m-1 group-btn" data-id="${g.id}">
                        ${g.name}
                    </button>`).join('');
                $('#groupList').html(html);
                document.querySelectorAll('.group-btn').forEach(btn => {
                    btn.addEventListener('click', function() {
                        selectedGroup = this.dataset.id;
                        btnItems.disabled = false;
                        $('#groupModal').modal('hide');
                    });
                });
            });
    });

    btnItems.addEventListener('click', function() {
        if (!selectedGroup) return alert('Select a group first');
        $('#itemList').html('<div class="text-center">Loading...</div>');
        $('#itemModal').modal('show');

        fetch(`/admin/screens/items-by-group/${selectedGroup}`)
            .then(res => res.json())
            .then(data => {
                const items = data?.items || [];
                const html = items.map(p => `
                    <button class="btn btn-outline-success btn-sm m-1 item-btn" data-id="${p.id}">
                        ${p.name}
                    </button>`).join('');
                $('#itemList').html(html);
            });
    });

    btnAuto.addEventListener('click', function() {
        alert('Auto-setup layout logic will be added here.');
    });
});
</script>
@endpush
