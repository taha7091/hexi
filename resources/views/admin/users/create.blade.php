<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New User - ERP System</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f9f9f9; padding: 20px; margin: 0; }
        .container { max-width: 800px; margin: 0 auto; }
        .header { background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin-bottom: 20px; }
        .header h1 { margin: 0; color: #333; }
        .form-container { background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; color: #333; }
        .form-group input, .form-group select, .form-group textarea { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; font-size: 14px; }
        .form-group input:focus, .form-group select:focus, .form-group textarea:focus { outline: none; border-color: #007bff; }
        .form-group small { color: #666; font-size: 12px; }
        .error { color: #dc3545; font-size: 12px; margin-top: 5px; }
        .btn { padding: 12px 24px; border: none; border-radius: 5px; text-decoration: none; display: inline-block; cursor: pointer; font-size: 14px; margin-right: 10px; }
        .btn-primary { background: #007bff; color: white; }
        .btn-secondary { background: #6c757d; color: white; }
        .btn:hover { opacity: 0.9; }
        .alert { padding: 15px; border-radius: 5px; margin-bottom: 20px; }
        .alert-danger { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .role-info { background: #e9ecef; padding: 15px; border-radius: 5px; margin-top: 10px; }
        .company-info { background: #e9ecef; padding: 15px; border-radius: 5px; margin-top: 10px; display: none; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Add New User</h1>
        </div>

        @if($errors->any())
            <div class="alert alert-danger">
                <strong>Please fix the following errors:</strong>
                <ul style="margin: 10px 0 0 20px;">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="form-container">
            <form method="POST" action="{{ route('admin.users.store') }}">
                @csrf
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="name">Full Name *</label>
                        <input type="text" name="name" id="name" value="{{ old('name') }}" required>
                        <small>Enter the user's full name</small>
                        @error('name')
                            <div class="error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="email">Email Address *</label>
                        <input type="email" name="email" id="email" value="{{ old('email') }}" required>
                        <small>This will be used for login</small>
                        @error('email')
                            <div class="error">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="password">Password *</label>
                        <input type="password" name="password" id="password" required>
                        <small>Minimum 6 characters</small>
                        @error('password')
                            <div class="error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="password_confirmation">Confirm Password *</label>
                        <input type="password" name="password_confirmation" id="password_confirmation" required>
                        <small>Re-enter the password</small>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="pin">PIN Code (Optional)</label>
                        <input type="text" name="pin" id="pin" value="{{ old('pin') }}" maxlength="10" pattern="[0-9]*">
                        <small>4-6 digit PIN for POS login (numbers only)</small>
                        @error('pin')
                            <div class="error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label>&nbsp;</label>
                        <div style="background: #e9ecef; padding: 15px; border-radius: 5px; margin-top: 5px;">
                            <strong>PIN Usage:</strong><br>
                            <small style="color: #666;">
                                • PIN allows quick login on POS devices<br>
                                • Leave empty if user won't use POS<br>
                                • Must be unique within the company
                            </small>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="role">User Role *</label>
                    <select name="role" id="role" required onchange="showRoleInfo()">
                        <option value="">Select Role</option>
                        @foreach($allowedRoles as $role)
                            <option value="{{ $role }}" {{ old('role') == $role ? 'selected' : '' }}>
                                {{ ucfirst(str_replace('_', ' ', $role)) }}
                            </option>
                        @endforeach
                    </select>
                    <small>Select the appropriate role for this user</small>
                    @error('role')
                        <div class="error">{{ $message }}</div>
                    @enderror

                    <div id="roleInfo" class="role-info" style="display: none;">
                        <h4 style="margin: 0 0 10px 0;">Role Permissions</h4>
                        <div id="roleDescription"></div>
                    </div>
                </div>

                <div class="form-group" id="companyRoleGroup" style="display: none;">
                    <label for="company_role_id">Custom Company Role (Optional)</label>
                    <select name="company_role_id" id="company_role_id" onchange="showCompanyRoleInfo()">
                        <option value="">No Custom Role (Use Default Permissions)</option>
                        @if(isset($companyRoles))
                            @foreach($companyRoles as $companyRole)
                                <option value="{{ $companyRole->id }}" {{ old('company_role_id') == $companyRole->id ? 'selected' : '' }}
                                        data-description="{{ $companyRole->description }}"
                                        data-color="{{ $companyRole->color }}"
                                        data-icon="{{ $companyRole->icon }}">
                                    {{ $companyRole->icon }} {{ $companyRole->name }}
                                </option>
                            @endforeach
                        @endif
                    </select>
                    <small>Assign a custom role with specific privileges you've configured</small>
                    @error('company_role_id')
                        <div class="error">{{ $message }}</div>
                    @enderror

                    <div id="companyRoleInfo" class="role-info" style="display: none;">
                        <h4 style="margin: 0 0 10px 0;">Custom Role Details</h4>
                        <div id="companyRoleDescription"></div>
                        <p style="margin: 10px 0 0 0; font-size: 12px; color: #666;">
                            <strong>Note:</strong> Custom role privileges will override default role permissions.
                        </p>
                    </div>
                </div>

                <div class="form-group">
                    <label for="company_id">Company *</label>
                    <select name="company_id" id="company_id" required onchange="updateBranches(); showCompanyInfo();">
                        <option value="">Select Company</option>
                        @foreach($companies as $company)
                            <option value="{{ $company->id }}" 
                                    data-license="{{ $company->license_expiry > now() ? 'Active' : 'Expired' }}"
                                    data-expiry="{{ $company->license_expiry->format('M d, Y') }}"
                                    data-branches="{{ json_encode($company->branches->pluck('name', 'id')) }}"
                                    {{ old('company_id', $selectedCompany) == $company->id ? 'selected' : '' }}>
                                {{ $company->name }}
                            </option>
                        @endforeach
                    </select>
                    <small>Select the company this user belongs to</small>
                    @error('company_id')
                        <div class="error">{{ $message }}</div>
                    @enderror
                    
                    <div id="companyInfo" class="company-info">
                        <h4 style="margin: 0 0 10px 0;">Company Information</h4>
                        <p style="margin: 5px 0;"><strong>License Status:</strong> <span id="licenseStatus"></span></p>
                        <p style="margin: 5px 0;"><strong>License Expiry:</strong> <span id="licenseExpiry"></span></p>
                    </div>
                </div>

                <div class="form-group">
                    <label for="branch_id">Branch (Optional)</label>
                    <select name="branch_id" id="branch_id">
                        <option value="">No specific branch</option>
                    </select>
                    <small>Assign user to a specific branch (optional)</small>
                    @error('branch_id')
                        <div class="error">{{ $message }}</div>
                    @enderror
                </div>

                <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee;">
                    <button type="submit" class="btn btn-primary">Create User</button>
                    <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>

        <!-- User Access Info -->
        <div class="form-container" style="margin-top: 20px;">
            <h3 style="margin: 0 0 15px 0;">User Access Information</h3>
            <p style="color: #666; margin-bottom: 15px;">
                After creating this user, they will be able to:
            </p>
            <ul style="color: #666; margin-left: 20px;">
                <li>Login with their email and password</li>
                <li>Access features based on their assigned role</li>
                <li>Manage data within their company scope</li>
                <li>Use POS devices if assigned to a branch (for POS users)</li>
            </ul>
        </div>
    </div>

    <script>
        const roleDescriptions = {
            'admin': 'Full access to company data, can manage brands, branches, users, and POS devices.',
            'manager': 'Can manage products, sales, and view reports within assigned brands.',
            'user': 'Basic access to view data and perform limited operations.',
            'pos_user': 'Access to POS system for sales transactions and basic inventory.'
        };

        function showRoleInfo() {
            const select = document.getElementById('role');
            const info = document.getElementById('roleInfo');
            const description = document.getElementById('roleDescription');
            const companyRoleGroup = document.getElementById('companyRoleGroup');

            if (select.value && roleDescriptions[select.value]) {
                description.innerHTML = '<p style="margin: 0; font-size: 14px;">' + roleDescriptions[select.value] + '</p>';
                info.style.display = 'block';

                // Show company role selection for non-admin roles
                if (select.value !== 'admin' && select.value !== 'master_admin') {
                    companyRoleGroup.style.display = 'block';
                } else {
                    companyRoleGroup.style.display = 'none';
                }
            } else {
                info.style.display = 'none';
                companyRoleGroup.style.display = 'none';
            }
        }

        function showCompanyRoleInfo() {
            const select = document.getElementById('company_role_id');
            const info = document.getElementById('companyRoleInfo');
            const description = document.getElementById('companyRoleDescription');

            if (select.value) {
                const option = select.options[select.selectedIndex];
                const roleDescription = option.dataset.description || 'No description provided';
                const roleIcon = option.dataset.icon || '👤';
                const roleColor = option.dataset.color || '#007bff';

                description.innerHTML = `
                    <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 10px;">
                        <span style="font-size: 20px;">${roleIcon}</span>
                        <strong style="color: ${roleColor};">${option.text}</strong>
                    </div>
                    <p style="margin: 0; font-size: 14px;">${roleDescription}</p>
                `;
                info.style.display = 'block';
            } else {
                info.style.display = 'none';
            }
        }

        function showCompanyInfo() {
            const select = document.getElementById('company_id');
            const info = document.getElementById('companyInfo');
            
            if (select.value) {
                const option = select.options[select.selectedIndex];
                const licenseStatus = option.getAttribute('data-license');
                const expiry = option.getAttribute('data-expiry');
                
                document.getElementById('licenseStatus').textContent = licenseStatus;
                document.getElementById('licenseStatus').style.color = licenseStatus === 'Active' ? '#28a745' : '#dc3545';
                document.getElementById('licenseExpiry').textContent = expiry;
                
                info.style.display = 'block';

                if (licenseStatus === 'Expired') {
                    info.style.borderLeft = '4px solid #dc3545';
                    info.style.background = '#f8d7da';
                } else {
                    info.style.borderLeft = '4px solid #28a745';
                    info.style.background = '#d4edda';
                }

                // Update company roles when company changes
                updateCompanyRoles();
            } else {
                info.style.display = 'none';
            }
        }

        function updateCompanyRoles() {
            const companySelect = document.getElementById('company_id');
            const roleSelect = document.getElementById('company_role_id');

            if (!companySelect.value) {
                roleSelect.innerHTML = '<option value="">No Custom Role (Use Default Permissions)</option>';
                return;
            }

            // Show loading
            roleSelect.innerHTML = '<option value="">Loading roles...</option>';

            // Fetch company roles
            fetch(`/admin/company-roles/${companySelect.value}`)
                .then(response => response.json())
                .then(roles => {
                    roleSelect.innerHTML = '<option value="">No Custom Role (Use Default Permissions)</option>';

                    roles.forEach(role => {
                        const option = document.createElement('option');
                        option.value = role.id;
                        option.textContent = `${role.icon} ${role.name}`;
                        option.dataset.description = role.description || '';
                        option.dataset.color = role.color;
                        option.dataset.icon = role.icon;
                        roleSelect.appendChild(option);
                    });
                })
                .catch(error => {
                    console.error('Error loading company roles:', error);
                    roleSelect.innerHTML = '<option value="">Error loading roles</option>';
                });
        }

        function updateBranches() {
            const companySelect = document.getElementById('company_id');
            const branchSelect = document.getElementById('branch_id');
            
            // Clear existing options
            branchSelect.innerHTML = '<option value="">No specific branch</option>';
            
            if (companySelect.value) {
                const option = companySelect.options[companySelect.selectedIndex];
                const branches = JSON.parse(option.getAttribute('data-branches') || '{}');
                
                for (const [id, name] of Object.entries(branches)) {
                    const branchOption = document.createElement('option');
                    branchOption.value = id;
                    branchOption.textContent = name;
                    branchSelect.appendChild(branchOption);
                }
            }
        }

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            showRoleInfo();
            showCompanyInfo();
            showCompanyRoleInfo();
            updateBranches();
            updateCompanyRoles();
        });
    </script>
</body>
</html>
