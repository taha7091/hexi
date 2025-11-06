<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'license_key',
        'license_expiry',
        'pos_limit',
        'daily_sales_limit',
        'daily_transaction_limit',
        'per_transaction_limit',
        'enforce_limits',
        'status',
        'contact_email',
        'contact_phone',
        'address',
        'notes',
        'max_devices',
        'active_devices',
        'allow_device_transfer',
        'device_restrictions',
        'activated_mac_addresses'
    ];

    protected $casts = [
        'license_expiry' => 'date',
        'allow_device_transfer' => 'boolean',
        'enforce_limits' => 'boolean',
        'device_restrictions' => 'array',
        'activated_mac_addresses' => 'array',
        'daily_sales_limit' => 'decimal:2',
        'per_transaction_limit' => 'decimal:2',
    ];

    public function brands()
    {
        return $this->hasMany(Brand::class);
    }

    public function branches()
    {
        return $this->hasMany(Branch::class);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function posLimits()
    {
        return $this->hasMany(PosLimit::class);
    }

    public function posDevices()
    {
        return $this->hasManyThrough(PosDevice::class, Branch::class);
    }

    /**
     * Get active POS devices for this company
     */
    public function activePosDevices()
    {
        return $this->posDevices()->where('is_activated', true);
    }

    /**
     * Check if company can activate more devices
     */
    public function canActivateMoreDevices()
    {
        $deviceLimit = $this->max_devices ?? $this->pos_limit ?? 1;
        $activeDevicesCount = $this->active_device_count; // Uses the updated attribute
        return $activeDevicesCount < $deviceLimit;
    }

    /**
     * Get remaining device slots
     */
    public function getRemainingDeviceSlotsAttribute()
    {
        $deviceLimit = $this->max_devices ?? $this->pos_limit ?? 1;
        $activeDevicesCount = $this->active_device_count; // Uses the updated attribute
        return max(0, $deviceLimit - $activeDevicesCount);
    }

    /**
     * Get current active device count (includes both PosDevice table and MAC addresses)
     */
    public function getActiveDeviceCountAttribute()
    {
        // Count devices from pos_devices table
        $posDevicesCount = $this->activePosDevices()->count();

        // Count devices from activated_mac_addresses JSON field
        $macAddressesCount = count($this->activated_mac_addresses ?? []);

        // Return the higher count (in case both systems are used)
        return max($posDevicesCount, $macAddressesCount);
    }

    /**
     * Get device limit (max_devices or pos_limit)
     */
    public function getDeviceLimitAttribute()
    {
        return $this->max_devices ?? $this->pos_limit ?? 1;
    }

    /**
     * Get device usage percentage
     */
    public function getDeviceUsagePercentageAttribute()
    {
        $deviceLimit = $this->device_limit;
        if ($deviceLimit == 0) {
            return 0;
        }

        return round(($this->active_device_count / $deviceLimit) * 100, 1);
    }

    /**
     * Check if transaction amount exceeds per-transaction limit
     */
    public function exceedsTransactionLimit($amount)
    {
        if (!$this->enforce_limits || !$this->per_transaction_limit) {
            return false;
        }

        return $amount > $this->per_transaction_limit;
    }

    /**
     * Check if daily sales limit would be exceeded
     */
    public function exceedsDailySalesLimit($additionalAmount = 0)
    {
        if (!$this->enforce_limits || !$this->daily_sales_limit) {
            return false;
        }

        $todaysSales = $this->getTodaysSalesTotal();
        return ($todaysSales + $additionalAmount) > $this->daily_sales_limit;
    }

    /**
     * Check if daily transaction count limit would be exceeded
     */
    public function exceedsDailyTransactionLimit($additionalCount = 1)
    {
        if (!$this->enforce_limits || !$this->daily_transaction_limit) {
            return false;
        }

        $todaysTransactionCount = $this->getTodaysTransactionCount();
        return ($todaysTransactionCount + $additionalCount) > $this->daily_transaction_limit;
    }

    /**
     * Get today's total sales amount for this company
     */
    public function getTodaysSalesTotal()
    {
        return \App\Models\Sale::whereHas('posDevice.branch', function($query) {
            $query->where('company_id', $this->id);
        })
        ->whereDate('created_at', today())
        ->sum('total_amount');
    }

    /**
     * Get today's transaction count for this company
     */
    public function getTodaysTransactionCount()
    {
        return \App\Models\Sale::whereHas('posDevice.branch', function($query) {
            $query->where('company_id', $this->id);
        })
        ->whereDate('created_at', today())
        ->count();
    }

    /**
     * Get remaining daily sales limit
     */
    public function getRemainingDailySalesLimit()
    {
        if (!$this->daily_sales_limit) {
            return null;
        }

        return max(0, $this->daily_sales_limit - $this->getTodaysSalesTotal());
    }

    /**
     * Get remaining daily transaction limit
     */
    public function getRemainingDailyTransactionLimit()
    {
        if (!$this->daily_transaction_limit) {
            return null;
        }

        return max(0, $this->daily_transaction_limit - $this->getTodaysTransactionCount());
    }

    /**
     * Update active device count
     */
    public function updateActiveDeviceCount()
    {
        $count = $this->posDevices()->where('is_activated', true)->count();
        $this->update(['active_devices' => $count]);
        return $count;
    }

    /**
     * Check if company license is valid
     */
    public function hasValidLicense()
    {
        return $this->status === 'active' && 
               ($this->license_expiry === null || $this->license_expiry->isFuture());
    }

    /**
     * Get company status with device info
     */
    public function getStatusWithDevicesAttribute()
    {
        $status = [
            'company_status' => $this->status,
            'license_valid' => $this->hasValidLicense(),
            'device_usage' => "{$this->active_devices}/{$this->max_devices}",
            'device_percentage' => $this->device_usage_percentage,
            'can_activate_more' => $this->canActivateMoreDevices()
        ];

        if ($this->license_expiry) {
            $status['license_expires'] = $this->license_expiry->format('Y-m-d');
            $status['days_until_expiry'] = $this->license_expiry->diffInDays(now());
        }

        return $status;
    }
}