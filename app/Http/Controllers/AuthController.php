<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Company;

class AuthController extends Controller
{
    /**
     * Show the login form
     */
    public function showLoginForm()
    {
        if (Auth::check()) {
            return redirect()->route('layouts.admin');
        }
        
        return view('auth.login');
    }

    /**
     * Handle login request
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'company_id' => 'nullable|exists:companies,id'
        ]);

        // Convert empty string to null for company_id
        $companyId = $request->company_id ?: null;

        // Check if this is master admin login (no company_id needed)
        if (!$companyId) {
            $masterAdmin = User::where('email', $request->email)
                              ->whereNull('company_id')
                              ->where('role', 'master_admin')
                              ->first();

            if ($masterAdmin && Hash::check($request->password, $masterAdmin->password)) {
                Auth::login($masterAdmin, $request->filled('remember'));
                return redirect()->intended(route('layouts.admin'));
            }

            // If no master admin found with this email, show error
            return back()->withErrors([
                'login' => 'Invalid master admin credentials.'
            ])->withInput($request->except('password'));
        }

        // For regular users, company_id is required and provided

        // Find user with matching email and company_id
        $user = User::where('email', $request->email)
                   ->where('company_id', $companyId)
                   ->first();

        if ($user && Hash::check($request->password, $user->password)) {
            // Check if company license is valid
            $company = Company::find($companyId);
            if ($company->license_expiry < now()) {
                return back()->withErrors([
                    'login' => 'Company license has expired. Please contact administrator.'
                ]);
            }

            Auth::login($user, $request->filled('remember'));
            return redirect()->intended(route('layouts.admin'));
        }

        return back()->withErrors([
            'login' => 'Invalid credentials or company access denied.'
        ])->withInput($request->except('password'));
    }

    /**
     * Handle logout request
     */
    public function logout(Request $request)
    {
        Auth::logout();
        
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect()->route('login');
    }

    /**
     * API Login endpoint
     */
    public function apiLogin(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'company_id' => 'nullable|exists:companies,id'
        ]);

        // Check if this is master admin login
        $masterAdmin = User::where('email', $request->email)
                          ->whereNull('company_id')
                          ->where('role', 'master_admin')
                          ->first();

        if ($masterAdmin && Hash::check($request->password, $masterAdmin->password)) {
            Auth::login($masterAdmin);
            return response()->json([
                'success' => true,
                'user' => $masterAdmin,
                'token' => $request->session()->token()
            ]);
        }

        // For regular users, company_id is required
        if (!$request->company_id) {
            return response()->json([
                'success' => false,
                'message' => 'Company ID is required for regular users.'
            ], 400);
        }

        // Find user with matching email and company_id
        $user = User::where('email', $request->email)
                   ->where('company_id', $request->company_id)
                   ->first();

        if ($user && Hash::check($request->password, $user->password)) {
            // Check if company license is valid
            $company = Company::find($request->company_id);
            if ($company->license_expiry < now()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Company license has expired.'
                ], 403);
            }

            Auth::login($user);
            return response()->json([
                'success' => true,
                'user' => $user->load('company', 'branch'),
                'token' => $request->session()->token()
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Invalid credentials or company access denied.'
        ], 401);
    }

    /**
     * API Logout endpoint
     */
    public function apiLogout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json(['success' => true, 'message' => 'Logged out successfully']);
    }

    /**
     * Get current authenticated user
     */
    public function me(Request $request)
    {
        return response()->json([
            'success' => true,
            'user' => Auth::user()->load('company', 'branch')
        ]);
    }

    /**
     * POS Device Activation
     */
    public function activatePOS(Request $request)
    {
        $request->validate([
            'company_id' => 'required|exists:companies,id',
            'device_name' => 'required|string',
            'device_info' => 'nullable|array'
        ]);

        $company = Company::find($request->company_id);

        // Check if company license is active
        if ($company->license_expiry < now()) {
            return response()->json([
                'success' => false,
                'message' => 'Company license has expired.'
            ], 403);
        }

        // Check POS device limit
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

        // Create activation token
        $activationToken = bin2hex(random_bytes(32));

        return response()->json([
            'success' => true,
            'message' => 'POS device can be activated',
            'activation_token' => $activationToken,
            'company' => $company,
            'pos_limit' => $company->pos_limit,
            'current_pos_count' => $currentPOSCount
        ]);
    }
}
