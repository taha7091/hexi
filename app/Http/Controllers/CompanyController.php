<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Company;

class CompanyController extends Controller
{
    public function index() {
        return Company::all();
    }

    public function store(Request $request) {
        $validated = $request->validate([
            'name' => 'required',
            'license_key' => 'required|unique:companies',
            'license_expiry' => 'required|date',
            'pos_limit' => 'required|integer'
        ]);
        return Company::create($validated);
    }

    /**
     * Get company by ID
     * Public endpoint for POS activation
     */
    public function show($id)
    {
        try {
            $company = Company::find($id);

            if (!$company) {
                return response()->json([
                    'success' => false,
                    'message' => 'Company not found'
                ], 404);
            }

            // Return a JSON response with 'success' and 'data' keys
            return response()->json([
                'success' => true,
                'data' => $company
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching company: ' . $e->getMessage()
            ], 500);
        }
    }
    public function update(Request $request, Company $company) {
        $company->update($request->all());
        return $company;
    }

    public function destroy(Company $company) {
        $company->delete();
        return response()->noContent();
    }

    /**
     * Get company device information including current usage
     */
    public function getDeviceInfo($id)
    {
        try {
            $company = Company::find($id);

            if (!$company) {
                return response()->json([
                    'success' => false,
                    'message' => 'Company not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'company_id' => $company->id,
                    'company_name' => $company->name,
                    'device_limit' => $company->device_limit,
                    'active_devices' => $company->active_device_count,
                    'remaining_slots' => $company->remaining_device_slots,
                    'can_activate_more' => $company->canActivateMoreDevices(),
                    'device_usage_percentage' => $company->device_usage_percentage,
                    'license_valid' => $company->hasValidLicense(),
                    'license_expiry' => $company->license_expiry,
                    'status' => $company->status
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching company device info: ' . $e->getMessage()
            ], 500);
        }
    }
}