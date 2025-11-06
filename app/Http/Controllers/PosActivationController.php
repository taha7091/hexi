<?php

namespace App\Http\Controllers;

use App\Models\PosDevice;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class PosActivationController extends Controller
{
    /**
     * Activate a POS device - IMPROVED VERSION with MAC address support
     */
    public function activate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'activation_key' => 'required|string|size:19', // Format: XXXX-XXXX-XXXX-XXXX
            'device_fingerprint' => 'required|string|min:10|max:255',
            'mac_address' => 'required|string|max:17', // MAC address format validation
            'hardware_info' => 'array',
            'app_version' => 'string|max:20'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid activation data',
                'errors' => $validator->errors()
            ], 400);
        }

        try {
            // Validate device fingerprint format
            if (!PosDevice::validateDeviceFingerprint($request->device_fingerprint)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid device fingerprint format'
                ], 400);
            }

            // Validate MAC address format
            if (!PosDevice::validateMacAddress($request->mac_address)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid MAC address format. Expected format: XX:XX:XX:XX:XX:XX or XX-XX-XX-XX-XX-XX'
                ], 400);
            }

            // CRITICAL: Check if device fingerprint is already in use BEFORE finding the device
            if (PosDevice::isDeviceFingerprintInUse($request->device_fingerprint)) {
                $existingDevice = PosDevice::getByFingerprint($request->device_fingerprint);
                return response()->json([
                    'success' => false,
                    'message' => 'This physical device is already activated',
                    'conflict_info' => [
                        'existing_device_name' => $existingDevice->device_name,
                        'existing_company' => $existingDevice->company->name ?? 'Unknown Company',
                        'existing_branch' => $existingDevice->branch->name ?? 'Unknown Branch',
                        'activated_at' => $existingDevice->activated_at,
                        'message' => 'Each physical device can only be activated once across the entire system.'
                    ]
                ], 409);
            }

            // Check if MAC address is already in use
            if (PosDevice::isMacAddressInUse($request->mac_address)) {
                $existingDevice = PosDevice::getByMacAddress($request->mac_address);
                return response()->json([
                    'success' => false,
                    'message' => 'MAC address is already registered to another device',
                    'conflict_info' => [
                        'existing_device_name' => $existingDevice->device_name,
                        'existing_company' => $existingDevice->company->name ?? 'Unknown Company',
                        'existing_branch' => $existingDevice->branch->name ?? 'Unknown Branch',
                        'activated_at' => $existingDevice->activated_at,
                        'message' => 'Each MAC address can only be registered to one device.'
                    ]
                ], 409);
            }

            // Find device by activation key
            $device = PosDevice::where('activation_key', $request->activation_key)->first();
            
            if (!$device) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid activation key'
                ], 404);
            }

            // Check if device is already activated
            if ($device->is_activated) {
                return response()->json([
                    'success' => false,
                    'message' => 'This POS device registration is already activated',
                    'device_info' => [
                        'device_name' => $device->device_name,
                        'activated_at' => $device->activated_at,
                        'branch' => $device->branch->name ?? 'Unknown Branch',
                        'company' => $device->company->name ?? 'Unknown Company',
                        'current_fingerprint' => $device->device_fingerprint ? substr($device->device_fingerprint, 0, 10) . '...' : null,
                        'current_mac_address' => $device->mac_address
                    ]
                ], 409);
            }

            // Check company license
            $company = $device->company;
            if (!$company || !$company->hasValidLicense()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Company license is invalid or expired'
                ], 403);
            }

            // Check company device limit before activation
            $deviceLimit = $company->max_devices ?? $company->pos_limit ?? 1;
            $activeDevicesCount = $company->active_device_count; // Uses combined count (PosDevice + MAC addresses)

            if ($activeDevicesCount >= $deviceLimit) {
                return response()->json([
                    'success' => false,
                    'message' => "❌ DEVICE LIMIT EXCEEDED!\n\nYou have exceeded the registered license limit.\n\nRegistered devices: {$activeDevicesCount}/{$deviceLimit}\n\nContact support to upgrade your license or deactivate unused devices.",
                    'error_type' => 'DEVICE_LIMIT_EXCEEDED',
                    'limit_info' => [
                        'device_limit' => $deviceLimit,
                        'active_devices' => $activeDevicesCount,
                        'remaining_slots' => 0,
                        'company_name' => $company->name
                    ]
                ], 403);
            }

            // Activate the device with MAC address
            $device->activateWithMacAddress(
                $request->device_fingerprint,
                $request->mac_address,
                $request->hardware_info ?? [],
                $request->ip(),
                $request->app_version
            );

            Log::info('POS Device activated successfully with MAC address', [
                'device_id' => $device->id,
                'device_name' => $device->device_name,
                'company' => $company->name,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'device_fingerprint_preview' => substr($request->device_fingerprint, 0, 10) . '...',
                'mac_address' => $request->mac_address
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Device activated successfully',
                'device_info' => [
                    'device_id' => $device->id,
                    'device_name' => $device->device_name,
                    'branch_name' => $device->branch->name ?? 'Unknown Branch',
                    'company_name' => $company->name,
                    'activated_at' => $device->activated_at,
                    'mac_address' => $device->mac_address,
                    'status' => $device->getActivationStatus()
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('POS Device activation failed', [
                'activation_key' => $request->activation_key,
                'device_fingerprint_preview' => isset($request->device_fingerprint) ? substr($request->device_fingerprint, 0, 10) . '...' : 'N/A',
                'mac_address' => $request->mac_address ?? 'N/A',
                'error' => $e->getMessage(),
                'ip_address' => $request->ip(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Deactivate a POS device
     */
    public function deactivate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'device_fingerprint' => 'required|string',
            'reason' => 'string|max:255'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid deactivation data',
                'errors' => $validator->errors()
            ], 400);
        }

        try {
            $device = PosDevice::where('device_fingerprint', $request->device_fingerprint)
                              ->where('is_activated', true)
                              ->first();

            if (!$device) {
                return response()->json([
                    'success' => false,
                    'message' => 'Device not found or not activated'
                ], 404);
            }

            $device->deactivate($request->reason);

            Log::info('POS Device deactivated', [
                'device_id' => $device->id,
                'device_name' => $device->device_name,
                'reason' => $request->reason,
                'ip_address' => $request->ip()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Device deactivated successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('POS Device deactivation failed', [
                'device_fingerprint' => substr($request->device_fingerprint, 0, 10) . '...',
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Check device status
     */
    public function status(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'device_fingerprint' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Device fingerprint required',
                'errors' => $validator->errors()
            ], 400);
        }

        $device = PosDevice::where('device_fingerprint', $request->device_fingerprint)->first();

        if (!$device) {
            return response()->json([
                'success' => false,
                'message' => 'Device not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'device_info' => [
                'device_id' => $device->id,
                'device_name' => $device->device_name,
                'branch_name' => $device->branch->name ?? 'Unknown Branch',
                'company_name' => $device->company->name ?? 'Unknown Company',
                'status' => $device->getActivationStatus(),
                'last_seen' => $device->last_seen,
                'app_version' => $device->app_version,
                'mac_address' => $device->mac_address
            ]
        ]);
    }

    /**
     * Check if MAC address is already registered
     */
    public function checkMac(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'mac_address' => 'required|string|max:17' // MAC address format validation
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid MAC address data',
                'errors' => $validator->errors()
            ], 400);
        }

        $isInUse = PosDevice::isMacAddressInUse($request->mac_address);
        
        return response()->json([
            'success' => true,
            'mac_address' => $request->mac_address,
            'in_use' => $isInUse,
            'message' => $isInUse ? 'MAC address is already registered' : 'MAC address is available'
        ]);
    }

    /**
     * Admin reset MAC address binding
     */
    public function resetMacAddress(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'device_fingerprint' => 'required|string',
            'admin_user_id' => 'required|exists:users,id',
            'reason' => 'string|max:255|nullable'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid reset data',
                'errors' => $validator->errors()
            ], 400);
        }

        $device = PosDevice::where('device_fingerprint', $request->device_fingerprint)->first();

        if (!$device) {
            return response()->json([
                'success' => false,
                'message' => 'Device not found'
            ], 404);
        }

        if (!auth()->user()->isMasterAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized action'
            ], 403);
        }

        try {
            $device->resetMacAddress($request->admin_user_id, $request->reason);
            return response()->json([
                'success' => true,
                'message' => 'MAC address reset successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Device heartbeat - update last seen
     */
    public function heartbeat(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'device_fingerprint' => 'required|string',
            'app_version' => 'string|max:20',
            'additional_info' => 'array'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid heartbeat data',
                'errors' => $validator->errors()
            ], 400);
        }

        $device = PosDevice::where('device_fingerprint', $request->device_fingerprint)
                          ->where('is_activated', true)
                          ->first();

        if (!$device) {
            return response()->json([
                'success' => false,
                'message' => 'Device not found or not activated'
            ], 404);
        }

        try {
            $device->updateHeartbeat(
                $request->app_version,
                $request->additional_info ?? []
            );

            return response()->json([
                'success' => true,
                'message' => 'Heartbeat updated',
                'server_time' => now()->toISOString()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Get device activation info by key
     */
    public function getDeviceInfo(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'activation_key' => 'required|string|size:19'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid activation key format',
                'errors' => $validator->errors()
            ], 400);
        }

        $device = PosDevice::where('activation_key', $request->activation_key)->first();

        if (!$device) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid activation key'
            ], 404);
        }

        $company = $device->company;

        return response()->json([
            'success' => true,
            'device_info' => [
                'device_name' => $device->device_name,
                'device_serial' => $device->device_serial,
                'branch_name' => $device->branch->name ?? 'Unknown Branch',
                'company_name' => $company->name ?? 'Unknown Company',
                'is_activated' => $device->is_activated,
                'can_activate' => $device->canBeActivated(),
                'company_device_usage' => $company ? "{$company->active_device_count}/{$company->device_limit}" : 'N/A',
                'license_valid' => $company ? $company->hasValidLicense() : false,
                'activation_status' => $device->getActivationStatus()
            ]
        ]);
    }

    /**
     * Check if device fingerprint is available for activation
     */
    public function checkFingerprint(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'device_fingerprint' => 'required|string|min:10'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid device fingerprint',
                'errors' => $validator->errors()
            ], 400);
        }

        $isInUse = PosDevice::isDeviceFingerprintInUse($request->device_fingerprint);
        
        if ($isInUse) {
            $existingDevice = PosDevice::getByFingerprint($request->device_fingerprint);
            return response()->json([
                'success' => false,
                'message' => 'Device fingerprint is already in use',
                'in_use' => true,
                'existing_device' => [
                    'device_name' => $existingDevice->device_name,
                    'company_name' => $existingDevice->company->name ?? 'Unknown Company',
                    'activated_at' => $existingDevice->activated_at
                ]
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Device fingerprint is available',
            'in_use' => false
        ]);
    }

    /**
     * Transfer device to another hardware (admin only)
     */
    public function transfer(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'device_id' => 'required|exists:pos_devices,id',
            'new_device_fingerprint' => 'required|string|min:10',
            'reason' => 'required|string|max:255',
            'hardware_info' => 'array'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid transfer data',
                'errors' => $validator->errors()
            ], 400);
        }

        try {
            $device = PosDevice::findOrFail($request->device_id);
            $company = $device->company;

            // Check if company allows device transfer
            if (!$company || !$company->allow_device_transfer) {
                return response()->json([
                    'success' => false,
                    'message' => 'Device transfer not allowed for this company'
                ], 403);
            }

            // Check if new fingerprint is already used
            if (PosDevice::isDeviceFingerprintInUse($request->new_device_fingerprint)) {
                return response()->json([
                    'success' => false,
                    'message' => 'New device fingerprint is already in use'
                ], 409);
            }

            // Update device fingerprint and hardware info
            $oldFingerprint = $device->device_fingerprint;
            $device->update([
                'device_fingerprint' => $request->new_device_fingerprint,
                'hardware_info' => array_merge($device->hardware_info ?? [], [
                    'transferred_at' => now()->toISOString(),
                    'transfer_reason' => $request->reason,
                    'previous_fingerprint' => $oldFingerprint,
                    'new_hardware_info' => $request->hardware_info ?? []
                ]),
                'ip_address' => $request->ip(),
                'last_seen' => now()
            ]);

            Log::info('POS Device transferred', [
                'device_id' => $device->id,
                'old_fingerprint' => substr($oldFingerprint, 0, 10) . '...',
                'new_fingerprint' => substr($request->new_device_fingerprint, 0, 10) . '...',
                'reason' => $request->reason,
                'admin_ip' => $request->ip()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Device transferred successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('POS Device transfer failed', [
                'device_id' => $request->device_id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Activate device with company number instead of activation key
     */
    public function activateWithCompany(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'company_number' => 'required|exists:companies,id',
            'device_fingerprint' => 'required|string|min:10|max:255',
            'mac_address' => 'required|string|max:17',
            'hardware_info' => 'array',
            'app_version' => 'string|max:20'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid activation data',
                'errors' => $validator->errors()
            ], 400);
        }

        try {
            // Validate device fingerprint format
            if (!PosDevice::validateDeviceFingerprint($request->device_fingerprint)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid device fingerprint format'
                ], 400);
            }

            // Validate MAC address format
            if (!PosDevice::validateMacAddress($request->mac_address)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid MAC address format. Expected format: XX:XX:XX:XX:XX:XX or XX-XX-XX-XX-XX-XX'
                ], 400);
            }

            // Check if device fingerprint is already in use
            if (PosDevice::isDeviceFingerprintInUse($request->device_fingerprint)) {
                $existingDevice = PosDevice::getByFingerprint($request->device_fingerprint);
                return response()->json([
                    'success' => false,
                    'message' => 'This physical device is already activated',
                    'conflict_info' => [
                        'existing_device_name' => $existingDevice->device_name,
                        'existing_company' => $existingDevice->company->name ?? 'Unknown Company',
                        'existing_branch' => $existingDevice->branch->name ?? 'Unknown Branch',
                        'activated_at' => $existingDevice->activated_at,
                        'message' => 'Each physical device can only be activated once across the entire system.'
                    ]
                ], 409);
            }

            // Check if MAC address is already in use
            if (PosDevice::isMacAddressInUse($request->mac_address)) {
                $existingDevice = PosDevice::getByMacAddress($request->mac_address);
                return response()->json([
                    'success' => false,
                    'message' => 'MAC address is already registered to another device',
                    'conflict_info' => [
                        'existing_device_name' => $existingDevice->device_name,
                        'existing_company' => $existingDevice->company->name ?? 'Unknown Company',
                        'existing_branch' => $existingDevice->branch->name ?? 'Unknown Branch',
                        'activated_at' => $existingDevice->activated_at,
                        'message' => 'Each MAC address can only be registered to one device.'
                    ]
                ], 409);
            }

            // Get company
            $company = Company::find($request->company_number);
            if (!$company) {
                return response()->json([
                    'success' => false,
                    'message' => 'Company not found'
                ], 404);
            }

            // Check company license
            if (!$company->hasValidLicense()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Company license is invalid or expired'
                ], 403);
            }

            // Check company device limit
            $deviceLimit = $company->max_devices ?? $company->pos_limit ?? 1;
            $activeDevicesCount = $company->active_device_count; // Uses combined count (PosDevice + MAC addresses)

            if ($activeDevicesCount >= $deviceLimit) {
                return response()->json([
                    'success' => false,
                    'message' => "❌ DEVICE LIMIT EXCEEDED!\n\nYou have exceeded the registered license limit.\n\nCompany: {$company->name}\nRegistered devices: {$activeDevicesCount}/{$deviceLimit}\n\nContact support to upgrade your license or deactivate unused devices.",
                    'error_type' => 'DEVICE_LIMIT_EXCEEDED',
                    'limit_info' => [
                        'device_limit' => $deviceLimit,
                        'active_devices' => $activeDevicesCount,
                        'remaining_slots' => 0,
                        'company_name' => $company->name
                    ]
                ], 403);
            }

            // Get first branch for the company (or create one if none exists)
            $branch = $company->branches()->first();
            if (!$branch) {
                $branch = Branch::create([
                    'company_id' => $company->id,
                    'name' => 'Main Branch',
                    'code' => 'MAIN',
                    'status' => 'active'
                ]);
            }

            // Create new device
            $device = PosDevice::create([
                'branch_id' => $branch->id,
                'device_name' => 'POS Device ' . substr($request->device_fingerprint, 0, 8),
                'device_serial' => 'SERIAL-' . substr($request->device_fingerprint, 0, 6),
                'activation_key' => PosDevice::generateActivationKey(),
                'is_activated' => false,
                'status' => 'pending'
            ]);

            // Activate the device with MAC address
            $device->activateWithMacAddress(
                $request->device_fingerprint,
                $request->mac_address,
                $request->hardware_info ?? [],
                $request->ip(),
                $request->app_version
            );

            Log::info('POS Device activated with company number', [
                'device_id' => $device->id,
                'device_name' => $device->device_name,
                'company_id' => $company->id,
                'company_name' => $company->name,
                'ip_address' => $request->ip(),
                'device_fingerprint_preview' => substr($request->device_fingerprint, 0, 10) . '...',
                'mac_address' => $request->mac_address
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Device activated successfully with company',
                'device_info' => [
                    'device_id' => $device->id,
                    'device_name' => $device->device_name,
                    'branch_name' => $device->branch->name ?? 'Unknown Branch',
                    'company_name' => $company->name,
                    'activated_at' => $device->activated_at,
                    'mac_address' => $device->mac_address,
                    'status' => $device->getActivationStatus(),
                    'activation_key' => $device->activation_key // Return activation key for future reference
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('POS Device activation with company failed', [
                'company_number' => $request->company_number,
                'device_fingerprint_preview' => isset($request->device_fingerprint) ? substr($request->device_fingerprint, 0, 10) . '...' : 'N/A',
                'mac_address' => $request->mac_address ?? 'N/A',
                'error' => $e->getMessage(),
                'ip_address' => $request->ip(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Get devices for a company with MAC addresses
     */
    public function getCompanyDevices($companyId)
    {
        try {
            $company = Company::find($companyId);
            if (!$company) {
                return response()->json([
                    'success' => false,
                    'message' => 'Company not found'
                ], 404);
            }

            $devices = PosDevice::with(['branch'])
                               ->whereHas('branch', function($q) use ($companyId) {
                                   $q->where('company_id', $companyId);
                               })
                               ->get()
                               ->map(function($device) {
                                   return [
                                       'id' => $device->id,
                                       'device_name' => $device->device_name,
                                       'mac_address' => $device->mac_address,
                                       'is_activated' => $device->is_activated,
                                       'activated_at' => $device->activated_at,
                                       'last_seen' => $device->last_seen,
                                       'branch_name' => $device->branch->name ?? 'Unknown',
                                       'status' => $device->status
                                   ];
                               });

            return response()->json([
                'success' => true,
                'data' => $devices
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching company devices: ' . $e->getMessage()
            ], 500);
        }
    }
}