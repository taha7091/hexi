<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Company;
use App\Models\User;
use Carbon\Carbon;

class MacActivationController extends Controller
{
    /**
     * Activate device with MAC address binding to company
     */
    public function activateDevice(Request $request)
    {
        $validated = $request->validate([
            'company_id' => 'required|exists:companies,id',
            'activation_key' => 'required|string',
            'mac_address' => 'required|string|min:12', // Accept any string with minimum 12 characters
            'device_fingerprint' => 'required|string',
            'hardware_info' => 'required|array',
            'app_version' => 'required|string'
        ]);

        DB::beginTransaction();

        try {
            $company = Company::findOrFail($validated['company_id']);

            // Verify activation key matches company license key
            if ($company->license_key !== $validated['activation_key']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid activation key for this company'
                ], 400);
            }

            // Check if company is active and license is valid
            if ($company->status !== 'active') {
                return response()->json([
                    'success' => false,
                    'message' => 'Company license is not active'
                ], 400);
            }

            if ($company->license_expiry && $company->license_expiry < now()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Company license has expired'
                ], 400);
            }

            // Normalize MAC address - remove separators and convert to uppercase
            $macAddress = strtoupper(str_replace([':', '-', ' '], '', $validated['mac_address']));
            $activatedMacs = $company->activated_mac_addresses ?? [];

            // Check if this MAC address is already activated for this company
            $existingActivation = collect($activatedMacs)->firstWhere('mac_address', $macAddress);
            
            if ($existingActivation) {
                // MAC already activated for this company - return success with existing info
                return response()->json([
                    'success' => true,
                    'message' => 'Device already activated for this company',
                    'device_info' => [
                        'company_id' => $company->id,
                        'company_name' => $company->name,
                        'mac_address' => $validated['mac_address'],
                        'activated_at' => $existingActivation['activated_at'],
                        'device_fingerprint' => $validated['device_fingerprint']
                    ]
                ]);
            }

            // Check device limit before adding new activation
            $deviceLimit = $company->max_devices ?? $company->pos_limit ?? 1;
            $currentActiveDevices = count($activatedMacs);

            if ($currentActiveDevices >= $deviceLimit) {
                return response()->json([
                    'success' => false,
                    'message' => "❌ DEVICE LIMIT EXCEEDED!\n\nYou have exceeded the registered license limit.\n\nCompany: {$company->name}\nRegistered devices: {$currentActiveDevices}/{$deviceLimit}\n\nContact support to upgrade your license or deactivate unused devices.",
                    'error_type' => 'DEVICE_LIMIT_EXCEEDED',
                    'limit_info' => [
                        'device_limit' => $deviceLimit,
                        'active_devices' => $currentActiveDevices,
                        'remaining_slots' => 0,
                        'company_name' => $company->name
                    ]
                ], 403);
            }

            // Add new MAC address activation
            $newActivation = [
                'mac_address' => $macAddress,
                'formatted_mac' => $validated['mac_address'],
                'device_fingerprint' => $validated['device_fingerprint'],
                'hardware_info' => $validated['hardware_info'],
                'app_version' => $validated['app_version'],
                'activated_at' => now()->toISOString(),
                'last_seen' => now()->toISOString()
            ];

            $activatedMacs[] = $newActivation;
            $company->activated_mac_addresses = $activatedMacs;
            $company->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Device activated successfully',
                'device_info' => [
                    'company_id' => $company->id,
                    'company_name' => $company->name,
                    'mac_address' => $validated['mac_address'],
                    'activated_at' => $newActivation['activated_at'],
                    'device_fingerprint' => $validated['device_fingerprint']
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            
            return response()->json([
                'success' => false,
                'message' => 'Activation failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check if MAC address is activated for a company
     */
    public function checkMacActivation(Request $request)
    {
        $validated = $request->validate([
            'company_id' => 'required|exists:companies,id',
            'mac_address' => 'required|string'
        ]);

        $company = Company::findOrFail($validated['company_id']);
        $macAddress = strtoupper(str_replace([':', '-', ' '], '', $validated['mac_address']));
        $activatedMacs = $company->activated_mac_addresses ?? [];

        $activation = collect($activatedMacs)->firstWhere('mac_address', $macAddress);

        if ($activation) {
            // Update last seen timestamp
            $activatedMacs = collect($activatedMacs)->map(function ($mac) use ($macAddress) {
                if ($mac['mac_address'] === $macAddress) {
                    $mac['last_seen'] = now()->toISOString();
                }
                return $mac;
            })->toArray();

            $company->activated_mac_addresses = $activatedMacs;
            $company->save();

            return response()->json([
                'success' => true,
                'activated' => true,
                'device_info' => [
                    'company_id' => $company->id,
                    'company_name' => $company->name,
                    'mac_address' => $activation['formatted_mac'],
                    'activated_at' => $activation['activated_at'],
                    'last_seen' => $activation['last_seen']
                ]
            ]);
        }

        return response()->json([
            'success' => true,
            'activated' => false,
            'message' => 'MAC address not activated for this company'
        ]);
    }

    /**
     * Reset MAC address for a company (Master Admin only)
     */
    public function resetMacAddress(Request $request)
    {
        // Check if user is master admin
        $user = Auth::user();
        if (!$user || !$user->isMasterAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Only master admin can reset MAC addresses.'
            ], 403);
        }

        $validated = $request->validate([
            'company_id' => 'required|exists:companies,id',
            'mac_address' => 'required|string'
        ]);

        DB::beginTransaction();

        try {
            $company = Company::findOrFail($validated['company_id']);
            $macAddress = strtoupper(str_replace([':', '-', ' '], '', $validated['mac_address']));
            $activatedMacs = $company->activated_mac_addresses ?? [];

            // Remove the MAC address from activated list
            $activatedMacs = collect($activatedMacs)->reject(function ($mac) use ($macAddress) {
                return $mac['mac_address'] === $macAddress;
            })->values()->toArray();

            $company->activated_mac_addresses = $activatedMacs;
            $company->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'MAC address reset successfully. Device can now be reactivated.'
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to reset MAC address: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all activated MAC addresses for a company (Master Admin only)
     */
    public function getCompanyMacAddresses(Request $request, $companyId)
    {
        // Check if user is master admin
        $user = Auth::user();
        if (!$user || !$user->isMasterAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Only master admin can view MAC addresses.'
            ], 403);
        }

        $company = Company::findOrFail($companyId);
        $activatedMacs = $company->activated_mac_addresses ?? [];

        return response()->json([
            'success' => true,
            'data' => [
                'company_id' => $company->id,
                'company_name' => $company->name,
                'activated_mac_addresses' => $activatedMacs,
                'total_activated' => count($activatedMacs)
            ]
        ]);
    }

    /**
     * Get all companies with their MAC addresses (Master Admin only)
     */
    public function getAllCompaniesWithMacs(Request $request)
    {
        // Check if user is master admin
        $user = Auth::user();
        if (!$user || !$user->isMasterAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Only master admin can view this information.'
            ], 403);
        }

        $companies = Company::select(['id', 'name', 'status', 'license_expiry', 'activated_mac_addresses'])
            ->get()
            ->map(function ($company) {
                $activatedMacs = $company->activated_mac_addresses ?? [];
                return [
                    'id' => $company->id,
                    'name' => $company->name,
                    'status' => $company->status,
                    'license_expiry' => $company->license_expiry,
                    'activated_mac_count' => count($activatedMacs),
                    'device_limit' => $company->device_limit,
                    'active_device_count' => $company->active_device_count,
                    'remaining_slots' => $company->remaining_device_slots,
                    'can_activate_more' => $company->canActivateMoreDevices(),
                    'activated_mac_addresses' => $activatedMacs
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $companies
        ]);
    }
}
