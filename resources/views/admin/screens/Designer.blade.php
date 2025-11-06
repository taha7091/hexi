    @extends('layouts.admin')

    @section('page-title', 'Screen Designer - ' . $screen->name)

    @section('content')
    <div class="container-fluid">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title">Design Screen: {{ $screen->name }}</h3>
                <a href="{{ route('admin.screens.index') }}" class="btn btn-secondary btn-sm">
                    <i class="fas fa-arrow-left"></i> Back
                </a>
            </div>

            <div class="card-body">
                <div class="d-flex gap-4">
                    <!-- LEFT SIDEBAR: PRODUCT LIST -->
                    <div class="border p-2 rounded" style="width:220px; height:550px; overflow-y:auto;">
                        @foreach($products as $product)
                            <div class="p-2 mb-1 border rounded text-center bg-light product-item"
                                draggable="true"
                                data-id="{{ $product->id }}">
                                {{ $product->name }}
                            </div>
                        @endforeach
                    </div>

                    <!-- RIGHT: GRID DESIGNER -->
                    <div class="flex-grow-1">
                        <div class="mb-3 d-flex flex-wrap gap-2">
                            <button class="btn btn-sm btn-outline-primary" id="btnClear">
                                <i class="fas fa-eraser"></i> Clear
                            </button>
                            <button class="btn btn-sm btn-outline-warning" id="btnAuto">
                                <i class="fas fa-magic"></i> Auto Setup
                            </button>
                            <button class="btn btn-sm btn-success" id="btnSave">
                                <i class="fas fa-save"></i> Save Layout
                            </button>
                        </div>

                        <div id="gridContainer" class="grid-container border rounded"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
    .grid-container {
        display: grid;
        grid-template-columns: repeat({{ $screen->grid_columns }}, 120px);
        grid-template-rows: repeat({{ $screen->grid_rows }}, 80px);
        gap: 5px;
        background: #f8f9fa;
        padding: 5px;
    }

    .grid-cell {
        border: 1px solid #ccc;
        background: white;
        text-align: center;
        vertical-align: middle;
        font-weight: 500;
        color: #333;
        cursor: pointer;
        line-height: 80px;
    }

    .grid-cell.filled {
        background: #d1e7dd;
        font-weight: bold;
    }
    </style>

    <script>
    const products = document.querySelectorAll('.product-item');
    const gridContainer = document.getElementById('gridContainer');

    // build grid
    for (let y = 0; y < {{ $screen->grid_rows }}; y++) {
        for (let x = 0; x < {{ $screen->grid_columns }}; x++) {
            const cell = document.createElement('div');
            cell.classList.add('grid-cell');
            cell.dataset.x = x;
            cell.dataset.y = y;
            gridContainer.appendChild(cell);
        }
    }

    // load existing items
    @foreach($screen->screenItems as $item)
    let cell = gridContainer.querySelector(`[data-x="{{ $item->grid_x }}"][data-y="{{ $item->grid_y }}"]`);
    if (cell) {
        cell.textContent = "{{ $item->display_name }}";
        cell.classList.add('filled');
    }
    @endforeach

    let dragged = null;
    products.forEach(p => {
        p.addEventListener('dragstart', e => {
            dragged = { id: p.dataset.id, name: p.textContent.trim() };
        });
    });

    gridContainer.addEventListener('dragover', e => e.preventDefault());
    gridContainer.addEventListener('drop', async e => {
        if (!e.target.classList.contains('grid-cell')) return;
        e.preventDefault();
        const x = e.target.dataset.x;
        const y = e.target.dataset.y;

        const res = await fetch("{{ route('screens.designer.addItem1', $screen) }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ product_id: dragged.id, x, y })
        });

        if (res.ok) {
            e.target.textContent = dragged.name;
            e.target.classList.add('filled');
        }
    });

    // clear screen
    document.getElementById('btnClear').addEventListener('click', async () => {
        if (!confirm('Clear all items from this screen?')) return;
        await fetch("{{ route('screens.designer.clear', $screen) }}", {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
        });
        document.querySelectorAll('.grid-cell').forEach(c => {
            c.textContent = '';
            c.classList.remove('filled');
        });
    });

    document.getElementById('btnAuto').addEventListener('click', () => {
        alert('Auto-setup logic will go here.');
    });

    document.getElementById('btnSave').addEventListener('click', () => {
        alert('Layout is auto-saved on drop!');
    });
    </script>
    @endsection
