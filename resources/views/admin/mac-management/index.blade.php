@extends('layouts.admin')

@section('title', 'MAC Address Management')

@section('content')
<div class="page-header">
    <h1>MAC Address Management</h1>
    <p>Manage device MAC addresses for all companies</p>
</div>

<div class="content-card">
    <div class="card-header">
        <h2>Companies with Activated Devices</h2>
        <button onclick="refreshData()" class="btn btn-primary">
            <i>🔄</i> Refresh
        </button>
    </div>

    <div id="loading" class="loading-container" style="display: none;">
        <div class="loading-spinner"></div>
        <p>Loading MAC address data...</p>
    </div>

    <div id="companies-container">
        <!-- Companies will be loaded here via JavaScript -->
    </div>
</div>

<!-- Reset MAC Address Modal -->
<div id="resetMacModal" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Reset MAC Address</h3>
            <span class="close" onclick="closeResetModal()">&times;</span>
        </div>
        <div class="modal-body">
            <p>Are you sure you want to reset this MAC address?</p>
            <div class="mac-info">
                <strong>Company:</strong> <span id="reset-company-name"></span><br>
                <strong>MAC Address:</strong> <span id="reset-mac-address"></span><br>
                <strong>Activated:</strong> <span id="reset-activated-date"></span>
            </div>
            <p class="warning">⚠️ This will allow the device to be reactivated on any company.</p>
        </div>
        <div class="modal-footer">
            <button onclick="closeResetModal()" class="btn btn-secondary">Cancel</button>
            <button onclick="confirmResetMac()" class="btn btn-danger">Reset MAC Address</button>
        </div>
    </div>
</div>

<style>
.page-header {
    margin-bottom: 30px;
    padding-bottom: 20px;
    border-bottom: 2px solid rgba(255, 255, 255, 0.2);
}

.page-header h1 {
    font-size: 32px;
    font-weight: 700;
    color: #2d3748;
    margin: 0 0 8px 0;
}

.page-header p {
    color: #718096;
    margin: 0;
}

.content-card {
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(20px);
    border-radius: 20px;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
    border: 1px solid rgba(255, 255, 255, 0.2);
    overflow: hidden;
}

.card-header {
    padding: 25px 30px;
    border-bottom: 1px solid rgba(0, 0, 0, 0.1);
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: linear-gradient(135deg, rgba(255, 255, 255, 0.8), rgba(255, 255, 255, 0.6));
}

.company-section {
    border-bottom: 1px solid #e2e8f0;
    padding: 20px 30px;
}

.company-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
}

.company-name {
    font-size: 18px;
    font-weight: 600;
    color: #2d3748;
}

.company-status {
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
}

.status-active {
    background: #c6f6d5;
    color: #22543d;
}

.status-inactive {
    background: #fed7d7;
    color: #742a2a;
}

.mac-devices {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 15px;
}

.mac-device {
    background: #f7fafc;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 15px;
    position: relative;
}

.mac-address {
    font-family: monospace;
    font-weight: 600;
    color: #2d3748;
    font-size: 14px;
}

.device-info {
    margin-top: 8px;
    font-size: 12px;
    color: #718096;
}

.reset-btn {
    position: absolute;
    top: 10px;
    right: 10px;
    background: #e53e3e;
    color: white;
    border: none;
    padding: 6px 12px;
    border-radius: 6px;
    font-size: 11px;
    cursor: pointer;
    transition: all 0.3s ease;
}

.reset-btn:hover {
    background: #c53030;
    transform: translateY(-1px);
}

.loading-container {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 60px 20px;
    text-align: center;
}

.loading-spinner {
    width: 40px;
    height: 40px;
    border: 4px solid #e2e8f0;
    border-top: 4px solid #667eea;
    border-radius: 50%;
    animation: spin 1s linear infinite;
    margin-bottom: 20px;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.modal {
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    backdrop-filter: blur(5px);
}

.modal-content {
    background: white;
    margin: 10% auto;
    padding: 0;
    border-radius: 12px;
    width: 90%;
    max-width: 500px;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
}

.modal-header {
    padding: 20px 25px;
    border-bottom: 1px solid #e2e8f0;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.modal-body {
    padding: 25px;
}

.modal-footer {
    padding: 20px 25px;
    border-top: 1px solid #e2e8f0;
    display: flex;
    justify-content: flex-end;
    gap: 10px;
}

.mac-info {
    background: #f7fafc;
    padding: 15px;
    border-radius: 8px;
    margin: 15px 0;
    border-left: 4px solid #667eea;
}

.warning {
    color: #e53e3e;
    font-weight: 500;
    margin-top: 15px;
}

.close {
    font-size: 24px;
    font-weight: bold;
    cursor: pointer;
    color: #718096;
}

.close:hover {
    color: #2d3748;
}

.btn {
    padding: 10px 16px;
    border-radius: 8px;
    font-weight: 500;
    cursor: pointer;
    border: none;
    transition: all 0.3s ease;
}

.btn-primary {
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
}

.btn-primary:hover {
    background: linear-gradient(135deg, #5a67d8, #6b46c1);
    transform: translateY(-1px);
}

.btn-secondary {
    background: #e2e8f0;
    color: #4a5568;
}

.btn-secondary:hover {
    background: #cbd5e0;
}

.btn-danger {
    background: #e53e3e;
    color: white;
}

.btn-danger:hover {
    background: #c53030;
}

.empty-state {
    text-align: center;
    padding: 60px 20px;
    color: #718096;
    font-style: italic;
}

/* Mobile Responsive Styles */
@media (max-width: 768px) {
    .content-card {
        margin: 10px;
        border-radius: 15px;
    }

    .card-header {
        padding: 20px 15px;
        flex-direction: column;
        align-items: stretch;
        gap: 15px;
    }

    .card-header h2 {
        font-size: 20px;
        text-align: center;
    }

    .card-header .btn {
        width: 100%;
    }

    .company-section {
        padding: 15px 20px;
    }

    .company-header {
        flex-direction: column;
        align-items: stretch;
        gap: 10px;
    }

    .company-name {
        font-size: 16px;
        text-align: center;
    }

    .company-status {
        align-self: center;
    }

    .mac-devices {
        grid-template-columns: 1fr;
        gap: 10px;
    }

    .mac-device {
        padding: 12px;
    }

    .reset-btn {
        position: static;
        width: 100%;
        margin-top: 10px;
        padding: 8px 12px;
        font-size: 12px;
    }

    .mac-address {
        font-size: 12px;
        word-break: break-all;
    }

    .device-info {
        font-size: 11px;
    }
}

@media (max-width: 480px) {
    .page-header h1 {
        font-size: 24px;
    }

    .page-header p {
        font-size: 14px;
    }

    .content-card {
        margin: 5px;
        border-radius: 12px;
    }

    .card-header {
        padding: 15px 10px;
    }

    .company-section {
        padding: 10px 15px;
    }

    .mac-device {
        padding: 10px;
    }

    .reset-btn {
        padding: 6px 10px;
        font-size: 11px;
    }

    .modal-content {
        width: 95% !important;
        margin: 5% auto !important;
    }

    .modal-header,
    .modal-body,
    .modal-footer {
        padding: 15px;
    }

    .modal-footer {
        flex-direction: column;
        gap: 10px;
    }

    .modal-footer .btn {
        width: 100%;
    }
}
</style>

<script>
let currentResetData = null;

async function refreshData() {
    const loading = document.getElementById('loading');
    const container = document.getElementById('companies-container');
    
    loading.style.display = 'block';
    container.innerHTML = '';

    try {
        const response = await fetch('/api/admin/mac-management/all-companies-macs', {
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'same-origin'
        });

        const data = await response.json();

        if (data.success) {
            displayCompanies(data.data);
        } else {
            container.innerHTML = '<div class="empty-state">Failed to load MAC address data</div>';
        }
    } catch (error) {
        console.error('Error loading MAC data:', error);
        container.innerHTML = '<div class="empty-state">Error loading MAC address data</div>';
    } finally {
        loading.style.display = 'none';
    }
}

function displayCompanies(companies) {
    const container = document.getElementById('companies-container');
    
    if (companies.length === 0) {
        container.innerHTML = '<div class="empty-state">No companies with activated devices found</div>';
        return;
    }

    let html = '';
    companies.forEach(company => {
        const statusClass = company.status === 'active' ? 'status-active' : 'status-inactive';
        
        html += `
            <div class="company-section">
                <div class="company-header">
                    <div>
                        <div class="company-name">${company.name}</div>
                        <div style="font-size: 12px; color: #718096; margin-top: 4px;">
                            ${company.active_device_count}/${company.device_limit} devices
                            ${company.can_activate_more ? '✅' : '❌'}
                            (${company.remaining_slots} slots remaining)
                        </div>
                    </div>
                    <span class="company-status ${statusClass}">${company.status}</span>
                </div>
        `;

        if (company.activated_mac_addresses.length > 0) {
            html += '<div class="mac-devices">';
            company.activated_mac_addresses.forEach(mac => {
                const activatedDate = new Date(mac.activated_at).toLocaleDateString();
                const lastSeen = mac.last_seen ? new Date(mac.last_seen).toLocaleDateString() : 'Never';
                
                html += `
                    <div class="mac-device">
                        <button class="reset-btn" onclick="openResetModal('${company.id}', '${company.name}', '${mac.formatted_mac}', '${mac.activated_at}')">
                            Reset
                        </button>
                        <div class="mac-address">${mac.formatted_mac}</div>
                        <div class="device-info">
                            <div>Activated: ${activatedDate}</div>
                            <div>Last Seen: ${lastSeen}</div>
                            <div>App Version: ${mac.app_version || 'Unknown'}</div>
                        </div>
                    </div>
                `;
            });
            html += '</div>';
        }

        html += '</div>';
    });

    container.innerHTML = html;
}

function openResetModal(companyId, companyName, macAddress, activatedAt) {
    currentResetData = {
        companyId: companyId,
        macAddress: macAddress
    };

    document.getElementById('reset-company-name').textContent = companyName;
    document.getElementById('reset-mac-address').textContent = macAddress;
    document.getElementById('reset-activated-date').textContent = new Date(activatedAt).toLocaleString();
    document.getElementById('resetMacModal').style.display = 'block';
}

function closeResetModal() {
    document.getElementById('resetMacModal').style.display = 'none';
    currentResetData = null;
}

async function confirmResetMac() {
    if (!currentResetData) return;

    try {
        const response = await fetch('/api/admin/mac-management/reset-mac', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'same-origin',
            body: JSON.stringify({
                company_id: currentResetData.companyId,
                mac_address: currentResetData.macAddress
            })
        });

        const data = await response.json();

        if (data.success) {
            alert('MAC address reset successfully!');
            closeResetModal();
            refreshData();
        } else {
            alert('Failed to reset MAC address: ' + data.message);
        }
    } catch (error) {
        console.error('Error resetting MAC:', error);
        alert('Error resetting MAC address');
    }
}

// Load data when page loads
document.addEventListener('DOMContentLoaded', function() {
    refreshData();
});

// Close modal when clicking outside
window.onclick = function(event) {
    const modal = document.getElementById('resetMacModal');
    if (event.target === modal) {
        closeResetModal();
    }
}
</script>
@endsection
