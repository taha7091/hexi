@extends('layouts.admin')

@section('title', 'Add New Company - ERP System')

@section('page-title', 'Add New Company')

@section('breadcrumb', 'Home > Admin > Companies > Create')

@section('styles')
<style>
    .form-container {
        background: #fff;
        padding: 35px;
        border-radius: 15px;
        box-shadow: 0 4px 25px rgba(0,0,0,0.08);
        margin-bottom: 25px;
    }
    .form-group { margin-bottom: 25px; }
    .form-group label {
        display: block;
        margin-bottom: 8px;
        font-weight: 600;
        color: #2c3e50;
        font-size: 14px;
    }
    .form-group input, .form-group select, .form-group textarea {
        width: 100%;
        padding: 12px 16px;
        border: 2px solid #e9ecef;
        border-radius: 10px;
        font-size: 14px;
        transition: all 0.3s ease;
    }
    .form-group input:focus, .form-group select:focus, .form-group textarea:focus {
        outline: none;
        border-color: #3498db;
        box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
    }
    .form-group small { color: #7f8c8d; font-size: 12px; margin-top: 5px; display: block; }
    .error { color: #e74c3c; font-size: 12px; margin-top: 8px; }
    .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 25px; }
    .license-info {
        background: linear-gradient(135deg, #e8f5e8, #f0f8f0);
        padding: 20px;
        border-radius: 12px;
        margin-top: 15px;
        border-left: 4px solid #27ae60;
    }
    .header-actions {
        display: flex;
        gap: 15px;
        margin-bottom: 25px;
        align-items: center;
    }
</style>
@endsection

@section('content')
<div class="header-actions">
    <a href="{{ route('admin.companies.index') }}" class="btn btn-secondary">
        <i style="margin-right: 8px;">←</i> Back to Companies
    </a>
</div>

        <div class="form-container">
            <form method="POST" action="{{ route('admin.companies.store') }}">
                @csrf
                
                <div class="form-group">
                    <label for="name">Company Name *</label>
                    <input type="text" name="name" id="name" value="{{ old('name') }}" required>
                    <small>Enter the full legal name of the company</small>
                    @error('name')
                        <div class="error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="license_key">License Key *</label>
                        <input type="text" name="license_key" id="license_key" value="{{ old('license_key', 'LIC-' . strtoupper(Str::random(8))) }}" required>
                        <small>Unique license identifier for this company</small>
                        @error('license_key')
                            <div class="error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="pos_limit">POS Device Limit *</label>
                        <input type="number" name="pos_limit" id="pos_limit" value="{{ old('pos_limit', 5) }}" min="1" max="100" required>
                        <small>Maximum number of POS devices allowed</small>
                        @error('pos_limit')
                            <div class="error">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="license_expiry">License Expiry Date *</label>
                        <input type="date" name="license_expiry" id="license_expiry" value="{{ old('license_expiry', now()->addYear()->format('Y-m-d')) }}" required>
                        <small>When the company's license expires</small>
                        @error('license_expiry')
                            <div class="error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="status">Initial Status</label>
                        <select name="status" id="status">
                            <option value="active" {{ old('status', 'active') == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                            <option value="suspended" {{ old('status') == 'suspended' ? 'selected' : '' }}>Suspended</option>
                        </select>
                        <small>Current status of the company license</small>
                    </div>
                </div>

                <div class="form-group">
                    <label for="contact_email">Contact Email</label>
                    <input type="email" name="contact_email" id="contact_email" value="{{ old('contact_email') }}">
                    <small>Primary contact email for this company</small>
                    @error('contact_email')
                        <div class="error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="contact_phone">Contact Phone</label>
                    <input type="tel" name="contact_phone" id="contact_phone" value="{{ old('contact_phone') }}">
                    <small>Primary contact phone number</small>
                    @error('contact_phone')
                        <div class="error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="address">Address</label>
                    <textarea name="address" id="address" rows="3">{{ old('address') }}</textarea>
                    <small>Company's business address</small>
                    @error('address')
                        <div class="error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="notes">Notes</label>
                    <textarea name="notes" id="notes" rows="3">{{ old('notes') }}</textarea>
                    <small>Any additional notes about this company</small>
                    @error('notes')
                        <div class="error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="license-info">
                    <h4 style="margin: 0 0 10px 0;">License Information</h4>
                    <p style="margin: 0; font-size: 14px; color: #666;">
                        This company will be able to create up to <strong id="pos-limit-display">5</strong> POS devices 
                        and the license will be valid until <strong id="expiry-display">{{ now()->addYear()->format('M d, Y') }}</strong>.
                    </p>
                </div>

            <div style="margin-top: 35px; padding-top: 25px; border-top: 2px solid #f1f3f4;">
                <button type="submit" class="btn btn-primary" style="padding: 14px 28px; font-weight: 600;">
                    <i style="margin-right: 8px;">✓</i> Create Company
                </button>
                <a href="{{ route('admin.companies.index') }}" class="btn btn-secondary" style="padding: 14px 28px;">
                    Cancel
                </a>
            </div>
        </form>
    </div>

    <!-- Next Steps Info -->
    <div class="form-container">
        <h3 style="margin: 0 0 20px 0; color: #2c3e50; font-size: 18px;">What's Next?</h3>
        <p style="color: #7f8c8d; margin-bottom: 20px; line-height: 1.6;">
            After creating this company, you'll be able to:
        </p>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
            <div style="background: #f8f9fa; padding: 15px; border-radius: 10px; text-align: center;">
                <div style="font-size: 24px; margin-bottom: 8px;">🏷️</div>
                <div style="font-weight: 600; color: #2c3e50; margin-bottom: 5px;">Create Brands</div>
                <div style="font-size: 12px; color: #7f8c8d;">Set up product brands</div>
            </div>
            <div style="background: #f8f9fa; padding: 15px; border-radius: 10px; text-align: center;">
                <div style="font-size: 24px; margin-bottom: 8px;">🏪</div>
                <div style="font-weight: 600; color: #2c3e50; margin-bottom: 5px;">Add Branches</div>
                <div style="font-size: 12px; color: #7f8c8d;">Set up store locations</div>
            </div>
            <div style="background: #f8f9fa; padding: 15px; border-radius: 10px; text-align: center;">
                <div style="font-size: 24px; margin-bottom: 8px;">👥</div>
                <div style="font-weight: 600; color: #2c3e50; margin-bottom: 5px;">Manage Users</div>
                <div style="font-size: 12px; color: #7f8c8d;">Add company staff</div>
            </div>
            <div style="background: #f8f9fa; padding: 15px; border-radius: 10px; text-align: center;">
                <div style="font-size: 24px; margin-bottom: 8px;">🖥️</div>
                <div style="font-weight: 600; color: #2c3e50; margin-bottom: 5px;">POS Devices</div>
                <div style="font-size: 12px; color: #7f8c8d;">Configure terminals</div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
<script>
    // Update license info display
    document.getElementById('pos_limit').addEventListener('input', function() {
        document.getElementById('pos-limit-display').textContent = this.value;
    });

    document.getElementById('license_expiry').addEventListener('input', function() {
        const date = new Date(this.value);
        document.getElementById('expiry-display').textContent = date.toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric'
        });
    });

    // Generate random license key based on company name
    document.getElementById('name').addEventListener('input', function() {
        if (this.value) {
            const prefix = this.value.substring(0, 3).toUpperCase().replace(/[^A-Z]/g, '') || 'COM';
            const random = Math.random().toString(36).substring(2, 8).toUpperCase();
            document.getElementById('license_key').value = `${prefix}-${random}`;
        }
    });
</script>
@endsection
