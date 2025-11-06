@extends('layouts.admin')

@section('title', 'Create Category')
@section('page-title', 'Create Category')

@section('styles')
<style>
/* ====== Simplified — Full stylesheet matching your original selectors ====== */
/* Variables expected: --bg-primary, --card-bg, --border-color, --bg-secondary, --text-primary, --text-secondary */

/* Container */
.create-category-container {
    background: var(--bg-primary);
    min-height: 100vh;
    padding: 2rem 0;
    display: flex;
    justify-content: center;
    align-items: flex-start;
}

/* Form shell */
.form-wizard {
    background: var(--card-bg);
    border-radius: 14px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.06);
    overflow: hidden;
    transition: all 0.25s ease;
    margin: 1rem;
    width: 100%;
    max-width: 1100px;
    position: relative;
}

/* Decorative top bar (kept but subtle) */
.form-wizard::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 6px;
    background: linear-gradient(90deg, rgba(79,172,254,0.95), rgba(0,242,254,0.9));
    background-size: 200% 100%;
    opacity: 0.95;
    /* no continuous heavy animation */
}

/* Header area — simplified */
.wizard-header {
    background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
    color: white;
    padding: 1.25rem 1.5rem;
    text-align: center;
    position: relative;
    overflow: hidden;
}

/* Subtle decorative overlay instead of heavy radial animation */
.wizard-header::before {
    content: '';
    position: absolute;
    inset: -30% -30% auto -30%;
    width: 160%;
    height: 160%;
    background: radial-gradient(circle, rgba(255,255,255,0.06) 0%, transparent 60%);
    pointer-events: none;
    opacity: 0.85;
}

/* Subtle bottom SVG wave kept but small */
.wizard-header::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    height: 60px;
    background: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 1200 120' preserveAspectRatio='none'%3E%3Cpath d='M985.66,92.83C906.67,72,823.78,31,743.84,14.19c-82.26-17.34-168.06-16.33-250.45.39-57.84,11.73-114,31.07-172,41.86A600.21,600.21,0,0,1,0,27.35V120H1200V95.8C1132.19,118.92,1055.71,111.31,985.66,92.83Z' fill='%23ffffff'%3E%3C/path%3E%3C/svg%3E") no-repeat center bottom;
    background-size: cover;
    opacity: 0.85;
}

/* Title and subtitle */
.wizard-title {
    font-size: 1.9rem;
    font-weight: 800;
    margin: 0;
    position: relative;
    z-index: 2;
    color: white;
    -webkit-background-clip: text;
    -webkit-text-fill-color: initial;
    text-shadow: none;
}

/* Removed pulsing glow; keep light subtle effect via opacity changes */
.wizard-subtitle {
    opacity: 0.95;
    margin-top: 0.35rem;
    position: relative;
    z-index: 2;
    font-size: 1rem;
    font-weight: 400;
    letter-spacing: 0.4px;
}

/* Icon position preserved but subtle and static */
.wizard-icon {
    position: absolute;
    top: 1rem;
    right: 1.25rem;
    font-size: 2.2rem;
    opacity: 0.08;
    z-index: 1;
}

/* Sections split: two-column layout on wide screens, stacked on small */
.form-sections {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1.25rem;
    min-height: 520px;
    position: relative;
    padding: 1.25rem;
}

/* Each section styling simplified */
.form-section {
    padding: 1.25rem;
    background: var(--card-bg);
    position: relative;
    transition: all 0.18s ease;
    border-radius: 8px;
}

/* First / last gradients reduced to very subtle tint */
.form-section:first-child {
    border-right: 1px solid var(--border-color);
    background: linear-gradient(180deg, rgba(79,172,254,0.02), rgba(0,242,254,0.01));
}
.form-section:last-child {
    background: linear-gradient(180deg, rgba(67,233,123,0.02), rgba(56,249,215,0.01));
}

/* Remove hover::before thick highlight; keep subtle top line for focus */
.form-section::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 3px;
    background: linear-gradient(90deg, transparent, var(--border-color), transparent);
    opacity: 0.25;
    pointer-events: none;
}

/* Section title area */
.section-title {
    font-size: 1.05rem;
    font-weight: 700;
    color: var(--text-primary);
    margin-bottom: 1rem;
    display: flex;
    align-items: center;
    gap: 0.75rem;
    position: relative;
}

/* simple underline instead of long flex rule */


/* Form groups with simple entrance style (one-time) */
.form-group {
    margin-bottom: 1.25rem;
    position: relative;
    opacity: 1;
    transform: none;
    transition: none;
}

/* Labels moderate size */
.form-label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 700;
    color: var(--text-primary);
    font-size: 0.95rem;
    text-transform: none;
    letter-spacing: 0.4px;
    padding-left: 0.6rem;
}

/* slim colored indicator */
.form-label::before {
    content: '';
    position: absolute;
    left: 0;
    margin-left: -0.6rem;
    top: 0.8rem;
    width: 4px;
    height: 14px;
    background: linear-gradient(135deg, #4facfe, #00f2fe);
    border-radius: 2px;
}

/* required indicator small */
.required-indicator {
    color: #ab1717ff;
    margin-left: 6px;
    font-size: 0.98rem;
}

/* Inputs — smaller padding, simpler border */
.form-control {
    width: 100%;
    padding: 0.9rem 1rem;
    border: 1.6px solid var(--border-color);
    border-radius: 10px;
    font-size: 0.98rem;
    background: var(--bg-secondary);
    color: var(--text-primary);
    font-weight: 600;
    box-shadow: none;
    transition: border-color 0.18s ease, background 0.18s ease;
}

/* Focus subtle */
.form-control:focus {
    outline: none;
    border-color: #4facfe;
    box-shadow: 0 0 0 4px rgba(79,172,254,0.08);
    background: #fff;
}

/* Remove hover transform */
.form-control:hover { border-color: #cfd8e3; }

/* Invalid */
.form-control.is-invalid {
    border-color: #e53e3e;
    box-shadow: 0 0 0 6px rgba(229,62,62,0.08);
    animation: none;
}

/* Invalid feedback simplified */
.invalid-feedback {
    color: #e53e3e;
    font-size: 0.95rem;
    margin-top: 0.5rem;
    font-weight: 700;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}
.invalid-feedback::before { content: '⚠️'; font-size: 1rem; }

/* Help text */
.form-help {
    font-size: 0.9rem;
    color: var(--text-secondary);
    margin-top: 0.6rem;
    font-style: italic;
    padding-left: 0.8rem;
    border-left: 3px solid var(--border-color);
    background: rgba(79,172,254,0.03);
    padding: 0.5rem 0.8rem;
    border-radius: 6px;
}

/* Color input grouping — simplified */
.color-input-group {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 0.6rem;
    background: var(--bg-secondary);
    border-radius: 10px;
    border: 1.6px solid var(--border-color);
}

/* subtle hover */
.color-input-group:hover { border-color: #4facfe; }

/* Color preview modest size */
.color-preview {
    width: 62px;
    height: 44px;
    border-radius: 10px;
    border: 2px solid white;
    box-shadow: 0 6px 18px rgba(0,0,0,0.08);
    cursor: pointer;
}

/* Keep simple shine disabled */
.color-preview::before { display: none; }

/* Check / toggle group simplified */
.form-check {
    display: flex;
    align-items: center;
    gap: 0.8rem;
    padding: 0.8rem;
    background: var(--bg-secondary);
    border-radius: 10px;
    border: 1.6px solid var(--border-color);
    cursor: pointer;
}
.form-check:hover { border-color: #4facfe; background: rgba(79,172,254,0.03); }

/* Checkbox size */
.form-check-input {
    width: 18px;
    height: 18px;
    accent-color: #4facfe;
    transform: none;
}

/* Labels */
.form-check-label {
    font-weight: 600;
    color: var(--text-primary);
    margin: 0;
    font-size: 0.95rem;
}

/* Actions footer */
.wizard-actions {
    padding: 1rem 1.25rem;
    background: var(--bg-primary);
    border-top: 1px solid var(--border-color);
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 0.75rem;
    flex-wrap: wrap;
}

/* Buttons simplified & consistent */
.btn {
    padding: 0.75rem 1.25rem;
    border-radius: 8px;
    font-weight: 700;
    border: none;
    cursor: pointer;
    transition: background 0.14s ease, transform 0.12s ease;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.95rem;
    text-transform: none;
}

/* Subtle hover lift only on buttons */
.btn:hover { transform: translateY(-2px); }

/* Primary / secondary simplified */
.btn-primary {
    background: linear-gradient(90deg,#4facfe,#00f2fe);
    color: #fff;
    box-shadow: 0 8px 20px rgba(79,172,254,0.12);
}
.btn-secondary {
    background: transparent;
    color: var(--text-primary);
    border: 1.6px solid var(--border-color);
}

/* Keep the small decorative progress indicator, simpler */
.progress-indicator {
    position: absolute;
    top: 6px;
    left: 0;
    right: 0;
    height: 4px;
    background: transparent;
    pointer-events: none;
}
.progress-bar {
    height: 100%;
    background: linear-gradient(90deg,#4facfe,#00f2fe);
    width: 40%; /* example width - controlled by JS */
}

/* Subtle progress glow removed heavy keyframes */

/* Small-screen (mobile/tablet) responsive rules */
@media (max-width: 960px) {
    .form-sections { grid-template-columns: 1fr; min-height: auto; }
    .form-section { border-right: none; }
    .form-wizard { max-width: 820px; }
    .wizard-header { padding: 1rem 1rem; }
    .wizard-title { font-size: 1.5rem; }
    .wizard-subtitle { font-size: 0.95rem; }
    .section-icon { width: 48px; height: 48px; font-size: 1.05rem; }
    .section-title { font-size: 1rem; }
}

/* Very small phone */
@media (max-width: 480px) {
    .form-wizard { margin: 0.5rem; max-width: calc(100% - 1rem); border-radius: 10px; }
    .wizard-header { padding: 0.85rem 0.9rem; }
    .wizard-title { font-size: 1.25rem; }
    .form-sections { padding: 0.85rem; gap: 0.85rem; }
    .form-group { margin-bottom: 0.9rem; }
    .form-label { font-size: 0.9rem; padding-left: 0.4rem; }
    .form-control { font-size: 0.95rem; padding: 0.7rem 0.8rem; border-radius: 8px; }
    .wizard-actions { padding: 0.85rem; justify-content: center; }
    .btn { width: 100%; justify-content: center; padding: 0.85rem; border-radius: 8px; }
}

/* Dark theme small adjustments (keeps original toggles workable) */
.theme-dark .form-control:focus { background: var(--bg-secondary); }
.theme-dark .color-input-group { background: var(--bg-secondary); }
.theme-dark .form-check { background: var(--bg-secondary); }

/* End of stylesheet */
</style>

@endsection

@section('content')
<div class="create-category-container">
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
                        <div class="wizard-icon">🏷️</div>
                        <h1 class="wizard-title">Create New Category</h1>
                        <p class="wizard-subtitle">Build your product catalog with organized categories</p>
                    </div>

                    <form action="{{ route('admin.categories.store') }}" method="POST" id="categoryForm">
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
                                    <label class="form-label" for="brand_id">
                                        Parent Brand <span class="required-indicator">*</span>
                                    </label>
                                    <select name="brand_id" id="brand_id" class="form-control @error('brand_id') is-invalid @enderror" required>
                                        <option value="">🏢 Select Parent Brand</option>
                                        @foreach($brands as $brand)
                                            <option value="{{ $brand->id }}" {{ old('brand_id') == $brand->id ? 'selected' : '' }}>
                                                {{ $brand->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('brand_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-help">💡 Choose the brand this category will belong to</div>
                                </div>

                                <div class="form-group">
                                    <label class="form-label" for="name">
                                        Category Name <span class="required-indicator">*</span>
                                    </label>
                                    <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror"
                                           value="{{ old('name') }}" required placeholder="Enter a unique category name...">
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-help">✨ A descriptive and memorable name for this category</div>
                                </div>

                                <div class="form-group">
                                    <label class="form-label" for="description">Optional Description</label>
                                    <textarea name="description" id="description" class="form-control @error('description') is-invalid @enderror"
                                              rows="4" placeholder="Describe what this category contains and its purpose...">{{ old('description') }}</textarea>
                                    @error('description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-help">📝 Help others understand what products belong in this category</div>
                                </div>
                            </div>

                            <!-- Display & Settings Section -->
                            <div class="form-section">
                                <div class="section-title">
                                    <div class="section-icon">
                                        <i class="fas fa-palette"></i>
                                    </div>
                                    Visual & Settings
                                </div>

                                <div class="form-group">
                                    <label class="form-label" for="color">Brand Color</label>
                                    <div class="color-input-group">
                                        <input type="color" name="color" id="color" class="form-control @error('color') is-invalid @enderror"
                                               value="{{ old('color', '#4facfe') }}" style="width: 100px; height: 60px; border-radius: 15px;">
                                        <div class="color-preview" id="colorPreview" style="background-color: {{ old('color', '#4facfe') }};"></div>
                                        <div>
                                            <strong>Color Preview</strong><br>
                                            <small>Used for visual identification</small>
                                        </div>
                                    </div>
                                    @error('color')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-help">🎨 Choose a color that represents this category</div>
                                </div>

                                <div class="form-group">
                                    <label class="form-label" for="icon">Icon Class</label>
                                    <input type="text" name="icon" id="icon" class="form-control @error('icon') is-invalid @enderror"
                                           value="{{ old('icon', 'fas fa-tag') }}" placeholder="fas fa-tag">
                                    @error('icon')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-help">🎯 Font Awesome icon class (e.g., fas fa-tag, fas fa-layer-group)</div>
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
                                    <div class="form-check">
                                        <input type="checkbox" name="is_active" id="is_active" class="form-check-input"
                                               value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_active">
                                            🟢 Active Category
                                        </label>
                                    </div>
                                    <div class="form-help">⚡ Inactive categories won't be visible in the system</div>
                                    @error('is_active')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Wizard Actions -->
                        <div class="wizard-actions">
                            <a href="{{ route('admin.categories.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Back to Categories
                            </a>
                            <button type="submit" class="btn btn-primary" id="submitBtn">
                                <i class="fas fa-magic"></i> Create Category
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
    $('#categoryForm').on('submit', function(e) {
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
});
</script>
@endsection