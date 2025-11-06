@extends('layouts.admin')

@section('title', 'POS Layout Designer - ERP System')

@section('page-title', 'POS Layout Designer')

@section('breadcrumb', 'Home > Admin > POS Layouts > Designer')

@section('styles')
<style>
    .layout-designer {
        display: grid;
        grid-template-columns: 300px 1fr;
        gap: 30px;
        height: calc(100vh - 200px);
    }
    
    .groups-panel {
        background: white;
        border-radius: 15px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        padding: 25px;
        overflow-y: auto;
    }
    
    .groups-panel h3 {
        margin: 0 0 20px 0;
        color: #2d3748;
        font-size: 18px;
        border-bottom: 2px solid #e2e8f0;
        padding-bottom: 10px;
    }
    
    .group-item {
        background: linear-gradient(135deg, #667eea, #764ba2);
        color: white;
        padding: 15px;
        margin-bottom: 15px;
        border-radius: 12px;
        cursor: grab;
        transition: all 0.3s ease;
        box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
        user-select: none;
    }
    
    .group-item:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
    }
    
    .group-item:active {
        cursor: grabbing;
        transform: scale(0.95);
    }
    
    .group-name {
        font-weight: 700;
        font-size: 16px;
        margin-bottom: 5px;
    }
    
    .group-info {
        font-size: 12px;
        opacity: 0.9;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .product-count {
        background: rgba(255,255,255,0.2);
        padding: 4px 8px;
        border-radius: 6px;
        font-weight: 600;
    }
    
    .canvas-container {
        background: white;
        border-radius: 15px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        position: relative;
        overflow: hidden;
    }
    
    .canvas-header {
        padding: 20px 25px;
        border-bottom: 2px solid #e2e8f0;
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: #f8f9fa;
    }
    
    .canvas-title {
        font-size: 18px;
        font-weight: 700;
        color: #2d3748;
        margin: 0;
    }
    
    .canvas-actions {
        display: flex;
        gap: 10px;
    }
    
    .pos-canvas {
        width: 100%;
        height: calc(100% - 80px);
        background: 
            linear-gradient(90deg, #f1f3f4 1px, transparent 1px),
            linear-gradient(180deg, #f1f3f4 1px, transparent 1px);
        background-size: 20px 20px;
        position: relative;
        overflow: auto;
        min-height: 600px;
    }
    
    .canvas-group {
        position: absolute;
        background: linear-gradient(135deg, #667eea, #764ba2);
        color: white;
        border-radius: 12px;
        padding: 15px;
        cursor: move;
        box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
        transition: all 0.3s ease;
        min-width: 120px;
        min-height: 80px;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        text-align: center;
        user-select: none;
        border: 2px solid transparent;
    }
    
    .canvas-group:hover {
        transform: scale(1.05);
        box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
        border-color: rgba(255,255,255,0.5);
    }
    
    .canvas-group.dragging {
        opacity: 0.8;
        transform: rotate(5deg);
        z-index: 1000;
    }
    
    .canvas-group.selected {
        border-color: #ffd700;
        box-shadow: 0 0 0 3px rgba(255, 215, 0, 0.3);
    }
    
    .canvas-group-name {
        font-weight: 700;
        font-size: 14px;
        margin-bottom: 5px;
    }
    
    .canvas-group-products {
        font-size: 11px;
        opacity: 0.9;
        background: rgba(255,255,255,0.2);
        padding: 3px 8px;
        border-radius: 10px;
    }
    
    .drop-zone {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        border: 3px dashed transparent;
        transition: all 0.3s ease;
        pointer-events: none;
    }
    
    .drop-zone.active {
        border-color: #667eea;
        background: rgba(102, 126, 234, 0.1);
        pointer-events: all;
    }
    
    .main-screen-indicator {
        position: absolute;
        top: 20px;
        left: 20px;
        background: linear-gradient(135deg, #48bb78, #38a169);
        color: white;
        padding: 10px 15px;
        border-radius: 8px;
        font-weight: 600;
        font-size: 14px;
        box-shadow: 0 4px 15px rgba(72, 187, 120, 0.3);
    }
    
    .layout-info {
        background: white;
        padding: 20px;
        border-radius: 15px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        margin-bottom: 20px;
    }
    
    .layout-stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 15px;
        margin-top: 15px;
    }
    
    .stat-card {
        text-align: center;
        padding: 15px;
        background: #f8f9fa;
        border-radius: 10px;
        border: 2px solid #e2e8f0;
    }
    
    .stat-number {
        font-size: 24px;
        font-weight: bold;
        color: #2d3748;
        margin-bottom: 5px;
    }
    
    .stat-label {
        font-size: 12px;
        color: #718096;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .save-indicator {
        position: fixed;
        top: 20px;
        right: 20px;
        background: #48bb78;
        color: white;
        padding: 10px 20px;
        border-radius: 8px;
        font-weight: 600;
        opacity: 0;
        transform: translateY(-20px);
        transition: all 0.3s ease;
        z-index: 2000;
    }
    
    .save-indicator.show {
        opacity: 1;
        transform: translateY(0);
    }
    
    .toolbar {
        display: flex;
        gap: 10px;
        align-items: center;
    }
    
    .toolbar button {
        padding: 8px 16px;
        border: none;
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        font-size: 12px;
    }
    
    .btn-clear {
        background: #fed7d7;
        color: #742a2a;
    }
    
    .btn-clear:hover {
        background: #feb2b2;
    }
    
    .btn-save {
        background: #c6f6d5;
        color: #22543d;
    }
    
    .btn-save:hover {
        background: #9ae6b4;
    }
    
    .btn-grid {
        background: #e2e8f0;
        color: #2d3748;
    }
    
    .btn-grid:hover {
        background: #cbd5e0;
    }
    
    .btn-grid.active {
        background: #667eea;
        color: white;
    }
</style>
@endsection

@section('content')
<!-- Layout Info -->
<div class="layout-info">
    <div style="display: flex; justify-content: space-between; align-items: center;">
        <div>
            <h2 style="margin: 0; color: #2d3748;">{{ $posLayout->name }}</h2>
            <p style="margin: 5px 0 0 0; color: #718096;">{{ $posLayout->description }}</p>
            <p style="margin: 5px 0 0 0; color: #718096;"><strong>Brand:</strong> {{ $posLayout->brand->name }}</p>
        </div>
        <div style="text-align: right;">
            <a href="{{ route('admin.pos-layouts.index') }}" class="btn btn-secondary">
                ← Back to Layouts
            </a>
            <a href="{{ route('admin.pos-layouts.edit', $posLayout) }}" class="btn btn-warning">
                ✏️ Edit Layout
            </a>
        </div>
    </div>
    
    <div class="layout-stats">
        <div class="stat-card">
            <div class="stat-number">{{ $groups->count() }}</div>
            <div class="stat-label">Available Groups</div>
        </div>
        <div class="stat-card">
            <div class="stat-number" id="placedGroupsCount">0</div>
            <div class="stat-label">Placed Groups</div>
        </div>
        <div class="stat-card">
            <div class="stat-number">{{ $groups->sum(function($group) { return $group->products->count(); }) }}</div>
            <div class="stat-label">Total Products</div>
        </div>
        <div class="stat-card">
            <div class="stat-number">{{ $posLayout->is_default ? 'Yes' : 'No' }}</div>
            <div class="stat-label">Default Layout</div>
        </div>
    </div>
</div>

<!-- Layout Designer -->
<div class="layout-designer">
    <!-- Groups Panel -->
    <div class="groups-panel">
        <h3>📦 Available Groups</h3>
        <div id="groupsList">
            @foreach($groups as $group)
                <div class="group-item" 
                     data-group-id="{{ $group->id }}"
                     data-group-name="{{ $group->name }}"
                     data-product-count="{{ $group->products->count() }}">
                    <div class="group-name">{{ $group->name }}</div>
                    <div class="group-info">
                        <span>{{ $group->division->category->name }}</span>
                        <span class="product-count">{{ $group->products->count() }} items</span>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
    
    <!-- Canvas -->
    <div class="canvas-container">
        <div class="canvas-header">
            <h3 class="canvas-title">🎨 POS Layout Canvas</h3>
            <div class="toolbar">
                <button class="btn-grid" id="toggleGrid">📐 Grid</button>
                <button class="btn-clear" id="clearCanvas">🗑️ Clear All</button>
                <button class="btn-save" id="saveLayout">💾 Save Layout</button>
            </div>
        </div>
        
        <div class="pos-canvas" id="posCanvas">
            <div class="main-screen-indicator">📱 MAIN SCREEN</div>
            <div class="drop-zone" id="dropZone"></div>
        </div>
    </div>
</div>

<!-- Save Indicator -->
<div class="save-indicator" id="saveIndicator">
    ✅ Layout Saved Successfully!
</div>
@endsection

@section('scripts')
<script>
    let draggedElement = null;
    let placedGroups = [];
    let isDragging = false;
    let offset = { x: 0, y: 0 };
    
    // Initialize drag and drop
    document.addEventListener('DOMContentLoaded', function() {
        initializeDragAndDrop();
        loadExistingLayout();
        updatePlacedGroupsCount();
    });
    
    function initializeDragAndDrop() {
        const groupItems = document.querySelectorAll('.group-item');
        const canvas = document.getElementById('posCanvas');
        const dropZone = document.getElementById('dropZone');
        
        // Make group items draggable
        groupItems.forEach(item => {
            item.addEventListener('dragstart', handleDragStart);
            item.addEventListener('dragend', handleDragEnd);
            item.setAttribute('draggable', 'true');
        });
        
        // Canvas drop events
        canvas.addEventListener('dragover', handleDragOver);
        canvas.addEventListener('drop', handleDrop);
        canvas.addEventListener('dragenter', handleDragEnter);
        canvas.addEventListener('dragleave', handleDragLeave);
        
        // Canvas group events
        canvas.addEventListener('mousedown', handleCanvasMouseDown);
        document.addEventListener('mousemove', handleMouseMove);
        document.addEventListener('mouseup', handleMouseUp);
        
        // Toolbar events
        document.getElementById('toggleGrid').addEventListener('click', toggleGrid);
        document.getElementById('clearCanvas').addEventListener('click', clearCanvas);
        document.getElementById('saveLayout').addEventListener('click', saveLayout);
    }
    
    function handleDragStart(e) {
        draggedElement = this;
        this.style.opacity = '0.5';
        e.dataTransfer.effectAllowed = 'copy';
    }
    
    function handleDragEnd(e) {
        this.style.opacity = '1';
        draggedElement = null;
    }
    
    function handleDragOver(e) {
        e.preventDefault();
        e.dataTransfer.dropEffect = 'copy';
    }
    
    function handleDragEnter(e) {
        e.preventDefault();
        document.getElementById('dropZone').classList.add('active');
    }
    
    function handleDragLeave(e) {
        if (!e.currentTarget.contains(e.relatedTarget)) {
            document.getElementById('dropZone').classList.remove('active');
        }
    }
    
    function handleDrop(e) {
        e.preventDefault();
        document.getElementById('dropZone').classList.remove('active');
        
        if (draggedElement) {
            const rect = e.currentTarget.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;
            
            createCanvasGroup(draggedElement, x, y);
        }
    }
    
    function createCanvasGroup(sourceElement, x, y) {
        const groupId = sourceElement.dataset.groupId;
        const groupName = sourceElement.dataset.groupName;
        const productCount = sourceElement.dataset.productCount;
        
        // Check if group already exists on canvas
        if (document.querySelector(`[data-canvas-group-id="${groupId}"]`)) {
            alert('This group is already placed on the canvas!');
            return;
        }
        
        const canvasGroup = document.createElement('div');
        canvasGroup.className = 'canvas-group';
        canvasGroup.dataset.canvasGroupId = groupId;
        canvasGroup.style.left = Math.max(0, x - 60) + 'px';
        canvasGroup.style.top = Math.max(0, y - 40) + 'px';
        
        canvasGroup.innerHTML = `
            <div class="canvas-group-name">${groupName}</div>
            <div class="canvas-group-products">${productCount} products</div>
        `;
        
        document.getElementById('posCanvas').appendChild(canvasGroup);
        
        // Add to placed groups array
        placedGroups.push({
            id: groupId,
            name: groupName,
            x: parseInt(canvasGroup.style.left),
            y: parseInt(canvasGroup.style.top),
            width: 120,
            height: 80
        });
        
        updatePlacedGroupsCount();
        
        // Add double-click to remove
        canvasGroup.addEventListener('dblclick', function() {
            if (confirm('Remove this group from the canvas?')) {
                removeCanvasGroup(this);
            }
        });
    }
    
    function handleCanvasMouseDown(e) {
        if (e.target.classList.contains('canvas-group')) {
            isDragging = true;
            draggedElement = e.target;
            
            const rect = e.target.getBoundingClientRect();
            const canvasRect = e.currentTarget.getBoundingClientRect();
            
            offset.x = e.clientX - rect.left;
            offset.y = e.clientY - rect.top;
            
            e.target.classList.add('dragging');
            e.preventDefault();
        }
    }
    
    function handleMouseMove(e) {
        if (isDragging && draggedElement) {
            const canvas = document.getElementById('posCanvas');
            const canvasRect = canvas.getBoundingClientRect();
            
            let x = e.clientX - canvasRect.left - offset.x;
            let y = e.clientY - canvasRect.top - offset.y;
            
            // Keep within canvas bounds
            x = Math.max(0, Math.min(x, canvas.clientWidth - 120));
            y = Math.max(0, Math.min(y, canvas.clientHeight - 80));
            
            draggedElement.style.left = x + 'px';
            draggedElement.style.top = y + 'px';
            
            // Update in placed groups array
            const groupId = draggedElement.dataset.canvasGroupId;
            const groupIndex = placedGroups.findIndex(g => g.id === groupId);
            if (groupIndex !== -1) {
                placedGroups[groupIndex].x = x;
                placedGroups[groupIndex].y = y;
            }
        }
    }
    
    function handleMouseUp(e) {
        if (isDragging) {
            isDragging = false;
            if (draggedElement) {
                draggedElement.classList.remove('dragging');
                draggedElement = null;
            }
        }
    }
    
    function removeCanvasGroup(element) {
        const groupId = element.dataset.canvasGroupId;
        element.remove();
        
        // Remove from placed groups array
        placedGroups = placedGroups.filter(g => g.id !== groupId);
        updatePlacedGroupsCount();
    }
    
    function clearCanvas() {
        if (confirm('Are you sure you want to clear all groups from the canvas?')) {
            document.querySelectorAll('.canvas-group').forEach(group => group.remove());
            placedGroups = [];
            updatePlacedGroupsCount();
        }
    }
    
    function toggleGrid() {
        const canvas = document.getElementById('posCanvas');
        const btn = document.getElementById('toggleGrid');
        
        if (canvas.style.backgroundImage) {
            canvas.style.backgroundImage = '';
            btn.classList.remove('active');
        } else {
            canvas.style.backgroundImage = `
                linear-gradient(90deg, #f1f3f4 1px, transparent 1px),
                linear-gradient(180deg, #f1f3f4 1px, transparent 1px)
            `;
            canvas.style.backgroundSize = '20px 20px';
            btn.classList.add('active');
        }
    }
    
    function saveLayout() {
        const layoutData = {
            groups: placedGroups
        };
        
        fetch(`{{ route('admin.pos-layouts.update-positions', $posLayout) }}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify(layoutData)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showSaveIndicator();
            } else {
                alert('Error saving layout. Please try again.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error saving layout. Please try again.');
        });
    }
    
    function showSaveIndicator() {
        const indicator = document.getElementById('saveIndicator');
        indicator.classList.add('show');
        
        setTimeout(() => {
            indicator.classList.remove('show');
        }, 3000);
    }
    
    function loadExistingLayout() {
        // Load existing group positions if any
        @foreach($groups as $group)
            @if($group->pos_x !== null && $group->pos_y !== null)
                const existingGroup = document.querySelector(`[data-group-id="{{ $group->id }}"]`);
                if (existingGroup) {
                    createCanvasGroup(existingGroup, {{ $group->pos_x }}, {{ $group->pos_y }});
                }
            @endif
        @endforeach
    }
    
    function updatePlacedGroupsCount() {
        document.getElementById('placedGroupsCount').textContent = placedGroups.length;
    }
</script>
@endsection
