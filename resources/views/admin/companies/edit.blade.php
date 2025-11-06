
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Company: {{ $company->name }} - ERP System</title>
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
        .alert-warning { background: #fff3cd; color: #856404; border: 1px solid #ffeaa7; }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 15px; margin-bottom: 20px; }
        .stat-card { background: #f8f9fa; padding: 15px; border-radius: 5px; text-align: center; }
        .stat-number { font-size: 24px; font-weight: bold; color: #007bff; }
        .stat-label { font-size: 12px; color: #666; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Edit Company: {{ $company->name }}</h1>
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

        @if($company->license_expiry < now())
            <div class="alert alert-warning">
                <strong>Warning:</strong> This company's license has expired on {{ $company->license_expiry->format('M d, Y') }}.
            </div>
        @endif

        <!-- Company Statistics -->
        <div class="form-container" style="margin-bottom: 20px;">
            <h3 style="margin: 0 0 15px 0;">Company Statistics</h3>
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-number">{{ $company->brands->count() }}</div>
                    <div class="stat-label">Brands</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number">{{ $company->branches->count() }}</div>
                    <div class="stat-label">Branches</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number">{{ $company->users->count() }}</div>
                    <div class="stat-label">Users</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number">
                        {{ $company->branches->sum(function($branch) { return $branch->posDevices->count(); }) }}
                    </div>
                    <div class="stat-label">POS Devices</div>
                </div>
            </div>
        </div>

        <div class="form-container">
            <form method="POST" action="{{ route('admin.companies.update', $company) }}">
                @csrf
                @method('PUT')
                
                <div class="form-group">
                    <label for="name">Company Name *</label>
                    <input type="text" name="name" id="name" value="{{ old('name', $company->name) }}" required>
                    <small>Enter the full legal name of the company</small>
                    @error('name')
                        <div class="error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="license_key">License Key *</label>
                        <input type="text" name="license_key" id="license_key" value="{{ old('license_key', $company->license_key) }}" required>
                        <small>Unique license identifier for this company</small>
                        @error('license_key')
                            <div class="error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="pos_limit">POS Device Limit *</label>
                        <input type="number" name="pos_limit" id="pos_limit" value="{{ old('pos_limit', $company->pos_limit) }}" min="1" max="100" required>
                        <small>Maximum number of POS devices allowed</small>
                        @error('pos_limit')
                            <div class="error">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="license_expiry">License Expiry Date *</label>
                        <input type="date" name="license_expiry" id="license_expiry" value="{{ old('license_expiry', $company->license_expiry->format('Y-m-d')) }}" required>
                        <small>When the company's license expires</small>
                        @error('license_expiry')
                            <div class="error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="status">Status</label>
                        <select name="status" id="status">
                            <option value="active" {{ old('status', $company->status ?? 'active') == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ old('status', $company->status ?? 'active') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                            <option value="suspended" {{ old('status', $company->status ?? 'active') == 'suspended' ? 'selected' : '' }}>Suspended</option>
                        </select>
                        <small>Current status of the company license</small>
                    </div>
                </div>

                <div class="form-group">
                    <label for="contact_email">Contact Email</label>
                    <input type="email" name="contact_email" id="contact_email" value="{{ old('contact_email', $company->contact_email ?? '') }}">
                    <small>Primary contact email for this company</small>
                    @error('contact_email')
                        <div class="error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="contact_phone">Contact Phone</label>
                    <input type="tel" name="contact_phone" id="contact_phone" value="{{ old('contact_phone', $company->contact_phone ?? '') }}">
                    <small>Primary contact phone number</small>
                    @error('contact_phone')
                        <div class="error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="address">Address</label>
                    <textarea name="address" id="address" rows="3">{{ old('address', $company->address ?? '') }}</textarea>
                    <small>Company's business address</small>
                    @error('address')
                        <div class="error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="notes">Notes</label>
                    <textarea name="notes" id="notes" rows="3">{{ old('notes', $company->notes ?? '') }}</textarea>
                    <small>Any additional notes about this company</small>
                    @error('notes')
                        <div class="error">{{ $message }}</div>
                    @enderror
                </div>

                <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee;">
                    <button type="submit" class="btn btn-primary">Update Company</button>
                    <a href="{{ route('admin.companies.show', $company) }}" class="btn btn-secondary">View Details</a>
                    <a href="{{ route('admin.companies.index') }}" class="btn btn-secondary">Back to List</a>
                </div>
            </form>
        </div>

        <!-- Danger Zone -->
        <div class="form-container" style="margin-top: 20px; border: 2px solid #dc3545;">
            <h3 style="margin: 0 0 15px 0; color: #dc3545;">Danger Zone</h3>
            <p style="color: #666; margin-bottom: 15px;">
                Deleting this company will permanently remove all associated brands, branches, users, and POS devices. This action cannot be undone.
            </p>
            <form method="POST" action="{{ route('admin.companies.destroy', $company) }}" onsubmit="return confirm('Are you sure you want to delete {{ $company->name }}? This will delete ALL related data and cannot be undone. Type the company name to confirm.')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger">Delete Company</button>
            </form>
        </div>
    </div>
</body>
</html>
