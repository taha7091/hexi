@extends('layouts.admin')

@section('title', 'Configure Privileges - ' . $role->name)

@section('content')
<div class="header-actions">
    <div>
        <h2>⚙️ Configure Privileges</h2>
        <p>Set what users with the "{{ $role->name }}" role can do</p>
    </div>
    <a href="{{ route('admin.roles.index') }}" class="btn btn-secondary">
        ← Back to Roles
    </a>
</div>

<div class="role-info-card">
    <div class="role-header" style="background: {{ $role->color }};">
        <div class="role-icon">{{ $role->icon }}</div>
        <div class="role-details">
            <h3>{{ $role->name }}</h3>
            <p>{{ $role->description ?: 'No description provided' }}</p>
            <span class="user-count">{{ $role->users->count() }} users assigned</span>
        </div>
    </div>
</div>

<form method="POST" action="{{ route('admin.roles.update-privileges', $role) }}">
    @csrf
    @method('PUT')

    <div class="privileges-container">
        <!-- POS Access Privileges -->
        <div class="privilege-section">
            <h3>🖥️ POS Access</h3>
            <div class="privilege-grid">
                <div class="privilege-item">
                    <label class="privilege-label">
                        <input type="checkbox" name="can_access_pos" value="1"
                               {{ old('can_access_pos', $privileges->can_access_pos ?? true) ? 'checked' : '' }}>
                        <span class="checkmark"></span>
                        <div class="privilege-info">
                            <strong>Access POS System</strong>
                            <small>Can log into and use the POS terminal</small>
                        </div>
                    </label>
                </div>

                <div class="privilege-item">
                    <label class="privilege-label">
                        <input type="checkbox" name="can_logout_from_pos" value="1"
                               {{ old('can_logout_from_pos', $privileges->can_logout_from_pos ?? true) ? 'checked' : '' }}>
                        <span class="checkmark"></span>
                        <div class="privilege-info">
                            <strong>Logout from POS</strong>
                            <small>Can logout from the POS system</small>
                        </div>
                    </label>
                </div>
            </div>
        </div>

        <!-- Sales Privileges -->
        <div class="privilege-section">
            <h3>💰 Sales Operations</h3>
            <div class="privilege-grid">
                <div class="privilege-item">
                    <label class="privilege-label">
                        <input type="checkbox" name="can_view_sales" value="1"
                               {{ old('can_view_sales', $privileges->can_view_sales ?? true) ? 'checked' : '' }}>
                        <span class="checkmark"></span>
                        <div class="privilege-info">
                            <strong>View Sales</strong>
                            <small>Can view current sales and transactions</small>
                        </div>
                    </label>
                </div>

                <div class="privilege-item highlight">
                    <label class="privilege-label">
                        <input type="checkbox" name="can_view_older_sales" value="1"
                               {{ old('can_view_older_sales', $privileges->can_view_older_sales ?? false) ? 'checked' : '' }}>
                        <span class="checkmark"></span>
                        <div class="privilege-info">
                            <strong>View Older Sales</strong>
                            <small>⚠️ Can view sales from previous days/sessions</small>
                        </div>
                    </label>
                </div>

                <div class="privilege-item">
                    <label class="privilege-label">
                        <input type="checkbox" name="can_create_sales" value="1"
                               {{ old('can_create_sales', $privileges->can_create_sales ?? true) ? 'checked' : '' }}>
                        <span class="checkmark"></span>
                        <div class="privilege-info">
                            <strong>Create Sales</strong>
                            <small>Can process new sales transactions</small>
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
            <h3>📊 End of Day Operations</h3>
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

                <div class="privilege-item">
                    <label class="privilege-label">
                        <input type="checkbox" name="can_open_cash_drawer" value="1"
                               {{ old('can_open_cash_drawer', $privileges->can_open_cash_drawer ?? false) ? 'checked' : '' }}>
                        <span class="checkmark"></span>
                        <div class="privilege-info">
                            <strong>Open Cash Drawer</strong>
                            <small>Can manually open the cash drawer</small>
                        </div>
                    </label>
                </div>
            </div>
        </div>

        <!-- Product Privileges -->
        <div class="privilege-section">
            <h3>📦 Product Management</h3>
            <div class="privilege-grid">
                <div class="privilege-item">
                    <label class="privilege-label">
                        <input type="checkbox" name="can_view_products" value="1"
                               {{ old('can_view_products', $privileges->can_view_products ?? true) ? 'checked' : '' }}>
                        <span class="checkmark"></span>
                        <div class="privilege-info">
                            <strong>View Products</strong>
                            <small>Can view product catalog and information</small>
                        </div>
                    </label>
                </div>

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
                        <input type="checkbox" name="can_delete_products" value="1"
                               {{ old('can_delete_products', $privileges->can_delete_products ?? false) ? 'checked' : '' }}>
                        <span class="checkmark"></span>
                        <div class="privilege-info">
                            <strong>Delete Products</strong>
                            <small>Can remove products from the system</small>
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
        <!-- Setup & UI Management Privileges -->
        <div class="privilege-section">
            <h3>🛠️ Setup & UI Management</h3>
            <div class="privilege-grid">
                <div class="privilege-item">
                    <label class="privilege-label">
                        <input type="checkbox" name="can_manage_pos_layouts" value="1"
                               {{ old('can_manage_pos_layouts', $privileges->can_manage_pos_layouts ?? false) ? 'checked' : '' }}>
                        <span class="checkmark"></span>
                        <div class="privilege-info">
                            <strong>Manage POS Layouts</strong>
                            <small>Can access and manage POS Layouts builder</small>
                        </div>
                    </label>
                </div>
                <div class="privilege-item">
                    <label class="privilege-label">
                        <input type="checkbox" name="can_manage_screen_setup" value="1"
                               {{ old('can_manage_screen_setup', $privileges->can_manage_screen_setup ?? false) ? 'checked' : '' }}>
                        <span class="checkmark"></span>
                        <div class="privilege-info">
                            <strong>Manage Screen Setup</strong>
                            <small>Can access and manage Screen Setup designer</small>
                        </div>
                    </label>
                </div>
            </div>
        </div>

        <!-- Sidebar Sections Visibility -->
        <div class="privilege-section">
            <h3>📋 Sidebar Sections Visibility</h3>
            <div class="privilege-grid">
                <div class="privilege-item">
                    <label class="privilege-label">
                        <input type="checkbox" name="visible_sections[]" value="setup"
                               {{ in_array('setup', $role->visible_sections ?? []) ? 'checked' : '' }}>
                        <span class="checkmark"></span>
                        <div class="privilege-info">
                            <strong>Setup</strong>
                            <small>Categories, Divisions, Groups, Products</small>
                        </div>
                    </label>
                </div>
                <div class="privilege-item">
                    <label class="privilege-label">
                        <input type="checkbox" name="visible_sections[]" value="pos_layouts"
                               {{ in_array('pos_layouts', $role->visible_sections ?? []) ? 'checked' : '' }}>
                        <span class="checkmark"></span>
                        <div class="privilege-info">
                            <strong>POS Layouts</strong>
                            <small>Show POS Layouts in Setup section</small>
                        </div>
                    </label>
                </div>
                <div class="privilege-item">
                    <label class="privilege-label">
                        <input type="checkbox" name="visible_sections[]" value="screen_setup"
                               {{ in_array('screen_setup', $role->visible_sections ?? []) ? 'checked' : '' }}>
                        <span class="checkmark"></span>
                        <div class="privilege-info">
                            <strong>Screen Setup</strong>
                            <small>Show Screen Setup designer in Setup section</small>
                        </div>
                    </label>
                </div>
                <div class="privilege-item">
                    <label class="privilege-label">
                        <input type="checkbox" name="visible_sections[]" value="employees"
                               {{ in_array('employees', $role->visible_sections ?? []) ? 'checked' : '' }}>
                        <span class="checkmark"></span>
                        <div class="privilege-info">
                            <strong>Employees</strong>
                            <small>Role Setup, Users, Cashier Privileges</small>
                        </div>
                    </label>
                </div>
                <div class="privilege-item">
                    <label class="privilege-label">
                        <input type="checkbox" name="visible_sections[]" value="customers"
                               {{ in_array('customers', $role->visible_sections ?? []) ? 'checked' : '' }}>
                        <span class="checkmark"></span>
                        <div class="privilege-info">
                            <strong>Customers</strong>
                            <small>Customer management features</small>
                        </div>
                    </label>
                </div>
                <div class="privilege-item">
                    <label class="privilege-label">
                        <input type="checkbox" name="visible_sections[]" value="calendar"
                               {{ in_array('calendar', $role->visible_sections ?? []) ? 'checked' : '' }}>
                        <span class="checkmark"></span>
                        <div class="privilege-info">
                            <strong>Calendar</strong>
                            <small>Events and schedule management</small>
                        </div>
                    </label>
                </div>
            </div>
            <small style="color:#6c757d;display:block;margin-top:8px;">Tip: If you leave this section empty, the sidebar will show its default sections for this role.</small>
        </div>

    </div>

    <div class="form-actions">
        <button type="submit" class="btn btn-success">
            💾 Save Privileges
        </button>
        <a href="{{ route('admin.roles.index') }}" class="btn btn-secondary">
            Cancel
        </a>
    </div>
</form>

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

.role-info-card {
    background: white;
    border-radius: 12px;
    margin-bottom: 30px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    border: 1px solid #e9ecef;
    overflow: hidden;
}

.role-header {
    padding: 25px;
    color: white;
    display: flex;
    align-items: center;
    gap: 20px;
}

.role-icon {
    font-size: 2.5rem;
    background: rgba(255,255,255,0.2);
    width: 80px;
    height: 80px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.role-details h3 {
    margin: 0 0 8px 0;
    font-size: 24px;
    font-weight: 600;
}

.role-details p {
    margin: 0 0 8px 0;
    opacity: 0.9;
    font-size: 14px;
}

.user-count {
    font-size: 12px;
    opacity: 0.8;
    background: rgba(255,255,255,0.2);
    padding: 4px 8px;
    border-radius: 12px;
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

@media (max-width: 768px) {
    .header-actions {
        flex-direction: column;
        gap: 15px;
    }

    .privilege-grid {
        grid-template-columns: 1fr;
    }

    .role-header {
        flex-direction: column;
        text-align: center;
        gap: 15px;
    }

    .form-actions {
        flex-direction: column;
    }
}
</style>
@endsection
