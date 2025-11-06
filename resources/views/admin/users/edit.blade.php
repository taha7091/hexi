<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User: {{ $user->name }} - ERP System</title>
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
        .btn-danger { background: #dc3545; color: white; }
        .btn:hover { opacity: 0.9; }
        .alert { padding: 15px; border-radius: 5px; margin-bottom: 20px; }
        .alert-danger { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .user-info { background: #e9ecef; padding: 15px; border-radius: 5px; margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Edit User: {{ $user->name }}</h1>
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

        <!-- User Info -->
        <div class="user-info">
            <h4 style="margin: 0 0 10px 0;">Current User Information</h4>
            <p style="margin: 5px 0;"><strong>User ID:</strong> {{ $user->id }}</p>
            <p style="margin: 5px 0;"><strong>Created:</strong> {{ $user->created_at->format('M d, Y') }}</p>
            <p style="margin: 5px 0;"><strong>Last Updated:</strong> {{ $user->updated_at->format('M d, Y') }}</p>
        </div>

        <div class="form-container">
            <form method="POST" action="{{ route('admin.users.update', $user) }}">
                @csrf
                @method('PUT')
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="name">Full Name *</label>
                        <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}" required>
                        <small>Enter the user's full name</small>
                        @error('name')
                            <div class="error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="email">Email Address *</label>
                        <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}" required>
                        <small>This will be used for login</small>
                        @error('email')
                            <div class="error">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="password">New Password</label>
                        <input type="password" name="password" id="password">
                        <small>Leave empty to keep current password</small>
                        @error('password')
                            <div class="error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="password_confirmation">Confirm New Password</label>
                        <input type="password" name="password_confirmation" id="password_confirmation">
                        <small>Re-enter the new password if changing</small>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="pin">PIN Code</label>
                        <input type="text" name="pin" id="pin" value="{{ old('pin', $user->pin) }}" maxlength="10" pattern="[0-9]*">
                        <small>4-6 digit PIN for POS login (numbers only)</small>
                        @error('pin')
                            <div class="error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label>&nbsp;</label>
                        <div style="background: #e9ecef; padding: 15px; border-radius: 5px; margin-top: 5px;">
                            <strong>Current PIN:</strong> {{ $user->pin ? '****' . substr($user->pin, -2) : 'Not set' }}<br>
                            <small style="color: #666;">
                                • PIN allows quick login on POS devices<br>
                                • Must be unique within the company<br>
                                • Leave empty to remove PIN access
                            </small>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="role">User Role *</label>
                    <select name="role" id="role" required>
                        @foreach($allowedRoles as $role)
                            <option value="{{ $role }}" {{ old('role', $user->role) == $role ? 'selected' : '' }}>
                                {{ ucfirst(str_replace('_', ' ', $role)) }}
                            </option>
                        @endforeach
                    </select>
                    <small>Select the appropriate role for this user</small>
                    @error('role')
                        <div class="error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="company_role_id">Custom Company Role (Optional)</label>
                    <select name="company_role_id" id="company_role_id">
                        <option value="">No Custom Role (Use Default Permissions)</option>
                        @if(isset($companyRoles))
                            @foreach($companyRoles as $companyRole)
                                <option value="{{ $companyRole->id }}"
                                        {{ old('company_role_id', $user->company_role_id) == $companyRole->id ? 'selected' : '' }}>
                                    {{ $companyRole->icon }} {{ $companyRole->name }}
                                </option>
                            @endforeach
                        @endif
                    </select>
                    <small>Assign a custom role with specific privileges you've configured</small>
                    @error('company_role_id')
                        <div class="error">{{ $message }}</div>
                    @enderror

                    @if($user->companyRole)
                        <div style="background: #e9ecef; padding: 15px; border-radius: 5px; margin-top: 10px;">
                            <h4 style="margin: 0 0 10px 0;">Current Custom Role</h4>
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <span style="font-size: 20px;">{{ $user->companyRole->icon }}</span>
                                <strong style="color: {{ $user->companyRole->color }};">{{ $user->companyRole->name }}</strong>
                            </div>
                            <p style="margin: 5px 0 0 0; font-size: 14px;">{{ $user->companyRole->description ?: 'No description provided' }}</p>
                        </div>
                    @endif
                </div>

                <div class="form-group">
                    <label for="company_id">Company *</label>
                    <select name="company_id" id="company_id" required onchange="updateBranches()">
                        @foreach($companies as $company)
                            <option value="{{ $company->id }}" 
                                    data-branches="{{ json_encode($company->branches->pluck('name', 'id')) }}"
                                    {{ old('company_id', $user->company_id) == $company->id ? 'selected' : '' }}>
                                {{ $company->name }}
                                @if($company->license_expiry < now())
                                    (License Expired)
                                @endif
                            </option>
                        @endforeach
                    </select>
                    <small>Select the company this user belongs to</small>
                    @error('company_id')
                        <div class="error">{{ $message }}</div>
                    @enderror
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
                    <button type="submit" class="btn btn-primary">Update User</button>
                    <a href="{{ route('admin.users.show', $user) }}" class="btn btn-secondary">View Details</a>
                    <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">Back to List</a>
                </div>
            </form>
        </div>

        <!-- Danger Zone -->
        @if($user->id !== 1)
        <div class="form-container" style="margin-top: 20px; border: 2px solid #dc3545;">
            <h3 style="margin: 0 0 15px 0; color: #dc3545;">Danger Zone</h3>
            <p style="color: #666; margin-bottom: 15px;">
                Deleting this user will permanently remove their account and all associated data. This action cannot be undone.
            </p>
            <form method="POST" action="{{ route('admin.users.destroy', $user) }}" onsubmit="return confirm('Are you sure you want to delete {{ $user->name }}? This action cannot be undone.')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger">Delete User</button>
            </form>
        </div>
        @endif
    </div>

    <script>
        function updateBranches() {
            const companySelect = document.getElementById('company_id');
            const branchSelect = document.getElementById('branch_id');
            const currentBranchId = '{{ old("branch_id", $user->branch_id) }}';
            
            // Clear existing options
            branchSelect.innerHTML = '<option value="">No specific branch</option>';
            
            if (companySelect.value) {
                const option = companySelect.options[companySelect.selectedIndex];
                const branches = JSON.parse(option.getAttribute('data-branches') || '{}');
                
                for (const [id, name] of Object.entries(branches)) {
                    const branchOption = document.createElement('option');
                    branchOption.value = id;
                    branchOption.textContent = name;
                    if (id == currentBranchId) {
                        branchOption.selected = true;
                    }
                    branchSelect.appendChild(branchOption);
                }
            }
        }

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            updateBranches();
        });
    </script>
</body>
</html>
