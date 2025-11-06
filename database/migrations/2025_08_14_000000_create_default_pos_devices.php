<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Company;
use App\Models\Branch;
use App\Models\PosDevice;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Create default POS devices for existing companies
        $companies = Company::with('branches')->get();
        
        foreach ($companies as $company) {
            foreach ($company->branches as $branch) {
                // Check if branch already has a POS device
                $existingDevice = PosDevice::where('branch_id', $branch->id)->first();
                
                if (!$existingDevice) {
                    PosDevice::create([
                        'branch_id' => $branch->id,
                        'device_name' => 'Web POS - ' . $branch->name,
                        'device_serial' => 'WEB-' . $branch->id . '-' . time(),
                        'status' => 'active',
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove default web POS devices (by serial pattern)
        PosDevice::where('device_serial', 'like', 'WEB-%')->delete();
    }
};
