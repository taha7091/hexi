@extends('layouts.admin')

@section('page-title', 'Screen Designer - ' . $screen->name)

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2>{{ $screen->name }}</h2>
                    <p class="text-muted">{{ $screen->description }}</p>
                </div>
                <div class="btn-group">
                    <a href="{{ route('admin.screens.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Screens
                    </a>
                    <a href="{{ route('admin.screens.edit', $screen) }}" class="btn btn-outline-primary">
                        <i class="fas fa-cog"></i> Settings
                    </a>
                    <button type="button" class="btn btn-info" id="syncToApp">
                        <i class="fas fa-sync"></i> Sync to App
                    </button>
                    <button type="button" class="btn btn-success" id="saveScreen">
                        <i class="fas fa-save"></i> Save
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Left Sidebar - Items Panel -->
        <div class="col-md-3">
            <div class="card">
                <div class="card-header">
                    <h5>Items</h5>
                </div>
                <div class="card-body">
                    <!-- Search -->
                    <div class="form-group">
                        <input type="text" class="form-control" id="itemSearch" placeholder="Search items...">
                    </div>

                    <!-- Products by Groups -->
                    <div class="products-by-groups">
                        @php
                            $productsByGroup = $products->groupBy('group.name');
                        @endphp

                        @foreach($productsByGroup as $groupName => $groupProducts)
                            <div class="group-section mb-3">
                                <div class="group-header" data-toggle="collapse" data-target="#group-{{ Str::slug($groupName) }}" aria-expanded="true">
                                    <h6 class="mb-0">
                                        <i class="fas fa-chevron-down"></i>
                                        {{ $groupName }}
                                        <span class="badge badge-secondary">{{ $groupProducts->count() }}</span>
                                    </h6>
                                </div>
                                <div class="collapse show" id="group-{{ Str::slug($groupName) }}">
                                    <div class="group-items mt-2">
                                        @foreach($groupProducts as $product)
                                            <div class="item-card" data-type="product" data-id="{{ $product->id }}" data-name="{{ $product->name }}" data-price="{{ $product->price }}">
                                                <div class="item-preview" style="background-color: {{ $product->group->color ?? '#007bff' }};">
                                                    <div class="item-name">{{ Str::limit($product->name, 12) }}</div>
                                                    <div class="item-price">${{ number_format($product->price, 2) }}</div>
                                                </div>
                                                <div class="item-info">
                                                    <small class="text-muted">{{ $product->group->name }}</small>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endforeach

                        <!-- Screen Groups -->
                        <div class="group-section mb-3">
                            <div class="group-header" data-toggle="collapse" data-target="#screen-groups" aria-expanded="false">
                                <h6 class="mb-0">
                                    <i class="fas fa-chevron-down"></i>
                                    Screen Groups
                                    <span class="badge badge-info">{{ $screenGroups->count() }}</span>
                                </h6>
                            </div>
                            <div class="collapse" id="screen-groups">
                                <div class="group-items mt-2">
                                    @foreach($screenGroups as $group)
                                        <div class="item-card" data-type="group" data-id="{{ $group->id }}" data-name="{{ $group->name }}">
                                            <div class="item-preview" style="background-color: {{ $group->color }};">
                                                <div class="item-name">{{ $group->name }}</div>
                                            </div>
                                            <div class="item-info">
                                                <small class="text-muted">{{ $group->description }}</small>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <!-- Category Screens -->
                        <div class="group-section mb-3">
                            <div class="group-header" data-toggle="collapse" data-target="#category-screens" aria-expanded="false">
                                <h6 class="mb-0">
                                    <i class="fas fa-chevron-down"></i>
                                    Category Screens
                                    <span class="badge badge-success">{{ $productsByGroup->count() }}</span>
                                </h6>
                            </div>
                            <div class="collapse" id="category-screens">
                                <div class="group-items mt-2">
                                    @foreach($productsByGroup as $groupName => $groupProducts)
                                        <div class="item-card" data-type="category_screen" data-id="{{ Str::slug($groupName) }}" data-name="{{ $groupName }}" data-group-name="{{ $groupName }}">
                                            <div class="item-preview" style="background-color: {{ $groupProducts->first()->group->color ?? '#28a745' }};">
                                                <div class="item-name">{{ $groupName }}</div>
                                                <div class="item-count">{{ $groupProducts->count() }} items</div>
                                            </div>
                                            <div class="item-info">
                                                <small class="text-muted">Category screen</small>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <!-- Action Items -->
                        <div class="group-section mb-3">
                            <div class="group-header" data-toggle="collapse" data-target="#action-items" aria-expanded="false">
                                <h6 class="mb-0">
                                    <i class="fas fa-chevron-down"></i>
                                    Actions
                                    <span class="badge badge-warning">4</span>
                                </h6>
                            </div>
                            <div class="collapse" id="action-items">
                                <div class="group-items mt-2">
                                    <div class="item-card" data-type="action" data-id="modifier" data-name="MODIFIER">
                                        <div class="item-preview" style="background-color: #ffc107;">
                                            <div class="item-name">MODIFIER</div>
                                        </div>
                                        <div class="item-info">
                                            <small class="text-muted">Product modifier</small>
                                        </div>
                                    </div>
                                    <div class="item-card" data-type="action" data-id="discount" data-name="DISCOUNT">
                                        <div class="item-preview" style="background-color: #dc3545;">
                                            <div class="item-name">DISCOUNT</div>
                                        </div>
                                        <div class="item-info">
                                            <small class="text-muted">Apply discount</small>
                                        </div>
                                    </div>
                                    <div class="item-card" data-type="action" data-id="clear" data-name="CLEAR">
                                        <div class="item-preview" style="background-color: #6c757d;">
                                            <div class="item-name">CLEAR</div>
                                        </div>
                                        <div class="item-info">
                                            <small class="text-muted">Clear transaction</small>
                                        </div>
                                    </div>
                                    <div class="item-card" data-type="action" data-id="total" data-name="TOTAL">
                                        <div class="item-preview" style="background-color: #28a745;">
                                            <div class="item-name">TOTAL</div>
                                        </div>
                                        <div class="item-info">
                                            <small class="text-muted">Calculate total</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Screen Designer -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h5 class="mb-0">Screen Layout - {{ $screen->name }}</h5>
                        <div class="btn-group btn-group-sm">
                            <button type="button" class="btn btn-outline-secondary" id="clearScreen">
                                <i class="fas fa-eraser"></i> Clear Screen
                            </button>
                            <button type="button" class="btn btn-outline-info" id="autoSetup">
                                <i class="fas fa-magic"></i> Auto Setup Products
                            </button>
                            <button type="button" class="btn btn-outline-success" id="createMainScreen">
                                <i class="fas fa-th-large"></i> Create Main Screen
                            </button>
                            <button type="button" class="btn btn-outline-warning" id="createCategoryScreens">
                                <i class="fas fa-layer-group"></i> Create Category Screens
                            </button>
                        </div>
                    </div>
                    <div class="alert alert-info alert-sm mb-0 py-2">
                        <small>
                            <strong>💡 Tip:</strong>
                            <strong>Main Screen:</strong> Create category buttons that link to specific screens.
                            <strong>Category Screens:</strong> Create dedicated screens for each product group.
                            <strong>Drag & Drop:</strong> Drag items from the left panel to the grid.
                        </small>
                    </div>
                </div>
                <div class="card-body p-2">
                    <div class="screen-designer" id="screenDesigner" 
                         style="background-color: {{ $screen->background_color }};"
                         data-rows="{{ $screen->grid_rows }}" 
                         data-columns="{{ $screen->grid_columns }}">
                        <!-- Grid will be generated by JavaScript -->
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Sidebar - Properties Panel -->
        <div class="col-md-3">
            <div class="card">
                <div class="card-header">
                    <h5>Properties</h5>
                </div>
                <div class="card-body">
                    <div id="propertiesPanel">
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-mouse-pointer fa-2x mb-2"></i>
                            <p>Select an item to edit its properties</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="card mt-3">
                <div class="card-header">
                    <h5>Actions</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button type="button" class="btn btn-primary" id="colorBtn">
                            <i class="fas fa-palette"></i> Color
                        </button>
                        <button type="button" class="btn btn-warning" id="sameColorBtn">
                            <i class="fas fa-fill-drip"></i> Same Color
                        </button>
                        <button type="button" class="btn btn-info" id="createLikeBtn">
                            <i class="fas fa-copy"></i> Create Like
                        </button>
                        <button type="button" class="btn btn-success" id="automaticSetupBtn">
                            <i class="fas fa-cogs"></i> Automatic Setup
                        </button>
                        <button type="button" class="btn btn-success" id="saveBtn">
                            <i class="fas fa-save"></i> Save
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Item Properties Modal -->
<div class="modal fade" id="itemPropertiesModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Item Properties</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="itemPropertiesForm">
                    <div class="form-group">
                        <label>Display Name</label>
                        <input type="text" class="form-control" id="itemDisplayName" required>
                    </div>
                    <div class="row">
                        <div class="col-6">
                            <div class="form-group">
                                <label>Background Color</label>
                                <input type="color" class="form-control" id="itemBackgroundColor">
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group">
                                <label>Text Color</label>
                                <input type="color" class="form-control" id="itemTextColor">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-6">
                            <div class="form-group">
                                <label>Width</label>
                                <select class="form-control" id="itemWidth">
                                    <option value="1">1</option>
                                    <option value="2">2</option>
                                    <option value="3">3</option>
                                    <option value="4">4</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group">
                                <label>Height</label>
                                <select class="form-control" id="itemHeight">
                                    <option value="1">1</option>
                                    <option value="2">2</option>
                                    <option value="3">3</option>
                                    <option value="4">4</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="saveItemProperties">Save</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
/* Group Sections */
.group-section {
    border: 1px solid #e9ecef;
    border-radius: 6px;
    overflow: hidden;
}

.group-header {
    background: #f8f9fa;
    padding: 10px 12px;
    cursor: pointer;
    border-bottom: 1px solid #e9ecef;
    transition: background-color 0.2s;
}

.group-header:hover {
    background: #e9ecef;
}

.group-header h6 {
    color: #495057;
    font-weight: 600;
}

.group-header .fas {
    transition: transform 0.2s;
    margin-right: 8px;
}

.group-header[aria-expanded="false"] .fas {
    transform: rotate(-90deg);
}

.group-items {
    padding: 8px;
    background: white;
}

/* Item Cards */
.item-card {
    margin-bottom: 8px;
    cursor: grab;
    border: 1px solid #dee2e6;
    border-radius: 6px;
    background: white;
    transition: all 0.2s;
    overflow: hidden;
}

.item-card:hover {
    border-color: #007bff;
    box-shadow: 0 3px 8px rgba(0,123,255,0.15);
    transform: translateY(-1px);
}

.item-card:active {
    cursor: grabbing;
    transform: scale(0.98);
}

.item-preview {
    padding: 10px 8px;
    border-radius: 4px;
    color: white;
    text-align: center;
    font-weight: bold;
    min-height: 50px;
    display: flex;
    flex-direction: column;
    justify-content: center;
    position: relative;
}

.item-name {
    font-size: 11px;
    line-height: 1.2;
    margin-bottom: 2px;
}

.item-price {
    font-size: 10px;
    opacity: 0.9;
    font-weight: normal;
}

.item-info {
    padding: 4px 8px;
    background: #f8f9fa;
    text-align: center;
}

/* Screen Designer */
.screen-designer {
    min-height: 600px;
    border: 3px solid #007bff;
    border-radius: 12px;
    position: relative;
    overflow: hidden;
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
}

.grid-container {
    display: grid;
    height: 100%;
    gap: 3px;
    padding: 8px;
    background: rgba(0,0,0,0.05);
}

.grid-cell {
    border: 2px dashed #007bff;
    border-radius: 6px;
    position: relative;
    min-height: 80px;
    transition: all 0.3s;
    background: rgba(255,255,255,0.8);
    display: flex;
    align-items: center;
    justify-content: center;
}

.grid-cell:hover {
    border-color: #0056b3;
    background: rgba(0,123,255,0.1);
    transform: scale(1.02);
    box-shadow: 0 4px 8px rgba(0,123,255,0.2);
}

.grid-cell::before {
    content: attr(data-row) ',' attr(data-col);
    position: absolute;
    top: 2px;
    left: 4px;
    font-size: 10px;
    color: #6c757d;
    opacity: 0.7;
}

.grid-cell.drop-target {
    border-color: #007bff;
    background: rgba(0,123,255,0.1);
}

.grid-item {
    position: relative; /* Let CSS Grid place items by grid-row/column */
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    border-radius: 8px;
    font-weight: bold;
    color: white;
    cursor: pointer;
    transition: all 0.3s;
    border: 3px solid transparent;
    font-size: 13px;
    text-align: center;
    line-height: 1.2;
    padding: 8px 4px;
    word-wrap: break-word;
    background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
    box-shadow: 0 2px 8px rgba(0,0,0,0.15);
}

.grid-item:hover {
    transform: scale(1.05);
    box-shadow: 0 4px 12px rgba(0,0,0,0.25);
    border-color: #ffc107;
}

.grid-item.selected {
    border-color: #ffc107;
    box-shadow: 0 0 0 3px rgba(255,193,7,0.5);
    background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%);
    color: #000;
}

.grid-item .item-name {
    font-size: 12px;
    margin-bottom: 2px;
}

.grid-item .item-price {
    font-size: 10px;
    opacity: 0.9;
    font-weight: normal;
}

.grid-item.multi-cell {
    z-index: 10;
}

/* Item List */
.item-list {
    max-height: 400px;
    overflow-y: auto;
}

/* Properties Panel */
#propertiesPanel .form-group {
    margin-bottom: 15px;
}

/* Alert styling */
.alert-sm {
    padding: 0.5rem 0.75rem;
    font-size: 0.875rem;
}

/* Responsive */
@media (max-width: 768px) {
    .screen-designer {
        min-height: 300px;
    }

    .grid-item {
        font-size: 10px;
    }
}
</style>
@endpush

@push('scripts')
<script>
class ScreenDesigner {
    constructor(screenId, gridRows, gridColumns) {
        this.screenId = screenId;
        this.gridRows = gridRows;
        this.gridColumns = gridColumns;
        this.selectedItem = null;
        this.screenItems = [];

        this.initializeGrid();
        this.loadScreenItems();
        this.bindEvents();
    }

    initializeGrid() {
        const designer = document.getElementById('screenDesigner');
        const gridContainer = document.createElement('div');
        gridContainer.className = 'grid-container';
        gridContainer.style.gridTemplateRows = `repeat(${this.gridRows}, 1fr)`;
        gridContainer.style.gridTemplateColumns = `repeat(${this.gridColumns}, 1fr)`;

        // Create grid cells
        for (let row = 0; row < this.gridRows; row++) {
            for (let col = 0; col < this.gridColumns; col++) {
                const cell = document.createElement('div');
                cell.className = 'grid-cell';
                cell.dataset.row = row;
                cell.dataset.col = col;
                gridContainer.appendChild(cell);
            }
        }

        designer.appendChild(gridContainer);
    }

    loadScreenItems() {
        // Load existing screen items from server
        fetch(`/api/screens/${this.screenId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    this.screenItems = data.screen.items;
                    this.renderItems();
                }
            })
            .catch(error => console.error('Error loading screen items:', error));
    }

    renderItems() {
        // Clear existing items
        document.querySelectorAll('.grid-item').forEach(item => item.remove());

        // Render each item
        this.screenItems.forEach(item => {
            this.renderItem(item);
        });
    }

    renderItem(item) {
        const gridContainer = document.querySelector('.grid-container');
        const itemElement = document.createElement('div');
        itemElement.className = 'grid-item';
        itemElement.dataset.itemId = item.id;
        itemElement.style.backgroundColor = item.background_color;
        itemElement.style.color = item.text_color;
        itemElement.style.gridRow = `${item.grid_y + 1} / span ${item.height}`;
        itemElement.style.gridColumn = `${item.grid_x + 1} / span ${item.width}`;

        // Create structured content for better display
        const nameElement = document.createElement('div');
        nameElement.className = 'item-name';
        nameElement.textContent = item.display_name;
        itemElement.appendChild(nameElement);

        // Add price for products
        if (item.type === 'product' && item.product && item.product.price) {
            const priceElement = document.createElement('div');
            priceElement.className = 'item-price';
            priceElement.textContent = `$${parseFloat(item.product.price).toFixed(2)}`;
            itemElement.appendChild(priceElement);
        }

        // Add item count for category screens
        if (item.type === 'category_screen' && item.custom_properties && item.custom_properties.item_count) {
            const countElement = document.createElement('div');
            countElement.className = 'item-price';
            countElement.textContent = `${item.custom_properties.item_count} items`;
            itemElement.appendChild(countElement);
        }

        if (item.width > 1 || item.height > 1) {
            itemElement.classList.add('multi-cell');
        }

        gridContainer.appendChild(itemElement);
    }

    bindEvents() {
        // Drag and drop from item list
        this.bindDragAndDrop();

        // Grid item selection
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('grid-item')) {
                this.selectItem(e.target);
            } else if (!e.target.closest('#propertiesPanel')) {
                this.deselectItem();
            }
        });

        // Save button
        document.getElementById('saveScreen').addEventListener('click', () => {
            this.saveScreen();
        });

        // Sync to App button
        document.getElementById('syncToApp').addEventListener('click', () => {
            this.syncToApp();
        });

        // Clear screen button
        document.getElementById('clearScreen').addEventListener('click', () => {
            this.clearScreen();
        });

        // Auto setup button
        document.getElementById('autoSetup').addEventListener('click', () => {
            this.autoSetup();
        });

        // Create main screen button
        document.getElementById('createMainScreen').addEventListener('click', () => {
            this.createMainScreen();
        });

        // Create category screens button
        document.getElementById('createCategoryScreens').addEventListener('click', () => {
            this.createCategoryScreens();
        });
    }

    bindDragAndDrop() {
        // Make item cards draggable
        document.querySelectorAll('.item-card').forEach(card => {
            card.draggable = true;

            card.addEventListener('dragstart', (e) => {
                e.dataTransfer.setData('text/plain', JSON.stringify({
                    type: card.dataset.type,
                    id: card.dataset.id,
                    name: card.dataset.name
                }));
            });
        });

        // Make grid cells drop targets
        document.querySelectorAll('.grid-cell').forEach(cell => {
            cell.addEventListener('dragover', (e) => {
                e.preventDefault();
                cell.classList.add('drop-target');
            });

            cell.addEventListener('dragleave', (e) => {
                cell.classList.remove('drop-target');
            });

            cell.addEventListener('drop', (e) => {
                e.preventDefault();
                cell.classList.remove('drop-target');

                const data = JSON.parse(e.dataTransfer.getData('text/plain'));
                const row = parseInt(cell.dataset.row);
                const col = parseInt(cell.dataset.col);

                this.addItemToGrid(data, col, row);
            });
        });
    }

    addItemToGrid(itemData, x, y) {
        // Check if position is available
        if (this.isPositionOccupied(x, y)) {
            alert('Position is already occupied');
            return;
        }

        // Show properties modal for new item
        this.showItemPropertiesModal(itemData, x, y);
    }

    isPositionOccupied(x, y, width = 1, height = 1, excludeItemId = null) {
        return this.screenItems.some(item => {
            if (excludeItemId && item.id === excludeItemId) return false;

            return !(
                x >= item.grid_x + item.width ||
                x + width <= item.grid_x ||
                y >= item.grid_y + item.height ||
                y + height <= item.grid_y
            );
        });
    }

    showItemPropertiesModal(itemData, x, y, existingItem = null) {
        const modal = document.getElementById('itemPropertiesModal');
        const form = document.getElementById('itemPropertiesForm');

        // Populate form
        document.getElementById('itemDisplayName').value = existingItem ? existingItem.display_name : itemData.name;
        document.getElementById('itemBackgroundColor').value = existingItem ? existingItem.background_color : '#007bff';
        document.getElementById('itemTextColor').value = existingItem ? existingItem.text_color : '#ffffff';
        document.getElementById('itemWidth').value = existingItem ? existingItem.width : 1;
        document.getElementById('itemHeight').value = existingItem ? existingItem.height : 1;

        // Show modal
        $(modal).modal('show');

        // Handle save
        document.getElementById('saveItemProperties').onclick = () => {
            const properties = {
                display_name: document.getElementById('itemDisplayName').value,
                background_color: document.getElementById('itemBackgroundColor').value,
                text_color: document.getElementById('itemTextColor').value,
                width: parseInt(document.getElementById('itemWidth').value),
                height: parseInt(document.getElementById('itemHeight').value),
                grid_x: x,
                grid_y: y,
                type: itemData.type,
                product_id: itemData.type === 'product' ? itemData.id : null,
                screen_group_id: itemData.type === 'group' ? itemData.id : null
            };

            if (existingItem) {
                this.updateItem(existingItem.id, properties);
            } else {
                this.createItem(properties);
            }

            $(modal).modal('hide');
        };
    }

    createItem(properties) {
        fetch(`/admin/screens/${this.screenId}/items`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify(properties)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                this.screenItems.push(data.item);
                this.renderItem(data.item);
            } else {
                alert(data.message || 'Error creating item');
            }
        })
        .catch(error => {
            console.error('Error creating item:', error);
            alert('Error creating item');
        });
    }

    updateItem(itemId, properties) {
        fetch(`/admin/screens/${this.screenId}/items/${itemId}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify(properties)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const index = this.screenItems.findIndex(item => item.id === itemId);
                if (index !== -1) {
                    this.screenItems[index] = data.item;
                    this.renderItems();
                }
            } else {
                alert(data.message || 'Error updating item');
            }
        })
        .catch(error => {
            console.error('Error updating item:', error);
            alert('Error updating item');
        });
    }

    selectItem(itemElement) {
        // Deselect previous item
        this.deselectItem();

        // Select new item
        itemElement.classList.add('selected');
        this.selectedItem = itemElement;

        // Show properties
        const itemId = itemElement.dataset.itemId;
        const item = this.screenItems.find(item => item.id == itemId);
        if (item) {
            this.showItemProperties(item);
        }
    }

    deselectItem() {
        if (this.selectedItem) {
            this.selectedItem.classList.remove('selected');
            this.selectedItem = null;
        }
        this.hideItemProperties();
    }

    showItemProperties(item) {
        const panel = document.getElementById('propertiesPanel');
        panel.innerHTML = `
            <div class="form-group">
                <label>Display Name</label>
                <input type="text" class="form-control" value="${item.display_name}" onchange="screenDesigner.updateSelectedItemProperty('display_name', this.value)">
            </div>
            <div class="row">
                <div class="col-6">
                    <div class="form-group">
                        <label>Background</label>
                        <input type="color" class="form-control" value="${item.background_color}" onchange="screenDesigner.updateSelectedItemProperty('background_color', this.value)">
                    </div>
                </div>
                <div class="col-6">
                    <div class="form-group">
                        <label>Text Color</label>
                        <input type="color" class="form-control" value="${item.text_color}" onchange="screenDesigner.updateSelectedItemProperty('text_color', this.value)">
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-6">
                    <div class="form-group">
                        <label>Width</label>
                        <select class="form-control" onchange="screenDesigner.updateSelectedItemProperty('width', parseInt(this.value))">
                            <option value="1" ${item.width === 1 ? 'selected' : ''}>1</option>
                            <option value="2" ${item.width === 2 ? 'selected' : ''}>2</option>
                            <option value="3" ${item.width === 3 ? 'selected' : ''}>3</option>
                            <option value="4" ${item.width === 4 ? 'selected' : ''}>4</option>
                        </select>
                    </div>
                </div>
                <div class="col-6">
                    <div class="form-group">
                        <label>Height</label>
                        <select class="form-control" onchange="screenDesigner.updateSelectedItemProperty('height', parseInt(this.value))">
                            <option value="1" ${item.height === 1 ? 'selected' : ''}>1</option>
                            <option value="2" ${item.height === 2 ? 'selected' : ''}>2</option>
                            <option value="3" ${item.height === 3 ? 'selected' : ''}>3</option>
                            <option value="4" ${item.height === 4 ? 'selected' : ''}>4</option>
                        </select>
                    </div>
                </div>
            </div>
            <button type="button" class="btn btn-danger btn-sm" onclick="screenDesigner.deleteSelectedItem()">
                <i class="fas fa-trash"></i> Delete Item
            </button>
        `;
    }

    hideItemProperties() {
        const panel = document.getElementById('propertiesPanel');
        panel.innerHTML = `
            <div class="text-center text-muted py-4">
                <i class="fas fa-mouse-pointer fa-2x mb-2"></i>
                <p>Select an item to edit its properties</p>
            </div>
        `;
    }

    updateSelectedItemProperty(property, value) {
        if (!this.selectedItem) return;

        const itemId = this.selectedItem.dataset.itemId;
        const item = this.screenItems.find(item => item.id == itemId);
        if (!item) return;

        // Update local data
        item[property] = value;

        // Update visual representation
        if (property === 'display_name') {
            this.selectedItem.textContent = value;
        } else if (property === 'background_color') {
            this.selectedItem.style.backgroundColor = value;
        } else if (property === 'text_color') {
            this.selectedItem.style.color = value;
        } else if (property === 'width' || property === 'height') {
            this.renderItems(); // Re-render to update grid positioning
        }

        // Save to server (debounced)
        clearTimeout(this.saveTimeout);
        this.saveTimeout = setTimeout(() => {
            this.saveItemToServer(item);
        }, 500);
    }

    saveItemToServer(item) {
        fetch(`/admin/screens/${this.screenId}/items/${item.id}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify(item)
        })
        .catch(error => console.error('Error saving item:', error));
    }

    deleteSelectedItem() {
        if (!this.selectedItem) return;

        if (!confirm('Are you sure you want to delete this item?')) return;

        const itemId = this.selectedItem.dataset.itemId;

        fetch(`/admin/screens/${this.screenId}/items/${itemId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                this.screenItems = this.screenItems.filter(item => item.id != itemId);
                this.selectedItem.remove();
                this.deselectItem();
            }
        })
        .catch(error => console.error('Error deleting item:', error));
    }

    clearScreen() {
        if (!confirm('Are you sure you want to clear all items from this screen?')) return;

        fetch(`/admin/screens/${this.screenId}/clear`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                this.screenItems = [];
                this.renderItems();
                this.deselectItem();
            }
        })
        .catch(error => console.error('Error clearing screen:', error));
    }

    autoSetup() {
        // Auto-populate screen with products grouped by category
        const products = @json($products);
        const productsByGroup = {};

        // Group products by their group name
        products.forEach(product => {
            const groupName = product.group?.name || 'Ungrouped';
            if (!productsByGroup[groupName]) {
                productsByGroup[groupName] = [];
            }
            productsByGroup[groupName].push(product);
        });

        let x = 0, y = 0;
        const maxItems = this.gridRows * this.gridColumns;
        let itemCount = 0;

        // Iterate through groups and place products
        Object.keys(productsByGroup).forEach(groupName => {
            const groupProducts = productsByGroup[groupName];
            const groupColor = groupProducts[0]?.group?.color || this.getRandomColor();

            groupProducts.forEach(product => {
                if (itemCount >= maxItems) return;

                if (!this.isPositionOccupied(x, y)) {
                    const properties = {
                        display_name: product.name,
                        background_color: groupColor,
                        text_color: '#ffffff',
                        width: 1,
                        height: 1,
                        grid_x: x,
                        grid_y: y,
                        type: 'product',
                        product_id: product.id,
                        screen_group_id: null
                    };

                    this.createItem(properties);
                    itemCount++;
                }

                x++;
                if (x >= this.gridColumns) {
                    x = 0;
                    y++;
                }
            });
        });

        // Show success message
        this.showNotification(`Auto setup completed! Added ${itemCount} products organized by groups.`, 'success');
    }

    getRandomColor() {
        const colors = ['#007bff', '#28a745', '#dc3545', '#ffc107', '#17a2b8', '#6f42c1', '#e83e8c', '#fd7e14'];
        return colors[Math.floor(Math.random() * colors.length)];
    }

    createMainScreen() {
        // Clear current screen first
        this.clearScreen();

        // Get unique product groups
        const products = @json($products);
        const productsByGroup = {};

        products.forEach(product => {
            const groupName = product.group?.name || 'Ungrouped';
            if (!productsByGroup[groupName]) {
                productsByGroup[groupName] = {
                    name: groupName,
                    color: product.group?.color || this.getRandomColor(),
                    count: 0
                };
            }
            productsByGroup[groupName].count++;
        });

        let x = 0, y = 0;
        const maxItems = this.gridRows * this.gridColumns;
        let itemCount = 0;

        // Create category buttons for main screen
        Object.values(productsByGroup).forEach(group => {
            if (itemCount >= maxItems) return;

            if (!this.isPositionOccupied(x, y)) {
                const properties = {
                    display_name: group.name,
                    background_color: group.color,
                    text_color: '#ffffff',
                    width: 2, // Make category buttons larger
                    height: 2,
                    grid_x: x,
                    grid_y: y,
                    type: 'category_screen',
                    product_id: null,
                    screen_group_id: null,
                    custom_properties: {
                        category_name: group.name,
                        item_count: group.count
                    }
                };

                this.createItem(properties);
                itemCount++;
            }

            x += 2; // Skip one column for larger buttons
            if (x >= this.gridColumns) {
                x = 0;
                y += 2; // Skip one row for larger buttons
            }
        });

        // Add action buttons in remaining space
        const actionButtons = [
            { name: 'TOTAL', color: '#28a745', type: 'action', id: 'total' },
            { name: 'CLEAR', color: '#6c757d', type: 'action', id: 'clear' },
            { name: 'DISCOUNT', color: '#dc3545', type: 'action', id: 'discount' }
        ];

        actionButtons.forEach(action => {
            if (itemCount >= maxItems) return;

            // Find next available position
            while (this.isPositionOccupied(x, y) && y < this.gridRows) {
                x++;
                if (x >= this.gridColumns) {
                    x = 0;
                    y++;
                }
            }

            if (y < this.gridRows && !this.isPositionOccupied(x, y)) {
                const properties = {
                    display_name: action.name,
                    background_color: action.color,
                    text_color: '#ffffff',
                    width: 1,
                    height: 1,
                    grid_x: x,
                    grid_y: y,
                    type: action.type,
                    product_id: null,
                    screen_group_id: null
                };

                this.createItem(properties);
                itemCount++;
            }

            x++;
            if (x >= this.gridColumns) {
                x = 0;
                y++;
            }
        });

        this.showNotification(`Main screen created with ${Object.keys(productsByGroup).length} category buttons!`, 'success');
    }

    createCategoryScreens() {
        // This would create separate screens for each category
        const products = @json($products);
        const productsByGroup = {};

        products.forEach(product => {
            const groupName = product.group?.name || 'Ungrouped';
            if (!productsByGroup[groupName]) {
                productsByGroup[groupName] = [];
            }
            productsByGroup[groupName].push(product);
        });

        let screensToCreate = Object.keys(productsByGroup).length;
        let screensCreated = 0;

        // Show confirmation dialog
        if (confirm(`This will create ${screensToCreate} new screens (one for each category). Continue?`)) {
            Object.keys(productsByGroup).forEach(groupName => {
                this.createCategoryScreen(groupName, productsByGroup[groupName]);
                screensCreated++;
            });

            this.showNotification(`${screensCreated} category screens will be created. Check the screens list to see them.`, 'info');
        }
    }

    createCategoryScreen(categoryName, products) {
        // This would make an API call to create a new screen for the category
        fetch('/admin/screens', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                name: `${categoryName} Screen`,
                description: `Auto-generated screen for ${categoryName} products`,
                grid_rows: this.gridRows,
                grid_columns: this.gridColumns,
                background_color: products[0]?.group?.color || '#f8f9fa',
                is_default: false
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                console.log(`Created screen for ${categoryName}:`, data.screen);
            }
        })
        .catch(error => {
            console.error(`Failed to create screen for ${categoryName}:`, error);
        });
    }

    saveScreen() {
        // Show success message
        const btn = document.getElementById('saveScreen');
        const originalText = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-check"></i> Saved!';
        btn.classList.remove('btn-success');
        btn.classList.add('btn-outline-success');

        setTimeout(() => {
            btn.innerHTML = originalText;
            btn.classList.remove('btn-outline-success');
            btn.classList.add('btn-success');
        }, 2000);
    }

    syncToApp() {
        const btn = document.getElementById('syncToApp');
        const originalText = btn.innerHTML;

        // Show syncing state
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Syncing...';
        btn.disabled = true;

        // Call sync API
        fetch(`/admin/screens/${this.screenId}/sync`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                btn.innerHTML = '<i class="fas fa-check"></i> Synced!';
                btn.classList.remove('btn-info');
                btn.classList.add('btn-success');

                // Show success notification
                this.showNotification('Screen synced to app successfully!', 'success');
            } else {
                throw new Error(data.message || 'Sync failed');
            }
        })
        .catch(error => {
            console.error('Sync error:', error);
            btn.innerHTML = '<i class="fas fa-exclamation-triangle"></i> Sync Failed';
            btn.classList.remove('btn-info');
            btn.classList.add('btn-danger');

            // Show error notification
            this.showNotification('Failed to sync screen to app. Please try again.', 'error');
        })
        .finally(() => {
            // Reset button after 3 seconds
            setTimeout(() => {
                btn.innerHTML = originalText;
                btn.classList.remove('btn-success', 'btn-danger');
                btn.classList.add('btn-info');
                btn.disabled = false;
            }, 3000);
        });
    }

    showNotification(message, type = 'info') {
        // Create notification element
        const notification = document.createElement('div');
        notification.className = `alert alert-${type === 'error' ? 'danger' : type} alert-dismissible fade show position-fixed`;
        notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
        notification.innerHTML = `
            ${message}
            <button type="button" class="close" data-dismiss="alert">
                <span>&times;</span>
            </button>
        `;

        // Add to page
        document.body.appendChild(notification);

        // Auto remove after 5 seconds
        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, 5000);
    }
}

// Initialize screen designer when page loads
document.addEventListener('DOMContentLoaded', function() {
    const screenDesigner = new ScreenDesigner(
        {{ $screen->id }},
        {{ $screen->grid_rows }},
        {{ $screen->grid_columns }}
    );

    // Make it globally accessible
    window.screenDesigner = screenDesigner;

    // Search functionality
    document.getElementById('itemSearch').addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        document.querySelectorAll('.item-card').forEach(card => {
            const name = card.dataset.name.toLowerCase();
            card.style.display = name.includes(searchTerm) ? 'block' : 'none';
        });
    });
});
</script>
@endpush
