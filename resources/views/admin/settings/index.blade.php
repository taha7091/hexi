@extends('layouts.admin')

@section('title', __('app.settings') . ' - ' . (__('app.erp_system') ?: 'ERP System'))

@section('styles')
<style>
    .settings-container {
        max-width: 1200px;
        margin: 0 auto;
    }

    .settings-tabs {
        display: flex;
        background: white;
        border-radius: 12px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        margin-bottom: 30px;
        overflow: hidden;
    }

    .settings-tab {
        flex: 1;
        padding: 15px 20px;
        text-align: center;
        cursor: pointer;
        border: none;
        background: transparent;
        color: #666;
        font-weight: 500;
        transition: all 0.3s ease;
        border-right: 1px solid #f0f0f0;
    }

    .settings-tab:last-child {
        border-right: none;
    }

    .settings-tab.active {
        background: linear-gradient(135deg, #667eea, #764ba2);
        color: white;
    }

    .settings-tab:hover:not(.active) {
        background: #f8f9fa;
        color: #333;
    }

    .settings-content {
        background: white;
        border-radius: 12px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        padding: 30px;
    }

    .settings-section {
        display: none;
    }

    .settings-section.active {
        display: block;
    }

    .setting-group {
        margin-bottom: 30px;
        padding-bottom: 20px;
        border-bottom: 1px solid #f0f0f0;
    }

    .setting-group:last-child {
        border-bottom: none;
        margin-bottom: 0;
    }

    .setting-group h3 {
        font-size: 18px;
        font-weight: 600;
        color: #2c3e50;
        margin-bottom: 15px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .setting-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 15px 0;
        border-bottom: 1px solid #f8f9fa;
    }

    .setting-item:last-child {
        border-bottom: none;
    }

    .setting-info {
        flex: 1;
    }

    .setting-info h4 {
        font-size: 14px;
        font-weight: 600;
        color: #2c3e50;
        margin-bottom: 5px;
    }

    .setting-info p {
        font-size: 12px;
        color: #666;
        margin: 0;
    }

    .setting-control {
        margin-left: 20px;
    }

    .toggle-switch {
        position: relative;
        width: 50px;
        height: 24px;
        background: #ddd;
        border-radius: 12px;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .toggle-switch.active {
        background: linear-gradient(135deg, #667eea, #764ba2);
    }

    .toggle-switch::after {
        content: '';
        position: absolute;
        top: 2px;
        left: 2px;
        width: 20px;
        height: 20px;
        background: white;
        border-radius: 50%;
        transition: all 0.3s ease;
        box-shadow: 0 2px 4px rgba(0,0,0,0.2);
    }

    .toggle-switch.active::after {
        left: 28px;
    }

    .theme-selector {
        display: flex;
        gap: 15px;
    }

    .theme-option {
        width: 80px;
        height: 60px;
        border-radius: 8px;
        cursor: pointer;
        border: 3px solid transparent;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }

    .theme-option.active {
        border-color: #667eea;
        transform: scale(1.05);
    }

    .theme-light {
        background: linear-gradient(135deg, #f8f9fa, #e9ecef);
    }

    .theme-dark {
        background: linear-gradient(135deg, #2c3e50, #34495e);
    }

    .theme-option::after {
        content: attr(data-name);
        position: absolute;
        bottom: 5px;
        left: 50%;
        transform: translateX(-50%);
        font-size: 10px;
        font-weight: 600;
        color: #666;
    }

    .theme-dark::after {
        color: #fff;
    }
</style>
@endsection

@section('content')
<div class="settings-container">
    <!-- Settings Tabs -->
    <div class="settings-tabs">
        <button class="settings-tab active" onclick="showTab('appearance')">
            🎨 {{ __('app.appearance') }}
        </button>
        <button class="settings-tab" onclick="showTab('notifications')">
            🔔 {{ __('app.notifications') }}
        </button>
        <button class="settings-tab" onclick="showTab('display')">
            🖥️ {{ __('app.display') }}
        </button>
        <button class="settings-tab" onclick="showTab('profile')">
            👤 {{ __('app.profile') }}
        </button>
        <button class="settings-tab" onclick="showTab('system')">
            ⚙️ {{ __('app.system') }}
        </button>
    </div>

    <!-- Settings Content -->
    <div class="settings-content">
        <!-- Success/Error Messages -->
        <div id="alert-container"></div>

        <!-- Appearance Settings -->
        <div id="appearance" class="settings-section active">
            <div class="setting-group">
                <h3>🎨 {{ __('app.theme_settings') }}</h3>
                <div class="setting-item">
                    <div class="setting-info">
                        <h4>{{ __('app.color_theme') }}</h4>
                        <p>{{ __('app.choose_theme_desc') }}</p>
                    </div>
                    <div class="setting-control">
                        <div class="theme-selector">
                            <div class="theme-option theme-light active"
                                 data-name="{{ __('app.light_theme') }}" onclick="setTheme('light')"></div>
                            <div class="theme-option theme-dark"
                                 data-name="{{ __('app.dark_theme') }}" onclick="setTheme('dark')"></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="setting-group">
                <h3>📐 Layout Settings</h3>
                <div class="setting-item">
                    <div class="setting-info">
                        <h4>Sidebar Collapsed</h4>
                        <p>Keep sidebar collapsed by default</p>
                    </div>
                    <div class="setting-control">
                        <div class="toggle-switch" onclick="toggleSidebarDefault(this)"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Notification Settings -->
        <div id="notifications" class="settings-section">
            <div class="setting-group">
                <h3>🔔 Notification Preferences</h3>
                <div class="setting-item">
                    <div class="setting-info">
                        <h4>Enable Notifications</h4>
                        <p>Receive system notifications</p>
                    </div>
                    <div class="setting-control">
                        <div class="toggle-switch active" onclick="toggleNotifications(this, 'notifications_enabled')"></div>
                    </div>
                </div>
                <div class="setting-item">
                    <div class="setting-info">
                        <h4>Email Notifications</h4>
                        <p>Receive notifications via email</p>
                    </div>
                    <div class="setting-control">
                        <div class="toggle-switch active" onclick="toggleNotifications(this, 'email_notifications')"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Display Settings -->
        <div id="display" class="settings-section">
            <div class="setting-group">
                <h3>🌍 Language & Region</h3>
                <div class="form-group">
                    <label for="language">{{ __('app.language') }}</label>
                    <select id="language" onchange="updateDisplaySettings()">
                        <option value="en" {{ app()->getLocale() === 'en' ? 'selected' : '' }}>English</option>
                        <option value="es" {{ app()->getLocale() === 'es' ? 'selected' : '' }}>Español</option>
                        <option value="fr" {{ app()->getLocale() === 'fr' ? 'selected' : '' }}>Français</option>
                        <option value="de" {{ app()->getLocale() === 'de' ? 'selected' : '' }}>Deutsch</option>
                        <option value="ar" {{ app()->getLocale() === 'ar' ? 'selected' : '' }}>العربية</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="timezone">{{ __('app.timezone') }}</label>
                    <select id="timezone" onchange="updateDisplaySettings()">
                        <option value="UTC" selected>UTC</option>
                        <option value="America/New_York">Eastern Time</option>
                        <option value="America/Chicago">Central Time</option>
                        <option value="America/Denver">Mountain Time</option>
                        <option value="America/Los_Angeles">Pacific Time</option>
                        <option value="Europe/London">London</option>
                        <option value="Europe/Paris">Paris</option>
                        <option value="Asia/Tokyo">Tokyo</option>
                        <option value="Asia/Dubai">Dubai</option>
                    </select>
                </div>
            </div>

            <div class="setting-group">
                <h3>📅 {{ __('app.date_time_format') }}</h3>
                <div class="form-group">
                    <label for="date_format">{{ __('app.date_format') }}</label>
                    <select id="date_format" onchange="updateDisplaySettings()">
                        <option value="Y-m-d" selected>YYYY-MM-DD</option>
                        <option value="d/m/Y">DD/MM/YYYY</option>
                        <option value="m/d/Y">MM/DD/YYYY</option>
                        <option value="d-m-Y">DD-MM-YYYY</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="time_format">{{ __('app.time_format') }}</label>
                    <select id="time_format" onchange="updateDisplaySettings()">
                        <option value="H:i:s" selected>24 Hour (HH:MM:SS)</option>
                        <option value="h:i:s A">12 Hour (HH:MM:SS AM/PM)</option>
                        <option value="H:i">24 Hour (HH:MM)</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Profile Settings -->
        <div id="profile" class="settings-section">
            <div class="setting-group">
                <h3>👤 {{ __('app.profile_information') }}</h3>
                <div class="form-group">
                    <label for="profile_name">{{ __('app.full_name') }}</label>
                    <input type="text" id="profile_name" value="{{ Auth::user()->name }}" placeholder="Enter your full name">
                </div>
                <div class="form-group">
                    <label for="profile_email">{{ __('app.email_address') }}</label>
                    <input type="email" id="profile_email" value="{{ Auth::user()->email }}" placeholder="Enter your email">
                </div>
            </div>

            <div class="setting-group">
                <h3>🔒 {{ __('app.change_password') }}</h3>
                <div class="form-group">
                    <label for="current_password">{{ __('app.current_password') }}</label>
                    <input type="password" id="current_password" placeholder="Enter current password">
                </div>
                <div class="form-group">
                    <label for="new_password">{{ __('app.new_password') }}</label>
                    <input type="password" id="new_password" placeholder="Enter new password">
                </div>
                <div class="form-group">
                    <label for="new_password_confirmation">{{ __('app.confirm_password') }}</label>
                    <input type="password" id="new_password_confirmation" placeholder="Confirm new password">
                </div>
            </div>

            <div class="btn-group">
                <button class="btn btn-primary" onclick="updateProfile()">{{ __('app.update') }} {{ __('app.profile') }}</button>
                <button class="btn btn-secondary" onclick="clearProfileForm()">{{ __('app.cancel') }}</button>
            </div>
        </div>

        <!-- System Settings -->
        <div id="system" class="settings-section">
            <div class="setting-group">
                <h3>ℹ️ {{ __('app.system_information') }}</h3>
                <div class="system-info" id="system-info">
                    <div class="system-info-grid">
                        <div class="system-info-item">
                            <span class="system-info-label">Loading...</span>
                            <span class="system-info-value">Please wait</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="setting-group">
                <h3>🧹 {{ __('app.maintenance') }}</h3>
                <div class="setting-item">
                    <div class="setting-info">
                        <h4>{{ __('app.clear_cache') }}</h4>
                        <p>{{ __('app.clear_cache_desc') }}</p>
                    </div>
                    <div class="setting-control">
                        <button class="btn btn-warning" onclick="clearCache()">{{ __('app.clear_cache') }}</button>
                    </div>
                </div>
                <div class="setting-item">
                    <div class="setting-info">
                        <h4>Export Settings</h4>
                        <p>Download your settings as a backup</p>
                    </div>
                    <div class="setting-control">
                        <button class="btn btn-info" onclick="exportSettings()">Export</button>
                    </div>
                </div>
                <div class="setting-item">
                    <div class="setting-info">
                        <h4>Reset Settings</h4>
                        <p>Reset all settings to default values</p>
                    </div>
                    <div class="setting-control">
                        <button class="btn btn-danger" onclick="resetSettings()">Reset All</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .form-group {
        margin-bottom: 20px;
    }

    .form-group label {
        display: block;
        font-weight: 600;
        color: #2c3e50;
        margin-bottom: 8px;
        font-size: 14px;
    }

    .form-group select,
    .form-group input {
        width: 100%;
        padding: 10px 12px;
        border: 2px solid #e9ecef;
        border-radius: 8px;
        font-size: 14px;
        transition: all 0.3s ease;
    }

    .form-group select:focus,
    .form-group input:focus {
        outline: none;
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }

    .btn-group {
        display: flex;
        gap: 10px;
        margin-top: 20px;
    }

    .system-info {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 20px;
        margin-bottom: 20px;
    }

    .system-info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 15px;
    }

    .system-info-item {
        display: flex;
        justify-content: space-between;
        padding: 8px 0;
        border-bottom: 1px solid #e9ecef;
    }

    .system-info-item:last-child {
        border-bottom: none;
    }

    .system-info-label {
        font-weight: 600;
        color: #2c3e50;
    }

    .system-info-value {
        color: #666;
        font-family: monospace;
        font-size: 12px;
    }

    .alert {
        padding: 12px 16px;
        border-radius: 8px;
        margin-bottom: 20px;
        border: none;
        font-size: 14px;
    }

    .alert-success {
        background: #d4edda;
        color: #155724;
    }

    .alert-error {
        background: #f8d7da;
        color: #721c24;
    }

    .alert-info {
        background: #cce7ff;
        color: #004085;
    }

    /* Mobile Responsive */
    @media (max-width: 768px) {
        .settings-tabs {
            flex-direction: column;
        }

        .settings-tab {
            border-right: none;
            border-bottom: 1px solid #f0f0f0;
        }

        .settings-tab:last-child {
            border-bottom: none;
        }

        .settings-content {
            padding: 20px 15px;
        }

        .setting-item {
            flex-direction: column;
            align-items: stretch;
            gap: 10px;
        }

        .setting-control {
            margin-left: 0;
            align-self: flex-start;
        }

        .theme-selector {
            justify-content: center;
        }

        .btn-group {
            flex-direction: column;
        }

        .system-info-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 480px) {
        .settings-container {
            margin: 0 10px;
        }

        .settings-content {
            padding: 15px 10px;
        }

        .theme-option {
            width: 60px;
            height: 45px;
        }
    }
</style>

<script>
// Tab switching functionality
function showTab(tabName) {
    // Hide all sections
    document.querySelectorAll('.settings-section').forEach(section => {
        section.classList.remove('active');
    });

    // Remove active class from all tabs
    document.querySelectorAll('.settings-tab').forEach(tab => {
        tab.classList.remove('active');
    });

    // Show selected section
    document.getElementById(tabName).classList.add('active');

    // Add active class to clicked tab
    event.target.classList.add('active');

    // Load system info when system tab is clicked
    if (tabName === 'system') {
        loadSystemInfo();
    }
}

// Theme switching functionality
function setTheme(theme) {
    // Update theme options
    document.querySelectorAll('.theme-option').forEach(option => {
        option.classList.remove('active');
    });

    document.querySelector(`.theme-${theme}`).classList.add('active');

    // Send theme update to server
    fetch('/admin/settings/theme', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            theme: theme,
            persist: true
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('Theme updated successfully!', 'success');
            // Apply theme immediately (you can add theme CSS classes here)
            document.body.className = document.body.className.replace(/theme-\w+/, '') + ` theme-${theme}`;
        } else {
            showAlert('Failed to update theme', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('Error updating theme', 'error');
    });
}

// Toggle switch functionality
function toggleSidebarDefault(element) {
    element.classList.toggle('active');
    const collapsed = element.classList.contains('active');

    fetch('/admin/settings/sidebar', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            collapsed: collapsed
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('Sidebar preference updated!', 'success');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('Error updating sidebar preference', 'error');
    });
}

function toggleNotifications(element, type) {
    element.classList.toggle('active');
    const enabled = element.classList.contains('active');

    const data = {};
    data[type] = enabled;

    fetch('/admin/settings/notifications', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('Notification settings updated!', 'success');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('Error updating notification settings', 'error');
    });
}

// Display settings update
function updateDisplaySettings() {
    const language = document.getElementById('language').value;
    const timezone = document.getElementById('timezone').value;
    const dateFormat = document.getElementById('date_format').value;
    const timeFormat = document.getElementById('time_format').value;

    fetch('/admin/settings/display', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            language: language,
            timezone: timezone,
            date_format: dateFormat,
            time_format: timeFormat
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('Display settings updated!', 'success');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('Error updating display settings', 'error');
    });
}

// Profile update
function updateProfile() {
    const name = document.getElementById('profile_name').value;
    const email = document.getElementById('profile_email').value;
    const currentPassword = document.getElementById('current_password').value;
    const newPassword = document.getElementById('new_password').value;
    const newPasswordConfirmation = document.getElementById('new_password_confirmation').value;

    fetch('/admin/settings/profile', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            name: name,
            email: email,
            current_password: currentPassword,
            new_password: newPassword,
            new_password_confirmation: newPasswordConfirmation
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('Profile updated successfully!', 'success');
            clearProfileForm();
        } else {
            showAlert(data.message || 'Failed to update profile', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('Error updating profile', 'error');
    });
}

function clearProfileForm() {
    document.getElementById('current_password').value = '';
    document.getElementById('new_password').value = '';
    document.getElementById('new_password_confirmation').value = '';
}

// System functions
function loadSystemInfo() {
    fetch('/admin/settings/system-info', {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            displaySystemInfo(data.data);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        document.getElementById('system-info').innerHTML = '<p>Error loading system information</p>';
    });
}

function displaySystemInfo(info) {
    const container = document.getElementById('system-info');
    let html = '<div class="system-info-grid">';

    Object.entries(info).forEach(([key, value]) => {
        const label = key.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
        html += `
            <div class="system-info-item">
                <span class="system-info-label">${label}:</span>
                <span class="system-info-value">${value}</span>
            </div>
        `;
    });

    html += '</div>';
    container.innerHTML = html;
}

function clearCache() {
    if (!confirm('Are you sure you want to clear the application cache?')) {
        return;
    }

    fetch('/admin/settings/clear-cache', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('Cache cleared successfully!', 'success');
        } else {
            showAlert(data.message || 'Failed to clear cache', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('Error clearing cache', 'error');
    });
}

function exportSettings() {
    fetch('/admin/settings/export', {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.blob())
    .then(blob => {
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.style.display = 'none';
        a.href = url;
        a.download = 'settings_export.json';
        document.body.appendChild(a);
        a.click();
        window.URL.revokeObjectURL(url);
        showAlert('Settings exported successfully!', 'success');
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('Error exporting settings', 'error');
    });
}

function resetSettings() {
    if (!confirm('Are you sure you want to reset all settings to default? This cannot be undone.')) {
        return;
    }

    fetch('/admin/settings/reset', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('Settings reset to default!', 'success');
            setTimeout(() => {
                location.reload();
            }, 2000);
        } else {
            showAlert(data.message || 'Failed to reset settings', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('Error resetting settings', 'error');
    });
}

// Alert system
function showAlert(message, type) {
    const container = document.getElementById('alert-container');
    const alert = document.createElement('div');
    alert.className = `alert alert-${type}`;
    alert.textContent = message;

    container.innerHTML = '';
    container.appendChild(alert);

    setTimeout(() => {
        alert.remove();
    }, 5000);
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    // Load system info if system tab is active
    if (document.getElementById('system').classList.contains('active')) {
        loadSystemInfo();
    }
});
</script>
@endsection
