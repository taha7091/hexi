<?php

namespace Tests\Feature;

use App\Models\PosDevice;
use App\Models\Company;
use App\Models\Branch;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MacAddressActivationTest extends TestCase
{
    use RefreshDatabase;

    public function test_mac_address_validation()
    {
        // Test valid MAC addresses
        $this->assertTrue(PosDevice::validateMacAddress('00:1A:2B:3C:4D:5E'));
        $this->assertTrue(PosDevice::validateMacAddress('00-1A-2B-3C-4D-5E'));
        $this->assertTrue(PosDevice::validateMacAddress('AA:BB:CC:DD:EE:FF'));
        
        // Test invalid MAC addresses
        $this->assertFalse(PosDevice::validateMacAddress(''));
        $this->assertFalse(PosDevice::validateMacAddress('invalid'));
        $this->assertFalse(PosDevice::validateMacAddress('00:1A:2B:3C:4D'));
        $this->assertFalse(PosDevice::validateMacAddress('00:1A:2B:3C:4D:5E:6F'));
        $this->assertFalse(PosDevice::validateMacAddress('GG:HH:II:JJ:KK:LL'));
    }

    public function test_mac_address_check_endpoint()
    {
        $response = $this->postJson('/api/pos/check-mac', [
            'mac_address' => '00:1A:2B:3C:4D:5E'
        ]);

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'in_use' => false,
                    'message' => 'MAC address is available'
                ]);
    }

    public function test_activation_with_mac_address()
    {
        $company = Company::factory()->create(['max_devices' => 5]);
        $branch = Branch::factory()->create(['company_id' => $company->id]);
        $device = PosDevice::factory()->create([
            'branch_id' => $branch->id,
            'is_activated' => false
        ]);

        $response = $this->postJson('/api/pos/activate', [
            'activation_key' => $device->activation_key,
            'device_fingerprint' => 'test-fingerprint-1234567890',
            'mac_address' => '00:1A:2B:3C:4D:5E',
            'hardware_info' => ['os' => 'Windows 10'],
            'app_version' => '1.0.0'
        ]);

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => 'Device activated successfully'
                ])
                ->assertJsonStructure([
                    'device_info' => [
                        'device_id',
                        'device_name',
                        'branch_name',
                        'company_name',
                        'activated_at',
                        'mac_address',
                        'status'
                    ]
                ]);

        // Verify the device was updated with MAC address
        $device->refresh();
        $this->assertEquals('00:1A:2B:3C:4D:5E', $device->mac_address);
        $this->assertTrue($device->is_activated);
    }

    public function test_activation_with_duplicate_mac_address()
    {
        $company = Company::factory()->create(['max_devices' => 5]);
        $branch = Branch::factory()->create(['company_id' => $company->id]);
        
        // Create first device with MAC address
        $device1 = PosDevice::factory()->create([
            'branch_id' => $branch->id,
            'is_activated' => true,
            'mac_address' => '00:1A:2B:3C:4D:5E'
        ]);

        // Try to activate second device with same MAC address
        $device2 = PosDevice::factory()->create([
            'branch_id' => $branch->id,
            'is_activated' => false
        ]);

        $response = $this->postJson('/api/pos/activate', [
            'activation_key' => $device2->activation_key,
            'device_fingerprint' => 'test-fingerprint-0987654321',
            'mac_address' => '00:1A:2B:3C:4D:5E',
            'hardware_info' => ['os' => 'Windows 10'],
            'app_version' => '1.0.0'
        ]);

        $response->assertStatus(409)
                ->assertJson([
                    'success' => false,
                    'message' => 'MAC address is already registered to another device'
                ]);
    }
}
