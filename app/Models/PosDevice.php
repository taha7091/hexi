<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PosDevice extends Model
{
    use HasFactory;

    protected $fillable = [
        'branch_id',
        'device_name',
        'device_serial',
        'device_model',
        'device_info',
        'status',
        'activation_key',
        'activated_at',
        'device_fingerprint',
        'mac_address',
        'ip_address',
        'hardware_info',
        'is_activated',
        'last_seen',
        'app_version',
        'mac_address_reset_at',
        'mac_address_reset_by'
    ];

    protected $casts = [
        'device_info' => 'array',
        'hardware_info' => 'array',
        'activated_at' => 'datetime',
        'last_seen' => 'datetime',
        'is_activated' => 'boolean',
        'mac_address_reset_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();
        
        // Generate activation key when creating new device
        static::creating(function ($device) {
            if (empty($device->activation_key)) {
                $device->activation_key = static::generateActivationKey();
            }
        });
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    public function syncLogs()
    {
        return $this->hasMany(SyncLog::class);
    }

    /**
     * Check if device is active
     */
    public function isActive()
    {
        return $this->status === 'active' && $this->is_activated;
    }

    /**
     * Check if device is activated
     */
    public function isActivated()
    {
        return $this->is_activated && !empty($this->activated_at);
    }

    /**
     * Get device's company through branch
     */
    public function getCompanyAttribute()
    {
        return $this->branch->company ?? null;
    }

    /**
     * Generate unique activation key
     */
    public static function generateActivationKey()
    {
        do {
            $key = strtoupper(Str::random(4) . '-' . Str::random(4) . '-' . Str::random(4) . '-' . Str::random(4));
        } while (static::where('activation_key', $key)->exists());
        
        return $key;
    }

    /**
     * Activate device with fingerprint - IMPROVED VERSION
     */
    public function activate($deviceFingerprint, $hardwareInfo = [], $ipAddress = null, $appVersion = null)
    {
        // Validate input
        if (empty($deviceFingerprint)) {
            throw new \Exception('Device fingerprint cannot be empty');
        }

        if (strlen($deviceFingerprint) < 10) {
            throw new \Exception('Device fingerprint must be at least 10 characters long');
        }

        // Check if this specific device is already activated
        if ($this->is_activated) {
            throw new \Exception('This POS device registration is already activated');
        }

        // Get company and validate
        $company = $this->company;
        if (!$company) {
            throw new \Exception('Device must be assigned to a branch with a company');
        }

        // Check company license validity
        if (!$company->hasValidLicense()) {
            throw new \Exception('Company license is invalid or expired');
        }

        // Use database transaction to prevent race conditions
        return DB::transaction(function () use ($deviceFingerprint, $hardwareInfo, $ipAddress, $appVersion, $company) {
            
            // Lock and check for existing device with same fingerprint
            $existingDevice = static::where('device_fingerprint', $deviceFingerprint)
                                   ->where('is_activated', true)
                                   ->lockForUpdate()
                                   ->first();
            
            if ($existingDevice) {
                if ($existingDevice->id === $this->id) {
                    throw new \Exception('This device is already activated');
                } else {
                    throw new \Exception(
                        'This physical device is already activated on another POS device. ' .
                        'Device: "' . $existingDevice->device_name . '" ' .
                        'Company: "' . ($existingDevice->company->name ?? 'Unknown') . '". ' .
                        'Each physical device can only be activated once.'
                    );
                }
            }

            // Check company device limit
            $activeDevicesCount = static::byCompany($company->id)
                                       ->where('is_activated', true)
                                       ->lockForUpdate()
                                       ->count();
            
            if ($activeDevicesCount >= $company->max_devices) {
                throw new \Exception(
                    "Company '{$company->name}' has reached maximum device limit of {$company->max_devices} devices. " .
                    "Currently active: {$activeDevicesCount} devices."
                );
            }

            // Activate the device
            $this->update([
                'is_activated' => true,
                'activated_at' => now(),
                'device_fingerprint' => $deviceFingerprint,
                'hardware_info' => array_merge($hardwareInfo, [
                    'activation_timestamp' => now()->toISOString(),
                    'activation_ip' => $ipAddress,
                    'initial_app_version' => $appVersion
                ]),
                'ip_address' => $ipAddress,
                'app_version' => $appVersion,
                'last_seen' => now(),
                'status' => 'active'
            ]);

            // Update company's active device count
            $company->increment('active_devices');

            // Log the activation
            \Log::info('POS Device activated successfully', [
                'device_id' => $this->id,
                'device_name' => $this->device_name,
                'company_id' => $company->id,
                'company_name' => $company->name,
                'device_fingerprint' => substr($deviceFingerprint, 0, 10) . '...',
                'ip_address' => $ipAddress
            ]);

            return true;
        });
    }

    /**
     * Deactivate device
     */
    public function deactivate($reason = null)
    {
        if (!$this->is_activated) {
            throw new \Exception('Device is not activated');
        }

        $company = $this->company;
        
        return DB::transaction(function () use ($company, $reason) {
            $this->update([
                'is_activated' => false,
                'status' => 'inactive',
                'device_fingerprint' => null,
                'hardware_info' => array_merge($this->hardware_info ?? [], [
                    'deactivated_at' => now()->toISOString(),
                    'deactivation_reason' => $reason,
                    'was_fingerprint' => $this->device_fingerprint
                ])
            ]);

            // Update company's active device count
            if ($company && $company->active_devices > 0) {
                $company->decrement('active_devices');
            }

            // Log the deactivation
            \Log::info('POS Device deactivated', [
                'device_id' => $this->id,
                'device_name' => $this->device_name,
                'company_name' => $company->name ?? 'Unknown',
                'reason' => $reason
            ]);

            return true;
        });
    }

    /**
     * Update device heartbeat
     */
    public function updateHeartbeat($appVersion = null, $additionalInfo = [])
    {
        if (!$this->is_activated) {
            throw new \Exception('Device is not activated');
        }

        $updateData = [
            'last_seen' => now()
        ];

        if ($appVersion) {
            $updateData['app_version'] = $appVersion;
        }

        if (!empty($additionalInfo)) {
            $updateData['hardware_info'] = array_merge($this->hardware_info ?? [], $additionalInfo, [
                'last_heartbeat' => now()->toISOString()
            ]);
        }

        $this->update($updateData);
    }

    /**
     * Check if device can be activated
     */
    public function canBeActivated()
    {
        if ($this->is_activated) {
            return false;
        }

        $company = $this->company;
        if (!$company) {
            return false;
        }

        if (!$company->hasValidLicense()) {
            return false;
        }

        $activeDevicesCount = static::byCompany($company->id)->where('is_activated', true)->count();
        
        return $activeDevicesCount < $company->max_devices;
    }

    /**
     * Get activation status with details
     */
    public function getActivationStatus()
    {
        if (!$this->is_activated) {
            return [
                'status' => 'inactive',
                'message' => 'Device not activated',
                'can_activate' => $this->canBeActivated(),
                'activation_key' => $this->activation_key,
                'company_device_usage' => $this->company ? "{$this->company->active_devices}/{$this->company->max_devices}" : 'N/A'
            ];
        }

        $lastSeenMinutes = $this->last_seen ? $this->last_seen->diffInMinutes(now()) : null;
        $isOnline = $lastSeenMinutes !== null && $lastSeenMinutes <= 5;

        return [
            'status' => 'active',
            'message' => 'Device activated and ' . ($isOnline ? 'online' : 'offline'),
            'activated_at' => $this->activated_at,
            'last_seen' => $this->last_seen,
            'is_online' => $isOnline,
            'app_version' => $this->app_version,
            'device_fingerprint' => $this->device_fingerprint ? substr($this->device_fingerprint, 0, 10) . '...' : null
        ];
    }

    /**
     * Check if a device fingerprint is already in use
     */
    public static function isDeviceFingerprintInUse($fingerprint)
    {
        return static::where('device_fingerprint', $fingerprint)
                    ->where('is_activated', true)
                    ->exists();
    }

    /**
     * Get device by fingerprint
     */
    public static function getByFingerprint($fingerprint)
    {
        return static::where('device_fingerprint', $fingerprint)
                    ->where('is_activated', true)
                    ->first();
    }

    /**
     * Scope for active devices
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active')->where('is_activated', true);
    }

    /**
     * Scope for activated devices
     */
    public function scopeActivated($query)
    {
        return $query->where('is_activated', true);
    }

    /**
     * Scope for devices by company
     */
    public function scopeByCompany($query, $companyId)
    {
        return $query->whereHas('branch', function($q) use ($companyId) {
            $q->where('company_id', $companyId);
        });
    }

    /**
     * Scope for online devices (seen within last 5 minutes)
     */
    public function scopeOnline($query)
    {
        return $query->where('last_seen', '>=', now()->subMinutes(5));
    }

    /**
     * Scope for offline devices
     */
    public function scopeOffline($query)
    {
        return $query->where('last_seen', '<', now()->subMinutes(5))
                    ->orWhereNull('last_seen');
    }

    /**
     * Get device status badge
     */
    public function getStatusBadgeAttribute()
    {
        if (!$this->is_activated) {
            return '<span class="badge badge-warning">Not Activated</span>';
        }

        if ($this->status !== 'active') {
            return '<span class="badge badge-danger">Inactive</span>';
        }

        $lastSeenMinutes = $this->last_seen ? $this->last_seen->diffInMinutes(now()) : null;
        $isOnline = $lastSeenMinutes !== null && $lastSeenMinutes <= 5;

        if ($isOnline) {
            return '<span class="badge badge-success">Online</span>';
        } else {
            return '<span class="badge badge-secondary">Offline</span>';
        }
    }

    /**
     * Get device info summary
     */
    public function getDeviceInfoSummaryAttribute()
    {
        $info = [];
        
        if ($this->device_model) {
            $info[] = $this->device_model;
        }
        
        if ($this->app_version) {
            $info[] = "v{$this->app_version}";
        }
        
        if ($this->hardware_info && isset($this->hardware_info['os'])) {
            $info[] = $this->hardware_info['os'];
        }
        
        return implode(' • ', $info);
    }

    /**
     * Validate device fingerprint format
     */
    public static function validateDeviceFingerprint($fingerprint)
    {
        if (empty($fingerprint)) {
            return false;
        }

        if (strlen($fingerprint) < 10) {
            return false;
        }

        // Additional validation rules can be added here
        return true;
    }

    /**
     * Validate MAC address format
     */
    public static function validateMacAddress($macAddress)
    {
        if (empty($macAddress)) {
            return false;
        }

        // Standard MAC address format validation (XX:XX:XX:XX:XX:XX or XX-XX-XX-XX-XX-XX)
        $pattern = '/^([0-9A-Fa-f]{2}[:-]){5}([0-9A-Fa-f]{2})$/';
        return preg_match($pattern, $macAddress) === 1;
    }

    /**
     * Check if MAC address is already registered to an activated device
     */
    public static function isMacAddressInUse($macAddress)
    {
        return static::where('mac_address', $macAddress)
                    ->where('is_activated', true)
                    ->exists();
    }

    /**
     * Get device by MAC address
     */
    public static function getByMacAddress($macAddress)
    {
        return static::where('mac_address', $macAddress)
                    ->where('is_activated', true)
                    ->first();
    }

    /**
     * Reset MAC address binding (admin only)
     */
    public function resetMacAddress($adminUserId, $reason = null)
    {
        if (!$this->is_activated) {
            throw new \Exception('Device is not activated');
        }

        if (empty($this->mac_address)) {
            throw new \Exception('Device does not have a MAC address to reset');
        }

        return DB::transaction(function () use ($adminUserId, $reason) {
            $oldMacAddress = $this->mac_address;
            
            $this->update([
                'mac_address' => null,
                'mac_address_reset_at' => now(),
                'mac_address_reset_by' => $adminUserId,
                'hardware_info' => array_merge($this->hardware_info ?? [], [
                    'mac_address_reset' => [
                        'at' => now()->toISOString(),
                        'by' => $adminUserId,
                        'reason' => $reason,
                        'previous_mac_address' => $oldMacAddress
                    ]
                ])
            ]);

            // Log the reset
            \Log::info('MAC address reset', [
                'device_id' => $this->id,
                'device_name' => $this->device_name,
                'old_mac_address' => $oldMacAddress,
                'admin_user_id' => $adminUserId,
                'reason' => $reason
            ]);

            return true;
        });
    }

    /**
     * Activate device with MAC address
     */
    public function activateWithMacAddress($deviceFingerprint, $macAddress, $hardwareInfo = [], $ipAddress = null, $appVersion = null)
    {
        // Validate MAC address
        if (!static::validateMacAddress($macAddress)) {
            throw new \Exception('Invalid MAC address format');
        }

        // Check if MAC address is already in use
        if (static::isMacAddressInUse($macAddress)) {
            $existingDevice = static::getByMacAddress($macAddress);
            throw new \Exception(
                'MAC address is already registered to device "' . $existingDevice->device_name . '" ' .
                'in company "' . ($existingDevice->company->name ?? 'Unknown') . '". ' .
                'Each MAC address can only be activated once.'
            );
        }

        // Use the existing activation logic but add MAC address
        return DB::transaction(function () use ($deviceFingerprint, $macAddress, $hardwareInfo, $ipAddress, $appVersion) {
            // Call the original activate method
            $this->activate($deviceFingerprint, $hardwareInfo, $ipAddress, $appVersion);
            
            // Update with MAC address
            $this->update([
                'mac_address' => $macAddress,
                'hardware_info' => array_merge($this->hardware_info ?? [], [
                    'mac_address_added_at' => now()->toISOString(),
                    'mac_address' => $macAddress
                ])
            ]);

            return true;
        });
    }
}
