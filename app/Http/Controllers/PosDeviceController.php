<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\PosDevice;
use App\Models\Branch;
use App\Models\Company;

class POSDeviceController extends Controller
{
    /**
     * Get POS devices with access control
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // Master admin sees all POS devices
        if ($user->isMasterAdmin()) {
            $query = PosDevice::with(['branch.company', 'sales', 'syncLogs']);
        } else {
            // Regular users see only their company's POS devices
            $query = PosDevice::whereHas('branch', function($q) use ($user) {
                $q->where('company_id', $user->company_id);
            })->with(['branch', 'sales', 'syncLogs']);
        }

        // Apply filters
        if ($request->has('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        return response()->json([
            'success' => true,
            'data' => $query->get()
        ]);
    }

    /**
     * Create new POS device
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'device_name' => 'required|string|max:255',
            'device_serial' => 'required|string|max:255|unique:pos_devices,device_serial',
            'device_model' => 'nullable|string|max:255',
            'device_info' => 'nullable|array',
            'status' => 'nullable|in:active,inactive,maintenance'
        ]);

        $user = Auth::user();

        // Check if user can create POS devices for this branch
        if (!$user->isMasterAdmin()) {
            $branch = Branch::find($validated['branch_id']);
            if ($branch->company_id !== $user->company_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'You can only create POS devices for your company branches.'
                ], 403);
            }
        }

        // Check POS device limits
        $branch = Branch::find($validated['branch_id']);
        $company = $branch->company;
        $deviceLimit = $company->max_devices ?? $company->pos_limit ?? 1;
        $currentPOSCount = $company->active_device_count; // Uses combined count (PosDevice + MAC addresses)

        if ($currentPOSCount >= $deviceLimit) {
            return response()->json([
                'success' => false,
                'message' => "❌ DEVICE LIMIT EXCEEDED!\n\nYou have exceeded the registered license limit.\n\nCompany: {$company->name}\nRegistered devices: {$currentPOSCount}/{$deviceLimit}\n\nContact support to upgrade your license or deactivate unused devices.",
                'error_type' => 'DEVICE_LIMIT_EXCEEDED',
                'limit_info' => [
                    'device_limit' => $deviceLimit,
                    'active_devices' => $currentPOSCount,
                    'remaining_slots' => 0,
                    'company_name' => $company->name
                ]
            ], 403);
        }

        $validated['status'] = $validated['status'] ?? 'active';
        $validated['device_info'] = json_encode($validated['device_info'] ?? []);

        $posDevice = PosDevice::create($validated);

        return response()->json([
            'success' => true,
            'data' => $posDevice->load('branch.company'),
            'message' => 'POS device created successfully.'
        ], 201);
    }

    /**
     * Show specific POS device
     */
    public function show(PosDevice $posDevice)
    {
        $user = Auth::user();

        // Check access
        if (!$user->isMasterAdmin() && $posDevice->branch->company_id !== $user->company_id) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied.'
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => $posDevice->load(['branch.company', 'sales', 'syncLogs'])
        ]);
    }

    /**
     * Update POS device
     */
    public function update(Request $request, PosDevice $posDevice)
    {
        $validated = $request->validate([
            'device_name' => 'sometimes|required|string|max:255',
            'device_serial' => 'sometimes|required|string|max:255|unique:pos_devices,device_serial,' . $posDevice->id,
            'device_model' => 'nullable|string|max:255',
            'device_info' => 'nullable|array',
            'status' => 'sometimes|in:active,inactive,maintenance'
        ]);

        $user = Auth::user();

        // Check access
        if (!$user->isMasterAdmin() && $posDevice->branch->company_id !== $user->company_id) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied.'
            ], 403);
        }

        if (isset($validated['device_info'])) {
            $validated['device_info'] = json_encode($validated['device_info']);
        }

        $posDevice->update($validated);

        return response()->json([
            'success' => true,
            'data' => $posDevice->load('branch.company'),
            'message' => 'POS device updated successfully.'
        ]);
    }

    /**
     * Delete POS device
     */
    public function destroy(PosDevice $posDevice)
    {
        $user = Auth::user();

        // Check access
        if (!$user->isMasterAdmin() && $posDevice->branch->company_id !== $user->company_id) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied.'
            ], 403);
        }

        // Check if device has sales
        if ($posDevice->sales()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete POS device with existing sales records.'
            ], 400);
        }

        $posDevice->delete();

        return response()->json([
            'success' => true,
            'message' => 'POS device deleted successfully.'
        ]);
    }

    /**
     * Activate POS device
     */
    public function activate(Request $request, PosDevice $posDevice)
    {
        $validated = $request->validate([
            'activation_code' => 'nullable|string',
            'device_info' => 'nullable|array'
        ]);

        $user = Auth::user();

        // Check access
        if (!$user->isMasterAdmin() && $posDevice->branch->company_id !== $user->company_id) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied.'
            ], 403);
        }

        // Check if company license is active
        $company = $posDevice->branch->company;
        if ($company->license_expiry < now()) {
            return response()->json([
                'success' => false,
                'message' => 'Company license has expired.'
            ], 403);
        }

        // Update device info if provided
        if (isset($validated['device_info'])) {
            $currentInfo = json_decode($posDevice->device_info, true) ?? [];
            $newInfo = array_merge($currentInfo, $validated['device_info']);
            $posDevice->update([
                'device_info' => json_encode($newInfo),
                'status' => 'active'
            ]);
        } else {
            $posDevice->update(['status' => 'active']);
        }

        // Generate activation token
        $activationToken = bin2hex(random_bytes(32));

        return response()->json([
            'success' => true,
            'data' => [
                'device' => $posDevice->load('branch.company'),
                'activation_token' => $activationToken,
                'company' => $company,
                'pos_limit' => $company->pos_limit,
                'current_pos_count' => PosDevice::whereHas('branch', function($q) use ($company) {
                    $q->where('company_id', $company->id);
                })->count()
            ],
            'message' => 'POS device activated successfully.'
        ]);
    }

    /**
     * Deactivate POS device
     */
    public function deactivate(PosDevice $posDevice)
    {
        $user = Auth::user();

        // Check access
        if (!$user->isMasterAdmin() && $posDevice->branch->company_id !== $user->company_id) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied.'
            ], 403);
        }

        $posDevice->update(['status' => 'inactive']);

        return response()->json([
            'success' => true,
            'data' => $posDevice,
            'message' => 'POS device deactivated successfully.'
        ]);
    }
}
