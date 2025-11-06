@extends('layouts.admin')

@section('title', 'Create New Role')

@section('content')
<div class="header-actions">
    <div>
        <h2>➕ Create New Role</h2>
        <p>Define a new role for your company</p>
    </div>
    <a href="{{ route('admin.roles.index') }}" class="btn btn-secondary">
        ← Back to Roles
    </a>
</div>

<div class="form-container">
    <form method="POST" action="{{ route('admin.roles.store') }}">
        @csrf
        
        <div class="form-section">
            <h3>🎭 Role Information</h3>
            
            <div class="form-group">
                <label for="name">Role Name *</label>
                <input type="text" name="name" id="name" required class="form-control" 
                       value="{{ old('name') }}" placeholder="e.g., Supervisor, Store Manager, Shift Manager">
                @error('name')
                    <div class="error-message">{{ $message }}</div>
                @enderror
                <small class="help-text">Choose a descriptive name for this role</small>
            </div>
            
            <div class="form-group">
                <label for="description">Description</label>
                <textarea name="description" id="description" class="form-control" rows="3" 
                          placeholder="Describe the responsibilities and purpose of this role">{{ old('description') }}</textarea>
                @error('description')
                    <div class="error-message">{{ $message }}</div>
                @enderror
                <small class="help-text">Optional: Provide a brief description of this role</small>
            </div>
        </div>

        <div class="form-section">
            <h3>🎨 Visual Settings</h3>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="icon">Icon</label>
                    <select name="icon" id="icon" class="form-control">
                        <option value="👤" {{ old('icon') == '👤' ? 'selected' : '' }}>👤 Person</option>
                        <option value="👨‍💼" {{ old('icon') == '👨‍💼' ? 'selected' : '' }}>👨‍💼 Manager</option>
                        <option value="👩‍💼" {{ old('icon') == '👩‍💼' ? 'selected' : '' }}>👩‍💼 Manager (Female)</option>
                        <option value="👮" {{ old('icon') == '👮' ? 'selected' : '' }}>👮 Supervisor</option>
                        <option value="🛡️" {{ old('icon') == '🛡️' ? 'selected' : '' }}>🛡️ Security</option>
                        <option value="📊" {{ old('icon') == '📊' ? 'selected' : '' }}>📊 Analyst</option>
                        <option value="💰" {{ old('icon') == '💰' ? 'selected' : '' }}>💰 Cashier</option>
                        <option value="📦" {{ old('icon') == '📦' ? 'selected' : '' }}>📦 Inventory</option>
                        <option value="🔧" {{ old('icon') == '🔧' ? 'selected' : '' }}>🔧 Maintenance</option>
                        <option value="📋" {{ old('icon') == '📋' ? 'selected' : '' }}>📋 Coordinator</option>
                    </select>
                    <small class="help-text">Choose an icon to represent this role</small>
                </div>
                
                <div class="form-group">
                    <label for="color">Color</label>
                    <div class="color-picker-container">
                        <input type="color" name="color" id="color" class="color-picker" 
                               value="{{ old('color', '#007bff') }}">
                        <div class="color-presets">
                            <button type="button" class="color-preset" data-color="#007bff" style="background: #007bff;"></button>
                            <button type="button" class="color-preset" data-color="#28a745" style="background: #28a745;"></button>
                            <button type="button" class="color-preset" data-color="#dc3545" style="background: #dc3545;"></button>
                            <button type="button" class="color-preset" data-color="#ffc107" style="background: #ffc107;"></button>
                            <button type="button" class="color-preset" data-color="#6f42c1" style="background: #6f42c1;"></button>
                            <button type="button" class="color-preset" data-color="#fd7e14" style="background: #fd7e14;"></button>
                        </div>
                    </div>
                    <small class="help-text">Choose a color theme for this role</small>
                </div>
            </div>
        </div>

        <div class="form-section">
            <h3>👁️ Preview</h3>
            <div class="role-preview">
                <div class="preview-card">
                    <div class="preview-header" id="preview-header">
                        <div class="preview-icon" id="preview-icon">👤</div>
                        <div class="preview-info">
                            <h4 id="preview-name">Role Name</h4>
                            <span class="preview-users">0 users</span>
                        </div>
                    </div>
                    <div class="preview-body">
                        <p id="preview-description">Role description will appear here</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-success">
                💾 Create Role
            </button>
            <a href="{{ route('admin.roles.index') }}" class="btn btn-secondary">
                Cancel
            </a>
        </div>
    </form>
</div>

<style>
.header-actions {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 25px;
}

.header-actions h2 {
    margin: 0 0 5px 0;
    color: #2c3e50;
}

.header-actions p {
    color: #6c757d;
    margin: 0;
}

.form-container {
    max-width: 800px;
    margin: 0 auto;
}

.form-section {
    background: white;
    border-radius: 12px;
    padding: 25px;
    margin-bottom: 25px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    border: 1px solid #e9ecef;
}

.form-section h3 {
    margin: 0 0 20px 0;
    color: #2c3e50;
    font-size: 18px;
    border-bottom: 2px solid #f8f9fa;
    padding-bottom: 10px;
}

.form-group {
    margin-bottom: 20px;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
    color: #2c3e50;
}

.form-control {
    width: 100%;
    padding: 12px 16px;
    border: 2px solid #e9ecef;
    border-radius: 8px;
    font-size: 14px;
    transition: border-color 0.3s ease;
}

.form-control:focus {
    outline: none;
    border-color: #007bff;
    box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.1);
}

.help-text {
    color: #6c757d;
    font-size: 12px;
    margin-top: 5px;
    display: block;
}

.error-message {
    color: #dc3545;
    font-size: 12px;
    margin-top: 5px;
}

.color-picker-container {
    display: flex;
    align-items: center;
    gap: 15px;
}

.color-picker {
    width: 50px;
    height: 40px;
    border: 2px solid #e9ecef;
    border-radius: 8px;
    cursor: pointer;
}

.color-presets {
    display: flex;
    gap: 8px;
}

.color-preset {
    width: 30px;
    height: 30px;
    border: 2px solid #e9ecef;
    border-radius: 6px;
    cursor: pointer;
    transition: transform 0.2s ease;
}

.color-preset:hover {
    transform: scale(1.1);
    border-color: #007bff;
}

.role-preview {
    padding: 20px;
    background: #f8f9fa;
    border-radius: 8px;
    border: 2px dashed #dee2e6;
}

.preview-card {
    background: white;
    border-radius: 12px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    border: 1px solid #e9ecef;
    overflow: hidden;
    max-width: 350px;
}

.preview-header {
    padding: 20px;
    color: white;
    display: flex;
    align-items: center;
    gap: 15px;
    background: #007bff;
    transition: background-color 0.3s ease;
}

.preview-icon {
    font-size: 1.5rem;
    background: rgba(255,255,255,0.2);
    width: 50px;
    height: 50px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.preview-info h4 {
    margin: 0 0 5px 0;
    font-size: 16px;
    font-weight: 600;
}

.preview-users {
    font-size: 12px;
    opacity: 0.9;
}

.preview-body {
    padding: 20px;
}

.preview-body p {
    color: #6c757d;
    font-size: 14px;
    margin: 0;
    font-style: italic;
}

.form-actions {
    display: flex;
    gap: 15px;
    justify-content: center;
    padding: 25px;
    background: white;
    border-radius: 12px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
}

@media (max-width: 768px) {
    .header-actions {
        flex-direction: column;
        gap: 15px;
    }
    
    .form-row {
        grid-template-columns: 1fr;
    }
    
    .form-actions {
        flex-direction: column;
    }
    
    .color-picker-container {
        flex-direction: column;
        align-items: flex-start;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const nameInput = document.getElementById('name');
    const descriptionInput = document.getElementById('description');
    const iconSelect = document.getElementById('icon');
    const colorInput = document.getElementById('color');
    
    const previewName = document.getElementById('preview-name');
    const previewDescription = document.getElementById('preview-description');
    const previewIcon = document.getElementById('preview-icon');
    const previewHeader = document.getElementById('preview-header');
    
    // Update preview in real-time
    function updatePreview() {
        previewName.textContent = nameInput.value || 'Role Name';
        previewDescription.textContent = descriptionInput.value || 'Role description will appear here';
        previewIcon.textContent = iconSelect.value;
        previewHeader.style.backgroundColor = colorInput.value;
    }
    
    nameInput.addEventListener('input', updatePreview);
    descriptionInput.addEventListener('input', updatePreview);
    iconSelect.addEventListener('change', updatePreview);
    colorInput.addEventListener('input', updatePreview);
    
    // Color preset buttons
    document.querySelectorAll('.color-preset').forEach(button => {
        button.addEventListener('click', function() {
            const color = this.dataset.color;
            colorInput.value = color;
            updatePreview();
        });
    });
    
    // Initial preview update
    updatePreview();
});
</script>
@endsection
