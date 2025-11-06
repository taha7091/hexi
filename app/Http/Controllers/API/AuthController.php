<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Handle user login request
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'device_name' => 'nullable|string',
        ]);

        // Find the user by email
        $user = User::where('email', $request->email)->first();

        // Check if user exists and password is correct
        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        // Generate device name if not provided
        $deviceName = $request->device_name ?? 'POS Terminal-' . time();

        // Create a token with appropriate abilities
        $tokenAbilities = [];
        
        // Add abilities based on user role
        if ($user->isMasterAdmin() || $user->isAdmin()) {
            $tokenAbilities[] = 'admin';
        }
        
        if ($user->canAccessPOS()) {
            $tokenAbilities[] = 'pos';
        }
        
        if ($user->canManageProducts()) {
            $tokenAbilities[] = 'products:manage';
        }

        // Create the token
        $token = $user->createToken($deviceName, $tokenAbilities);

        return response()->json([
            'success' => true,
            'token' => $token->plainTextToken,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'company_id' => $user->company_id,
                'branch_id' => $user->branch_id,
                'abilities' => $tokenAbilities,
            ]
        ]);
    }

    /**
     * Handle user logout request
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        // Revoke the token that was used to authenticate the current request
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Successfully logged out'
        ]);
    }

    /**
     * Handle PIN-based login for POS systems
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function pinLogin(Request $request)
    {
        $request->validate([
            'pin' => 'required|string',
            'company_id' => 'required|exists:companies,id',
        ]);

        // Find user by PIN and company
        $user = User::where('pin', $request->pin)
                   ->where('company_id', $request->company_id)
                   ->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid PIN or access denied.'
            ], 401);
        }

        // Check if company license is valid
        $company = $user->company;
        if ($company && $company->license_expiry < now()) {
            return response()->json([
                'success' => false,
                'message' => 'Company license has expired.'
            ], 403);
        }

        // Create token with appropriate abilities based on role
        $tokenAbilities = ['*']; // Full access for now, can be restricted later
        $deviceName = $request->device_name ?? 'POS Device';

        $token = $user->createToken($deviceName, $tokenAbilities);

        return response()->json([
            'success' => true,
            'token' => $token->plainTextToken,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'company_id' => $user->company_id,
                'branch_id' => $user->branch_id,
                'abilities' => $tokenAbilities,
            ]
        ]);
    }

    /**
     * Get current user information
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function user(Request $request)
    {
        $user = $request->user();
        
        return response()->json([
            'success' => true,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'company_id' => $user->company_id,
                'branch_id' => $user->branch_id,
                'company' => $user->company,
                'branch' => $user->branch,
                'abilities' => $user->currentAccessToken()->abilities,
            ]
        ]);
    }

    /**
     * Check if token is valid
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function check(Request $request)
    {
        return response()->json([
            'success' => true,
            'message' => 'Token is valid',
            'token_abilities' => $request->user()->currentAccessToken()->abilities
        ]);
    }
}