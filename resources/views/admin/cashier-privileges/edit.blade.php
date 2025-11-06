@extends('layouts.admin')

@section('title', 'Configure Cashier Privileges - ' . $cashier->name)

@section('content')
<div class="header-actions">
    <h2>⚙️ Configure Privileges for {{ $cashier->name }}</h2>
    <p>Set what this cashier can and cannot do in the POS system</p>
    <a href="{{ route('admin.cashier-privileges.index') }}" class="btn btn-secondary">
        ← Back to Cashier List
    </a>
</div>

<div class="cashier-info-card">
    <div class="cashier-header">
        <h3>👤 {{ $cashier->name }}</h3>
        <span class="role-badge">Cashier</span>
    </div>
    <div class="cashier-details">
        <div><strong>Email:</strong> {{ $cashier->email }}</div>
        <div><strong>PIN:</strong> <code>{{ $cashier->pin ?? 'Not Set' }}</code></div>
        <div><strong>Company:</strong> {{ $cashier->company->name }}</div>
    </div>
</div>

<form method="POST" action="{{ route('admin.cashier-privileges.update', $cashier) }}">
    @csrf
    @method('PUT')
    
    <div class="privileges-container">
        <!-- Sales Privileges -->
        <div class="privilege-section">
            <h3>💰 Sales Privileges</h3>
            <div class="privilege-grid">
                <div class="privilege-item">
                    <label class="privilege-label">
                        <input type="checkbox" name="can_view_older_sales" value="1" 
                               {{ old('can_view_older_sales', $privileges->can_view_older_sales ?? false) ? 'checked' : '' }}>
                        <span class="checkmark"></span>
                        <div class="privilege-info">
                            <strong>View Older Sales</strong>
                            <small>Can view sales from previous days/sessions</small>
                        </div>
                    </label>
                </div>
                
                <div class="privilege-item">
                    <label class="privilege-label">
                        <input type="checkbox" name="can_process_refunds" value="1" 
                               {{ old('can_process_refunds', $privileges->can_process_refunds ?? false) ? 'checked' : '' }}>
                        <span class="checkmark"></span>
                        <div class="privilege-info">
                            <strong>Process Refunds</strong>
                            <small>Can process customer refunds</small>
                        </div>
                    </label>
                </div>
                
                <div class="privilege-item">
                    <label class="privilege-label">
                        <input type="checkbox" name="can_apply_discounts" value="1" 
                               {{ old('can_apply_discounts', $privileges->can_apply_discounts ?? false) ? 'checked' : '' }}>
                        <span class="checkmark"></span>
                        <div class="privilege-info">
                            <strong>Apply Discounts</strong>
                            <small>Can apply discounts to items/transactions</small>
                        </div>
                    </label>
                </div>
                
                <div class="privilege-item">
                    <label class="privilege-label">
                        <input type="checkbox" name="can_void_transactions" value="1" 
                               {{ old('can_void_transactions', $privileges->can_void_transactions ?? false) ? 'checked' : '' }}>
                        <span class="checkmark"></span>
                        <div class="privilege-info">
                            <strong>Void Transactions</strong>
                            <small>Can cancel/void completed transactions</small>
                        </div>
                    </label>
                </div>
                
                <div class="privilege-item">
                    <label class="privilege-label">
                        <input type="checkbox" name="can_modify_prices" value="1" 
                               {{ old('can_modify_prices', $privileges->can_modify_prices ?? false) ? 'checked' : '' }}>
                        <span class="checkmark"></span>
                        <div class="privilege-info">
                            <strong>Modify Prices</strong>
                            <small>Can change product prices during sale</small>
                        </div>
                    </label>
                </div>
            </div>
        </div>

        <!-- End of Day Privileges -->
        <div class="privilege-section">
            <h3>📊 End of Day Privileges</h3>
            <div class="privilege-grid">
                <div class="privilege-item highlight">
                    <label class="privilege-label">
                        <input type="checkbox" name="can_press_end_of_day" value="1" 
                               {{ old('can_press_end_of_day', $privileges->can_press_end_of_day ?? false) ? 'checked' : '' }}>
                        <span class="checkmark"></span>
                        <div class="privilege-info">
                            <strong>Press End of Day</strong>
                            <small>⚠️ Can close daily operations and generate reports</small>
                        </div>
                    </label>
                </div>
                
                <div class="privilege-item">
                    <label class="privilege-label">
                        <input type="checkbox" name="can_view_daily_reports" value="1" 
                               {{ old('can_view_daily_reports', $privileges->can_view_daily_reports ?? false) ? 'checked' : '' }}>
                        <span class="checkmark"></span>
                        <div class="privilege-info">
                            <strong>View Daily Reports</strong>
                            <small>Can view daily sales and transaction reports</small>
                        </div>
                    </label>
                </div>
                
                <div class="privilege-item">
                    <label class="privilege-label">
                        <input type="checkbox" name="can_view_cash_drawer" value="1" 
                               {{ old('can_view_cash_drawer', $privileges->can_view_cash_drawer ?? true) ? 'checked' : '' }}>
                        <span class="checkmark"></span>
                        <div class="privilege-info">
                            <strong>View Cash Drawer</strong>
                            <small>Can view cash drawer status and amounts</small>
                        </div>
                    </label>
                </div>
            </div>
        </div>

        <!-- Product Privileges -->
        <div class="privilege-section">
            <h3>📦 Product Privileges</h3>
            <div class="privilege-grid">
                <div class="privilege-item">
                    <label class="privilege-label">
                        <input type="checkbox" name="can_add_products" value="1" 
                               {{ old('can_add_products', $privileges->can_add_products ?? false) ? 'checked' : '' }}>
                        <span class="checkmark"></span>
                        <div class="privilege-info">
                            <strong>Add Products</strong>
                            <small>Can add new products to the system</small>
                        </div>
                    </label>
                </div>
                
                <div class="privilege-item">
                    <label class="privilege-label">
                        <input type="checkbox" name="can_edit_products" value="1" 
                               {{ old('can_edit_products', $privileges->can_edit_products ?? false) ? 'checked' : '' }}>
                        <span class="checkmark"></span>
                        <div class="privilege-info">
                            <strong>Edit Products</strong>
                            <small>Can modify existing product information</small>
                        </div>
                    </label>
                </div>
                
                <div class="privilege-item">
                    <label class="privilege-label">
                        <input type="checkbox" name="can_manage_inventory" value="1" 
                               {{ old('can_manage_inventory', $privileges->can_manage_inventory ?? false) ? 'checked' : '' }}>
                        <span class="checkmark"></span>
                        <div class="privilege-info">
                            <strong>Manage Inventory</strong>
                            <small>Can update stock levels and inventory</small>
                        </div>
                    </label>
                </div>
            </div>
        </div>

        <!-- System Privileges -->
        <div class="privilege-section">
            <h3>⚙️ System Privileges</h3>
            <div class="privilege-grid">
                <div class="privilege-item">
                    <label class="privilege-label">
                        <input type="checkbox" name="can_access_settings" value="1" 
                               {{ old('can_access_settings', $privileges->can_access_settings ?? false) ? 'checked' : '' }}>
                        <span class="checkmark"></span>
                        <div class="privilege-info">
                            <strong>Access Settings</strong>
                            <small>Can access system settings and configuration</small>
                        </div>
                    </label>
                </div>
                
                <div class="privilege-item">
                    <label class="privilege-label">
                        <input type="checkbox" name="can_backup_data" value="1" 
                               {{ old('can_backup_data', $privileges->can_backup_data ?? false) ? 'checked' : '' }}>
                        <span class="checkmark"></span>
                        <div class="privilege-info">
                            <strong>Backup Data</strong>
                            <small>Can create data backups</small>
                        </div>
                    </label>
                </div>
            </div>
        </div>
    </div>

    <div class="form-actions">
        <button type="submit" class="btn btn-success">
            💾 Save Privileges
        </button>
        <a href="{{ route('admin.cashier-privileges.index') }}" class="btn btn-secondary">
            Cancel
        </a>
    </div>
</form>

<style>
.cashier-info-card {
    background: white;
    border-radius: 12px;
    padding: 25px;
    margin-bottom: 30px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    border: 1px solid #e9ecef;
}

.cashier-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
}

.cashier-header h3 {
    margin: 0;
    color: #2c3e50;
}

.role-badge {
    background: #17a2b8;
    color: white;
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.cashier-details {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 15px;
    color: #6c757d;
    font-size: 14px;
}

.privileges-container {
    display: grid;
    gap: 30px;
}

.privilege-section {
    background: white;
    border-radius: 12px;
    padding: 25px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    border: 1px solid #e9ecef;
}

.privilege-section h3 {
    margin: 0 0 20px 0;
    color: #2c3e50;
    font-size: 18px;
    border-bottom: 2px solid #f8f9fa;
    padding-bottom: 10px;
}

.privilege-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 15px;
}

.privilege-item {
    border: 2px solid #e9ecef;
    border-radius: 8px;
    padding: 15px;
    transition: all 0.3s ease;
}

.privilege-item:hover {
    border-color: #007bff;
    background: #f8f9ff;
}

.privilege-item.highlight {
    border-color: #ffc107;
    background: #fffbf0;
}

.privilege-label {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    cursor: pointer;
    margin: 0;
}

.privilege-label input[type="checkbox"] {
    width: 18px;
    height: 18px;
    margin: 0;
    cursor: pointer;
}

.privilege-info strong {
    display: block;
    color: #2c3e50;
    margin-bottom: 4px;
    font-size: 14px;
}

.privilege-info small {
    color: #6c757d;
    font-size: 12px;
    line-height: 1.4;
}

.form-actions {
    margin-top: 30px;
    display: flex;
    gap: 15px;
    justify-content: center;
    padding: 25px;
    background: white;
    border-radius: 12px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
}

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

code {
    background: #f8f9fa;
    padding: 2px 6px;
    border-radius: 4px;
    font-family: 'Courier New', monospace;
    font-size: 12px;
    color: #e83e8c;
}

@media (max-width: 768px) {
    .header-actions {
        flex-direction: column;
        gap: 15px;
    }
    
    .privilege-grid {
        grid-template-columns: 1fr;
    }
    
    .cashier-details {
        grid-template-columns: 1fr;
    }
    
    .form-actions {
        flex-direction: column;
    }
}
</style>
@endsection
