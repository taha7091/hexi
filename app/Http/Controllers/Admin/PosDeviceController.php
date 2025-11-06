<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PosDevice;
use App\Models\Company;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PosDeviceController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = PosDevice::with(['branch.company']);

        // Filter by company for non-master admins
        if (!$user->isMasterAdmin()) {
            $query->whereHas('branch', function($q) use ($user) {
                $q->where('company_id', $user->company_id);
            });
        }

        // Apply filters
        if ($request->filled('company_id')) {
            $query->whereHas('branch', function($q) use ($request) {
                $q->where('company_id', $request->company_id);
            });
        }

        if ($request->filled('status')) {
            if ($request->status === 'activated') {
                $query->where('is_activated', true);
            } elseif ($request->status === 'not_activated') {
                $query->where('is_activated', false);
            } elseif ($request->status === 'online') {
                $query->where('last_seen', '>=', now()->subMinutes(5));
            } elseif ($request->status === 'offline') {
                $query->where(function($q) {
                    $q->where('last_seen', '<', now()->subMinutes(5))
                      ->orWhereNull('last_seen');
                });
            }
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('device_name', 'like', "%{$search}%")
                  ->orWhere('device_serial', 'like', "%{$search}%")
                  ->orWhere('activation_key', 'like', "%{$search}%");
            });
        }

        $devices = $query->orderBy('created_at', 'desc')->paginate(15);

        // Get companies for filter (master admin only)
        $companies = $user->isMasterAdmin() ? Company::all() : collect();

        // Get statistics
        $stats = [
            'total_devices' => $query->count(),
            'activated_devices' => (clone $query)->where('is_activated', true)->count(),
            'online_devices' => (clone $query)->where('last_seen', '>=', now()->subMinutes(5))->count(),
            'companies_with_devices' => $user->isMasterAdmin() ?
                Company::whereHas('posDevices')->count() : 1,
            'total_mac_devices' => $user->isMasterAdmin() ?
                Company::all()->sum(function($company) { return $company->active_device_count; }) :
                $user->company->active_device_count
        ];

        return view('admin.pos-devices.index', compact('devices', 'companies', 'stats'));
    }

    public function create()
    {
        $user = Auth::user();
        
        // Get branches based on user role
        if ($user->isMasterAdmin()) {
            $branches = Branch::with('company')->get();
        } else {
            $branches = Branch::where('company_id', $user->company_id)->get();
        }

        return view('admin.pos-devices.create', compact('branches'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'device_name' => 'required|string|max:255',
            'device_serial' => 'nullable|string|max:255|unique:pos_devices',
            'device_model' => 'nullable|string|max:255',
            'device_info' => 'nullable|array'
        ]);

        $user = Auth::user();
        $branch = Branch::findOrFail($request->branch_id);

        // Check if user can create device for this branch
        if (!$user->isMasterAdmin() && $branch->company_id !== $user->company_id) {
            return redirect()->back()->with('error', 'You can only create devices for your company');
        }

        // Check if company has reached device limit
        $company = $branch->company;
        $totalDevices = PosDevice::whereHas('branch', function($q) use ($company) {
            $q->where('company_id', $company->id);
        })->count();

        if ($totalDevices >= $company->max_devices) {
            return redirect()->back()->with('error', "Company has reached maximum device limit of {$company->max_devices} devices");
        }

        $device = PosDevice::create([
            'branch_id' => $request->branch_id,
            'device_name' => $request->device_name,
            'device_serial' => $request->device_serial,
            'device_model' => $request->device_model,
            'device_info' => $request->device_info ?? [],
            'status' => 'inactive'
        ]);

        return redirect()->route('admin.pos-devices.show', $device)
                        ->with('success', 'POS Device created successfully. Activation key: ' . $device->activation_key);
    }

    public function show(PosDevice $posDevice)
    {
        $user = Auth::user();
        
        // Check access permissions
        if (!$user->isMasterAdmin() && $posDevice->branch->company_id !== $user->company_id) {
            abort(403, 'Access denied');
        }

        $posDevice->load(['branch.company', 'sales' => function($query) {
            $query->latest()->limit(10);
        }]);

        $activationStatus = $posDevice->getActivationStatus();
        
        // Get device statistics
        $stats = [
            'total_sales' => $posDevice->sales()->count(),
            'total_revenue' => $posDevice->sales()->sum('total_amount'),
            'last_sale' => $posDevice->sales()->latest()->first(),
            'uptime_days' => $posDevice->activated_at ? 
                $posDevice->activated_at->diffInDays(now()) : 0
        ];

        return view('admin.pos-devices.show', compact('posDevice', 'activationStatus', 'stats'));
    }

    public function edit(PosDevice $posDevice)
    {
        $user = Auth::user();
        
        // Check access permissions
        if (!$user->isMasterAdmin() && $posDevice->branch->company_id !== $user->company_id) {
            abort(403, 'Access denied');
        }

        // Get branches based on user role
        if ($user->isMasterAdmin()) {
            $branches = Branch::with('company')->get();
        } else {
            $branches = Branch::where('company_id', $user->company_id)->get();
        }

        return view('admin.pos-devices.edit', compact('posDevice', 'branches'));
    }

    public function update(Request $request, PosDevice $posDevice)
    {
        $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'device_name' => 'required|string|max:255',
            'device_serial' => 'nullable|string|max:255|unique:pos_devices,device_serial,' . $posDevice->id,
            'device_model' => 'nullable|string|max:255',
            'device_info' => 'nullable|array'
        ]);

        $user = Auth::user();
        $branch = Branch::findOrFail($request->branch_id);

        // Check permissions
        if (!$user->isMasterAdmin() && 
            ($posDevice->branch->company_id !== $user->company_id || $branch->company_id !== $user->company_id)) {
            return redirect()->back()->with('error', 'Access denied');
        }

        $posDevice->update([
            'branch_id' => $request->branch_id,
            'device_name' => $request->device_name,
            'device_serial' => $request->device_serial,
            'device_model' => $request->device_model,
            'device_info' => $request->device_info ?? []
        ]);

        return redirect()->route('admin.pos-devices.show', $posDevice)
                        ->with('success', 'POS Device updated successfully');
    }

    public function destroy(PosDevice $posDevice)
    {
        $user = Auth::user();
        
        // Check permissions
        if (!$user->isMasterAdmin() && $posDevice->branch->company_id !== $user->company_id) {
            return redirect()->back()->with('error', 'Access denied');
        }

        // Check if device has sales
        if ($posDevice->sales()->count() > 0) {
            return redirect()->back()->with('error', 'Cannot delete device with existing sales records');
        }

        // Deactivate if activated
        if ($posDevice->is_activated) {
            $posDevice->deactivate('Device deleted by admin');
        }

        $posDevice->delete();

        return redirect()->route('admin.pos-devices.index')
                        ->with('success', 'POS Device deleted successfully');
    }

    public function activate(Request $request, PosDevice $posDevice)
    {
        $user = Auth::user();
        
        // Check permissions
        if (!$user->isMasterAdmin() && $posDevice->branch->company_id !== $user->company_id) {
            return response()->json(['success' => false, 'message' => 'Access denied'], 403);
        }

        try {
            if ($posDevice->is_activated) {
                return response()->json(['success' => false, 'message' => 'Device is already activated']);
            }

            // For admin activation, we'll use a system fingerprint
            $systemFingerprint = 'ADMIN_ACTIVATED_' . $posDevice->id . '_' . time();
            
            $posDevice->activate(
                $systemFingerprint,
                ['activated_by_admin' => true, 'admin_user_id' => $user->id],
                $request->ip(),
                'admin_panel'
            );

            return response()->json([
                'success' => true, 
                'message' => 'Device activated successfully',
                'status' => $posDevice->getActivationStatus()
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    public function deactivate(Request $request, PosDevice $posDevice)
    {
        $user = Auth::user();
        
        // Check permissions
        if (!$user->isMasterAdmin() && $posDevice->branch->company_id !== $user->company_id) {
            return response()->json(['success' => false, 'message' => 'Access denied'], 403);
        }

        try {
            if (!$posDevice->is_activated) {
                return response()->json(['success' => false, 'message' => 'Device is not activated']);
            }

            $reason = $request->input('reason', 'Deactivated by admin');
            $posDevice->deactivate($reason);

            return response()->json([
                'success' => true, 
                'message' => 'Device deactivated successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    public function regenerateKey(PosDevice $posDevice)
    {
        $user = Auth::user();
        
        // Check permissions
        if (!$user->isMasterAdmin() && $posDevice->branch->company_id !== $user->company_id) {
            return response()->json(['success' => false, 'message' => 'Access denied'], 403);
        }

        // Can only regenerate key for non-activated devices
        if ($posDevice->is_activated) {
            return response()->json(['success' => false, 'message' => 'Cannot regenerate key for activated device']);
        }

        $newKey = PosDevice::generateActivationKey();
        $posDevice->update(['activation_key' => $newKey]);

        return response()->json([
            'success' => true, 
            'message' => 'Activation key regenerated successfully',
            'new_key' => $newKey
        ]);
    }
}