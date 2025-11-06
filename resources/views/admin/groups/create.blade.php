@extends('layouts.admin')

@section('title', 'Create Group')
@section('page-title', 'Create Group')

@section('styles')
<style>
    .create-group-container {
        background: var(--bg-primary);
        min-height: 100vh;
        padding: 2rem 0;
    }

    .form-wizard {
        background: var(--card-bg);
        border-radius: 25px;
        box-shadow: 0 25px 80px rgba(0,0,0,0.1);
        overflow: hidden;
        transition: all 0.4s cubic-bezier(0.25, 0.8, 0.25, 1);
        margin: 0 auto;
        max-width: 1400px;
        position: relative;
    }

    .form-wizard::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 6px;
        background: linear-gradient(90deg, #43e97b, #38f9d7, #667eea, #764ba2, #f093fb, #f5576c);
        background-size: 300% 100%;
        animation: gradientShift 8s ease infinite;
    }

    @keyframes gradientShift {
        0%, 100% { background-position: 0% 50%; }
        50% { background-position: 100% 50%; }
    }

    .wizard-header {
        background: linear-gradient(135deg, #43e97b 0%, #38f9d7 50%, #667eea 100%);
        color: white;
        padding: 4rem 3rem;
        text-align: center;
        position: relative;
        overflow: hidden;
    }

    .wizard-header::before {
        content: '';
        position: absolute;
        top: -50%;
        left: -50%;
        width: 200%;
        height: 200%;
        background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
        animation: rotate 20s linear infinite;
    }

    .wizard-header::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        height: 100px;
        background: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 1200 120' preserveAspectRatio='none'%3E%3Cpath d='M985.66,92.83C906.67,72,823.78,31,743.84,14.19c-82.26-17.34-168.06-16.33-250.45.39-57.84,11.73-114,31.07-172,41.86A600.21,600.21,0,0,1,0,27.35V120H1200V95.8C1132.19,118.92,1055.71,111.31,985.66,92.83Z' fill='%23ffffff'%3E%3C/path%3E%3C/svg%3E") no-repeat center bottom;
        background-size: cover;
    }

    @keyframes rotate {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }

    .wizard-title {
        font-size: 3.5rem;
        font-weight: 900;
        margin: 0;
        position: relative;
        z-index: 2;
        text-shadow: 0 4px 8px rgba(0,0,0,0.2);
        background: linear-gradient(45deg, #ffffff, #f0f8ff);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        animation: titleGlow 3s ease-in-out infinite alternate;
    }

    @keyframes titleGlow {
        from { filter: drop-shadow(0 0 10px rgba(255,255,255,0.3)); }
        to { filter: drop-shadow(0 0 20px rgba(255,255,255,0.6)); }
    }

    .wizard-subtitle {
        opacity: 0.95;
        margin-top: 1rem;
        position: relative;
        z-index: 2;
        font-size: 1.3rem;
        font-weight: 300;
        letter-spacing: 1px;
    }

    .wizard-icon {
        position: absolute;
        top: 2rem;
        right: 3rem;
        font-size: 4rem;
        opacity: 0.2;
        z-index: 1;
        animation: float 6s ease-in-out infinite;
    }

    @keyframes float {
        0%, 100% { transform: translateY(0px); }
        50% { transform: translateY(-20px); }
    }

    .form-sections {
        display: grid;
        grid-template-columns: 1fr 1fr;
        min-height: 800px;
        position: relative;
    }

    .form-section {
        padding: 4rem 3rem;
        background: var(--card-bg);
        position: relative;
        transition: all 0.3s ease;
    }

    .form-section:first-child {
        border-right: 2px solid var(--border-color);
        background: linear-gradient(135deg, rgba(67, 233, 123, 0.02), rgba(56, 249, 215, 0.02));
    }

    .form-section:last-child {
        background: linear-gradient(135deg, rgba(102, 126, 234, 0.02), rgba(118, 75, 162, 0.02));
    }

    .form-section::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 3px;
        background: linear-gradient(90deg, transparent, var(--border-color), transparent);
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    .form-section:hover::before {
        opacity: 1;
    }

    .section-title {
        font-size: 1.8rem;
        font-weight: 800;
        color: var(--text-primary);
        margin-bottom: 3rem;
        display: flex;
        align-items: center;
        gap: 1.5rem;
        position: relative;
    }

    .section-title::after {
        content: '';
        flex: 1;
        height: 2px;
        background: linear-gradient(90deg, var(--border-color), transparent);
        margin-left: 1rem;
    }

    .section-icon {
        width: 70px;
        height: 70px;
        background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
        border-radius: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 2rem;
        box-shadow: 0 15px 35px rgba(67, 233, 123, 0.3);
        position: relative;
        overflow: hidden;
        transition: all 0.3s ease;
    }

    .section-icon::before {
        content: '';
        position: absolute;
        top: -50%;
        left: -50%;
        width: 200%;
        height: 200%;
        background: linear-gradient(45deg, transparent, rgba(255,255,255,0.2), transparent);
        transform: rotate(-45deg);
        transition: all 0.3s ease;
    }

    .section-icon:hover {
        transform: scale(1.1) rotate(5deg);
        box-shadow: 0 20px 40px rgba(67, 233, 123, 0.4);
    }

    .section-icon:hover::before {
        animation: shine 0.6s ease;
    }

    @keyframes shine {
        0% { transform: translateX(-100%) translateY(-100%) rotate(-45deg); }
        100% { transform: translateX(100%) translateY(100%) rotate(-45deg); }
    }

    .form-group {
        margin-bottom: 2.5rem;
        position: relative;
        animation: slideInUp 0.6s ease forwards;
        opacity: 0;
        transform: translateY(30px);
    }

    .form-group:nth-child(1) { animation-delay: 0.1s; }
    .form-group:nth-child(2) { animation-delay: 0.2s; }
    .form-group:nth-child(3) { animation-delay: 0.3s; }
    .form-group:nth-child(4) { animation-delay: 0.4s; }
    .form-group:nth-child(5) { animation-delay: 0.5s; }
    .form-group:nth-child(6) { animation-delay: 0.6s; }

    @keyframes slideInUp {
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .form-label {
        display: block;
        margin-bottom: 1rem;
        font-weight: 800;
        color: var(--text-primary);
        font-size: 1.1rem;
        text-transform: uppercase;
        letter-spacing: 2px;
        position: relative;
        padding-left: 1rem;
    }

    .form-label::before {
        content: '';
        position: absolute;
        left: 0;
        top: 50%;
        transform: translateY(-50%);
        width: 4px;
        height: 20px;
        background: linear-gradient(135deg, #43e97b, #38f9d7);
        border-radius: 2px;
    }

    .required-indicator {
        color: #e53e3e;
        margin-left: 8px;
        font-size: 1.2rem;
        animation: pulse 2s infinite;
    }

    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.5; }
    }

    .form-control {
        width: 100%;
        padding: 1.5rem 1.75rem;
        border: 3px solid var(--border-color);
        border-radius: 20px;
        font-size: 1.1rem;
        transition: all 0.4s cubic-bezier(0.25, 0.8, 0.25, 1);
        background: var(--bg-secondary);
        color: var(--text-primary);
        font-weight: 600;
        position: relative;
        box-shadow: 0 5px 15px rgba(0,0,0,0.05);
    }

    .form-control:focus {
        outline: none;
        border-color: #43e97b;
        box-shadow: 0 0 0 6px rgba(67, 233, 123, 0.15), 0 10px 25px rgba(0,0,0,0.1);
        transform: translateY(-3px) scale(1.02);
        background: white;
    }

    .form-control:hover {
        border-color: #a0aec0;
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(0,0,0,0.08);
    }

    .form-control.is-invalid {
        border-color: #e53e3e;
        box-shadow: 0 0 0 6px rgba(229, 62, 62, 0.15);
        animation: shake 0.5s ease;
    }

    @keyframes shake {
        0%, 100% { transform: translateX(0); }
        25% { transform: translateX(-5px); }
        75% { transform: translateX(5px); }
    }

    .invalid-feedback {
        color: #e53e3e;
        font-size: 1rem;
        margin-top: 0.75rem;
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .invalid-feedback::before {
        content: '⚠️';
        font-size: 1.2rem;
    }

    .form-help {
        font-size: 0.95rem;
        color: var(--text-secondary);
        margin-top: 0.75rem;
        font-style: italic;
        padding-left: 1rem;
        border-left: 3px solid var(--border-color);
        background: rgba(67, 233, 123, 0.05);
        padding: 0.5rem 1rem;
        border-radius: 8px;
    }

    .color-input-group {
        display: flex;
        align-items: center;
        gap: 1.5rem;
        padding: 1rem;
        background: var(--bg-secondary);
        border-radius: 15px;
        border: 2px solid var(--border-color);
        transition: all 0.3s ease;
    }

    .color-input-group:hover {
        border-color: #43e97b;
        box-shadow: 0 8px 25px rgba(67, 233, 123, 0.1);
    }

    .color-preview {
        width: 80px;
        height: 60px;
        border-radius: 15px;
        border: 3px solid white;
        box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        transition: all 0.3s ease;
        cursor: pointer;
        position: relative;
        overflow: hidden;
    }

    .color-preview::before {
        content: '';
        position: absolute;
        top: -50%;
        left: -50%;
        width: 200%;
        height: 200%;
        background: linear-gradient(45deg, transparent, rgba(255,255,255,0.3), transparent);
        transform: rotate(-45deg);
        transition: all 0.3s ease;
        opacity: 0;
    }

    .color-preview:hover {
        transform: scale(1.15) rotate(5deg);
        box-shadow: 0 15px 35px rgba(0,0,0,0.25);
    }

    .color-preview:hover::before {
        opacity: 1;
        animation: shine 0.6s ease;
    }

    .form-check {
        display: flex;
        align-items: center;
        gap: 1.5rem;
        padding: 1.5rem;
        background: var(--bg-secondary);
        border-radius: 15px;
        border: 3px solid var(--border-color);
        transition: all 0.3s ease;
        cursor: pointer;
        position: relative;
        overflow: hidden;
    }

    .form-check::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(67, 233, 123, 0.1), transparent);
        transition: left 0.6s ease;
    }

    .form-check:hover {
        border-color: #43e97b;
        background: rgba(67, 233, 123, 0.05);
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(67, 233, 123, 0.15);
    }

    .form-check:hover::before {
        left: 100%;
    }

    .form-check-input {
        width: 25px;
        height: 25px;
        accent-color: #43e97b;
        transform: scale(1.2);
    }

    .form-check-label {
        font-weight: 700;
        color: var(--text-primary);
        cursor: pointer;
        margin: 0;
        font-size: 1.1rem;
    }

    .pos-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1.5rem;
        margin-bottom: 2rem;
    }

    .pos-dimensions {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1.5rem;
        margin-bottom: 2rem;
    }

    .wizard-actions {
        padding: 3rem;
        background: linear-gradient(135deg, var(--bg-primary), var(--bg-secondary));
        border-top: 2px solid var(--border-color);
        display: flex;
        justify-content: space-between;
        align-items: center;
        position: relative;
    }

    .wizard-actions::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 2px;
        background: linear-gradient(90deg, #43e97b, #38f9d7, #667eea);
        background-size: 200% 100%;
        animation: gradientShift 4s ease infinite;
    }

    .btn {
        padding: 1.25rem 3rem;
        border-radius: 20px;
        font-weight: 800;
        text-decoration: none;
        border: none;
        cursor: pointer;
        transition: all 0.4s cubic-bezier(0.25, 0.8, 0.25, 1);
        display: inline-flex;
        align-items: center;
        gap: 1rem;
        font-size: 1.1rem;
        text-transform: uppercase;
        letter-spacing: 2px;
        position: relative;
        overflow: hidden;
    }

    .btn::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
        transition: left 0.6s ease;
    }

    .btn:hover::before {
        left: 100%;
    }

    .btn-primary {
        background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
        color: white;
        box-shadow: 0 15px 35px rgba(67, 233, 123, 0.4);
    }

    .btn-primary:hover {
        transform: translateY(-5px) scale(1.05);
        box-shadow: 0 20px 45px rgba(67, 233, 123, 0.5);
        background: linear-gradient(135deg, #3dd470 0%, #32e6c4 100%);
    }

    .btn-secondary {
        background: var(--card-bg);
        color: var(--text-primary);
        border: 3px solid var(--border-color);
        box-shadow: 0 8px 25px rgba(0,0,0,0.1);
    }

    .btn-secondary:hover {
        background: var(--bg-primary);
        transform: translateY(-3px) scale(1.02);
        box-shadow: 0 12px 30px rgba(0,0,0,0.15);
        border-color: #43e97b;
    }

    .progress-indicator {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: var(--border-color);
        overflow: hidden;
    }

    .progress-bar {
        height: 100%;
        background: linear-gradient(90deg, #43e97b, #38f9d7);
        width: 50%;
        animation: progressGlow 2s ease infinite alternate;
    }

    @keyframes progressGlow {
        from { box-shadow: 0 0 10px rgba(67, 233, 123, 0.5); }
        to { box-shadow: 0 0 20px rgba(67, 233, 123, 0.8); }
    }

    /* Mobile Responsive */
    @media (max-width: 768px) {
        .form-sections {
            grid-template-columns: 1fr;
        }

        .form-section {
            border-right: none;
            border-bottom: 2px solid var(--border-color);
            padding: 3rem 2rem;
        }

        .form-section:last-child {
            border-bottom: none;
        }

        .wizard-actions {
            flex-direction: column;
            gap: 1.5rem;
            padding: 2.5rem 2rem;
        }

        .btn {
            width: 100%;
            justify-content: center;
        }

        .wizard-title {
            font-size: 2.5rem;
        }

        .wizard-header {
            padding: 3rem 2rem;
        }

        .section-title {
            font-size: 1.5rem;
        }

        .section-icon {
            width: 60px;
            height: 60px;
            font-size: 1.5rem;
        }

        .pos-grid,
        .pos-dimensions {
            grid-template-columns: 1fr;
        }
    }

    /* Dark theme adjustments */
    .theme-dark .form-control:focus {
        background: var(--bg-secondary);
    }

    .theme-dark .color-input-group {
        background: var(--bg-secondary);
    }

    .theme-dark .form-check {
        background: var(--bg-secondary);
    }
</style>
@endsection

@section('content')
<div class="create-group-container">
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-12">
                <div class="form-wizard">
                    <!-- Progress Indicator -->
                    <div class="progress-indicator">
                        <div class="progress-bar"></div>
                    </div>

                    <!-- Wizard Header -->
                    <div class="wizard-header">
                        <div class="wizard-icon">📊</div>
                        <h1 class="wizard-title">Create New Group</h1>
                        <p class="wizard-subtitle">Organize products within divisions using smart groups</p>
                    </div>

                    <form action="{{ route('admin.groups.store') }}" method="POST" id="groupForm">
                        @csrf

                        <!-- Form Sections -->
                        <div class="form-sections">
                            <!-- Basic Information Section -->
                            <div class="form-section">
                                <div class="section-title">
                                    <div class="section-icon">
                                        <i class="fas fa-info-circle"></i>
                                    </div>
                                    Essential Details
                                </div>

                                <div class="form-group">
                                    <label class="form-label" for="division_id">
                                        Parent Division <span class="required-indicator">*</span>
                                    </label>
                                    <select name="division_id" id="division_id" class="form-control @error('division_id') is-invalid @enderror" required>
                                        <option value="">📁 Select Parent Division</option>
                                        @foreach($divisions as $division)
                                            <option value="{{ $division->id }}" {{ old('division_id', request('division_id')) == $division->id ? 'selected' : '' }}>
                                                {{ $division->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('division_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-help">💡 Choose the division this group will belong to</div>
                                </div>

                                <div class="form-group">
                                    <label class="form-label" for="name">
                                        Group Name <span class="required-indicator">*</span>
                                    </label>
                                    <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror"
                                           value="{{ old('name') }}" required placeholder="Enter a unique group name...">
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-help">✨ A descriptive and memorable name for this group</div>
                                </div>

                                <div class="form-group">
                                    <label class="form-label" for="description">Optional Description</label>
                                    <textarea name="description" id="description" class="form-control @error('description') is-invalid @enderror"
                                              rows="4" placeholder="Describe what this group contains and its purpose...">{{ old('description') }}</textarea>
                                    @error('description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-help">📝 Help others understand what products belong in this group</div>
                                </div>

                                <div class="form-group">
                                    <div class="form-check">
                                        <input type="checkbox" name="is_active" id="is_active" class="form-check-input"
                                               value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_active">
                                            🟢 Active Group
                                        </label>
                                    </div>
                                    <div class="form-help">⚡ Inactive groups won't be visible in the system</div>
                                    @error('is_active')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Display & POS Settings Section -->
                            <div class="form-section">
                                <div class="section-title">
                                    <div class="section-icon">
                                        <i class="fas fa-palette"></i>
                                    </div>
                                    Visual & POS Settings
                                </div>

                                <div class="form-group">
                                    <label class="form-label" for="color">Brand Color</label>
                                    <div class="color-input-group">
                                        <input type="color" name="color" id="color" class="form-control @error('color') is-invalid @enderror"
                                               value="{{ old('color', '#43e97b') }}" style="width: 100px; height: 60px; border-radius: 15px;">
                                        <div class="color-preview" id="colorPreview" style="background-color: {{ old('color', '#43e97b') }};"></div>
                                        <div>
                                            <strong>Color Preview</strong><br>
                                            <small>Used for visual identification</small>
                                        </div>
                                    </div>
                                    @error('color')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-help">🎨 Choose a color that represents this group</div>
                                </div>

                                <div class="form-group">
                                    <label class="form-label" for="icon">Icon Class</label>
                                    <input type="text" name="icon" id="icon" class="form-control @error('icon') is-invalid @enderror"
                                           value="{{ old('icon', 'fas fa-layer-group') }}" placeholder="fas fa-layer-group">
                                    @error('icon')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-help">🎯 Font Awesome icon class (e.g., fas fa-layer-group, fas fa-cubes)</div>
                                </div>

                                <div class="form-group">
                                    <label class="form-label" for="sort_order">Display Order</label>
                                    <input type="number" name="sort_order" id="sort_order" class="form-control @error('sort_order') is-invalid @enderror"
                                           value="{{ old('sort_order', 0) }}" min="0" placeholder="0">
                                    @error('sort_order')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-help">📊 Lower numbers appear first in lists (0 = first)</div>
                                </div>

                                <div class="form-group">
                                    <label class="form-label">POS Position</label>
                                    <div class="pos-grid">
                                        <div>
                                            <input type="number" name="pos_x" id="pos_x" class="form-control @error('pos_x') is-invalid @enderror"
                                                   value="{{ old('pos_x', 0) }}" min="0" placeholder="X Position">
                                            @error('pos_x')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div>
                                            <input type="number" name="pos_y" id="pos_y" class="form-control @error('pos_y') is-invalid @enderror"
                                                   value="{{ old('pos_y', 0) }}" min="0" placeholder="Y Position">
                                            @error('pos_y')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="form-help">📍 Position coordinates for POS layout (X, Y)</div>
                                </div>

                                <div class="form-group">
                                    <label class="form-label">POS Dimensions</label>
                                    <div class="pos-dimensions">
                                        <div>
                                            <input type="number" name="width" id="width" class="form-control @error('width') is-invalid @enderror"
                                                   value="{{ old('width', 150) }}" min="50" placeholder="Width (px)">
                                            @error('width')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div>
                                            <input type="number" name="height" id="height" class="form-control @error('height') is-invalid @enderror"
                                                   value="{{ old('height', 100) }}" min="50" placeholder="Height (px)">
                                            @error('height')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="form-help">📐 Size dimensions for POS display (Width × Height)</div>
                                </div>
                            </div>
                        </div>

                        <!-- Wizard Actions -->
                        <div class="wizard-actions">
                            <a href="{{ route('admin.groups.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Back to Groups
                            </a>
                            <button type="submit" class="btn btn-primary" id="submitBtn">
                                <i class="fas fa-magic"></i> Create Group
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    // Color preview update with animation
    $('#color').on('change input', function() {
        const color = $(this).val();
        $('#colorPreview').css({
            'background-color': color,
            'transform': 'scale(1.1)',
            'transition': 'all 0.3s ease'
        });
        
        setTimeout(() => {
            $('#colorPreview').css('transform', 'scale(1)');
        }, 300);
    });

    // Form validation with enhanced feedback
    $('.form-control').on('blur', function() {
        if ($(this).val() && $(this).hasClass('is-invalid')) {
            $(this).removeClass('is-invalid');
            $(this).next('.invalid-feedback').fadeOut();
        }
    });

    // Real-time validation
    $('#name').on('input', function() {
        const value = $(this).val();
        if (value.length > 0 && value.length < 3) {
            $(this).addClass('is-invalid');
        } else {
            $(this).removeClass('is-invalid');
        }
    });

    // Enhanced form submission
    $('#groupForm').on('submit', function(e) {
        const submitBtn = $('#submitBtn');
        submitBtn.html('<i class="fas fa-spinner fa-spin"></i> Creating...');
        submitBtn.prop('disabled', true);
        
        // Add loading animation to form
        $('.form-wizard').css({
            'opacity': '0.8',
            'pointer-events': 'none'
        });
    });

    // Smooth scroll to error fields
    if ($('.is-invalid').length > 0) {
        $('html, body').animate({
            scrollTop: $('.is-invalid').first().offset().top - 100
        }, 500);
    }

    // Add floating labels effect
    $('.form-control').on('focus', function() {
        $(this).parent().addClass('focused');
    }).on('blur', function() {
        if (!$(this).val()) {
            $(this).parent().removeClass('focused');
        }
    });

    // POS position validation
    $('#pos_x, #pos_y').on('input', function() {
        const value = parseInt($(this).val());
        if (value < 0) {
            $(this).val(0);
        }
    });

    // POS dimensions validation
    $('#width, #height').on('input', function() {
        const value = parseInt($(this).val());
        if (value < 50) {
            $(this).val(50);
        }
    });
});
</script>
@endsection