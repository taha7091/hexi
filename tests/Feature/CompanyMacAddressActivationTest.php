<?php

namespace Tests\Feature;

use App\Models\PosDevice;
use App\Models\Company;
use App\Models\Branch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompanyMacAddressActivationTest extends TestCase
{
    use RefreshDatabase;

    public function test_company_based_activation_with_mac_address()
    {
        // Create a company with device limits
        $company = Company::factory()->create([
            'name' => 'Test Company',
            'max_devices' => 3,
            'active_devices' => 0
        ]);

        $branch = Branch::factory()->create(['company_id' => $company->id]);
        $device = PosDevice::factory()->create([
            'branch_id' => $branch->id,
            'is_activated' => false
        ]);

        // Activate device with company context
        $response = $this->postJson('/api/pos/activate', [
            'activation_key' => $device->activation_key,
            'device_fingerprint' => 'test-fingerprint-1234567890',
            'mac_address' => '00:1A:2B:3C:4D:5E',
            'hardware_info' => ['os' => 'Windows 10'],
            'app_version' => '1.0.0',
            'company_id' => $company->id
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

        // Verify the device was updated with MAC address and company context
        $device->refresh();
        $this->assertEquals('00:1A:2B:3C:4D:5E', $device->mac_address);
        $this->assertTrue($device->is_activated);
        $this->assertEquals($company->id, $device->branch->company_id);

        // Verify company device count was updated
        $company->refresh();
        $this->assertEquals(1, $company->active_devices);
    }

    public function test_activation_with_company_number_instead_of_activation_key()
    {
        $company = Company::factory()->create([
            'name' => 'Test Company 2',
            'max_devices' => 2,
            'active_devices' => 0
        ]);

        $branch = Branch::factory()->create(['company_id' => $company->id]);
        $device = PosDevice::factory()->create([
            'branch_id' => $branch->id,
            'is_activated' => false
        ]);

        // Activate using company number instead of activation key
        $response = $this->postJson('/api/pos/activate-with-company', [
            'company_number' => $company->id,
            'device_fingerprint' => 'test-fingerprint-company-123',
            'mac_address' => 'AA:BB:CC:DD:EE:FF',
            'hardware_info' => ['os' => 'Linux'],
            'app_version' => '1.0.0'
        ]);

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => 'Device activated successfully with company'
                ]);

        // Verify device was activated and assigned to the correct company
        $activatedDevice = PosDevice::where('device_fingerprint', 'test-fingerprint-company-123')
                                   ->where('is_activated', true)
                                   ->first();
        
        $this->assertNotNull($activatedDevice);
        $this->assertEquals('AA:BB:CC:DD:EE:FF', $activatedDevice->mac_address);
        $this->assertEquals($company->id, $activatedDevice->branch->company_id);
    }

    public function test_mac_address_transfer_to_cloud_during_activation()
    {
        $company = Company::factory()->create(['max_devices' => 5]);
        $branch = Branch::factory()->create(['company_id' => $company->id]);
        $device = PosDevice::factory()->create([
            'branch_id' => $branch->id,
            'is_activated' => false
        ]);

        $response = $this->postJson('/api/pos/activate', [
            'activation_key' => $device->activation_key,
            'device_fingerprint' => 'cloud-fingerprint-123',
            'mac_address' => '11:22:33:44:55:66',
            'hardware_info' => ['os' => 'macOS'],
            'app_version' => '1.0.0',
            'company_id' => $company->id
        ]);

        $response->assertStatus(200);

        // Verify MAC address is stored in cloud (database)
        $activatedDevice = PosDevice::where('mac_address', '11:22:33:44:55:66')
                                   ->where('is_activated', true)
                                   ->first();
        
        $this->assertNotNull($activatedDevice);
        $this->assertEquals($company->id, $activatedDevice->branch->company_id);
        $this->assertEquals('11:22:33:44:55:66', $activatedDevice->mac_address);
    }

    public function test_reactivation_requires_master_admin_reset()
    {
        $company = Company::factory()->create(['max_devices' => 5]);
        $branch = Branch::factory()->create(['company_id' => $company->id]);
        
        // Create master admin user
        $masterAdmin = User::factory()->create([
            'role' => 'master_admin',
            'company_id' => $company->id
        ]);

        // Create and activate device
        $device = PosDevice::factory()->create([
            'branch_id' => $branch->id,
            'is_activated' => true,
            'mac_address' => '11:22:33:44:55:66',
            'device_fingerprint' => 'existing-fingerprint-123'
        ]);

        // Try to reactivate without reset - should fail
        $response = $this->postJson('/api/pos/activate', [
            'activation_key' => $device->activation_key,
            'device_fingerprint' => 'new-fingerprint-456',
            'mac_address' => '11:22:33:44:55:66',
            'hardware_info' => ['os' => 'Windows'],
            'app_version' => '1.0.0'
        ]);

        $response->assertStatus(409)
                ->assertJson([
                    'success' => false,
                    'message' => 'MAC address is already registered to another device'
                ]);

        // Reset MAC address as master admin
        $this->actingAs($masterAdmin);
        $resetResponse = $this->postJson('/api/pos/reset-mac', [
            'device_fingerprint' => 'existing-fingerprint-123',
            'admin_user_id' => $masterAdmin->id,
            'reason' => 'Device reactivation required'
        ]);

        $resetResponse->assertStatus(200)
                     ->assertJson([
                         'success' => true,
                         'message' => 'MAC address reset successfully'
                     ]);

        // Now reactivation should work
        $reactivateResponse = $this->postJson('/api/pos/activate', [
            'activation_key' => $device->activation_key,
            'device_fingerprint' => 'new-fingerprint-456',
            'mac_address' => '11:22:33:44:55:66',
            'hardware_info' => ['os' => 'Windows'],
            'app_version' => '1.0.0'
        ]);

        $reactivateResponse->assertStatus(200);
    }

    public function test_cross_company_activation_with_mac_address()
    {
        // Create two different companies
        $company1 = Company::factory()->create([
            'name' => 'Company A',
            'max_devices' => 2,
            'active_devices' => 0
        ]);

        $company2 = Company::factory()->create([
            'name' => 'Company B', 
            'max_devices' => 2,
            'active_devices' => 0
        ]);

        $branch1 = Branch::factory()->create(['company_id' => $company1->id]);
        $branch2 = Branch::factory()->create(['company_id' => $company2->id]);

        $device1 = PosDevice::factory()->create([
            'branch_id' => $branch1->id,
            'is_activated' => false
        ]);

        $device2 = PosDevice::factory()->create([
            'branch_id' => $branch2->id, 
            'is_activated' => false
        ]);

        // Activate device in company 1
        $response1 = $this->postJson('/api/pos/activate', [
            'activation_key' => $device1->activation_key,
            'device_fingerprint' => 'cross-company-fingerprint-1',
            'mac_address' => 'AA:BB:CC:DD:EE:FF',
            'hardware_info' => ['os' => 'Windows'],
            'app_version' => '1.0.0',
            'company_id' => $company1->id
        ]);

        $response1->assertStatus(200);

        // Try to activate same MAC address in company 2 - should fail
        $response2 = $this->postJson('/api/pos/activate', [
            'activation_key' => $device2->activation_key,
            'device_fingerprint' => 'cross-company-fingerprint-2',
            'mac_address' => 'AA:BB:CC:DD:EE:FF',
            'hardware_info' => ['os' => 'Linux'],
            'app_version' => '1.0.0',
            'company_id' => $company2->id
        ]);

        $response2->assertStatus(409)
                 ->assertJson([
                     'success' => false,
                     'message' => 'MAC address is already registered to another device'
                 ]);

        // Verify only company 1 has the device activated
        $company1->refresh();
        $company2->refresh();
        $this->assertEquals(1, $company1->active_devices);
        $this->assertEquals(0, $company2->active_devices);
    }

    public function test_mac_address_display_in_cloud_dashboard()
    {
        $company = Company::factory()->create(['max_devices' => 5]);
        $branch = Branch::factory()->create(['company_id' => $company->id]);
        
        // Create devices with MAC addresses
        $device1 = PosDevice::factory()->create([
            'branch_id' => $branch->id,
            'is_activated' => true,
            'mac_address' => '11:22:33:44:55:66',
            'device_name' => 'POS Device 1'
        ]);

        $device2 = PosDevice::factory()->create([
            'branch_id' => $branch->id,
            'is_activated' => true,
            'mac_address' => 'AA:BB:CC:DD:EE:FF',
            'device_name' => 'POS Device 2'
        ]);

        // Test API endpoint to get devices with MAC addresses
        $response = $this->getJson("/api/companies/{$company->id}/devices");

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        '*' => [
                            'id',
                            'device_name',
                            'mac_address',
                            'is_activated',
                            'activated_at',
                            'last_seen'
                        ]
                    ]
                ])
                ->assertJsonFragment(['mac_address' => '11:22:33:44:55:66'])
                ->assertJsonFragment(['mac_address' => 'AA:BB:CC:DD:EE:FF']);
    }

    public function test_reset_button_functionality()
    {
        $company = Company::factory()->create(['max_devices' => 5]);
        $branch = Branch::factory()->create(['company_id' => $company->id]);
        
        // Create master admin
        $masterAdmin = User::factory()->create([
            'role' => 'master_admin',
            'company_id' => $company->id
        ]);

        // Create activated device with MAC address
        $device = PosDevice::factory()->create([
            'branch_id' => $branch->id,
            'is_activated' => true,
            'mac_address' => '11:22:33:44:55:66',
            'device_fingerprint' => 'reset-test-fingerprint'
        ]);

        // Test reset functionality
        $this->actingAs($masterAdmin);
        $response = $this->postJson('/api/pos/reset-mac', [
            'device_fingerprint' => 'reset-test-fingerprint',
            'admin_user_id' => $masterAdmin->id,
            'reason' => 'Testing reset functionality'
        ]);

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => 'MAC address reset successfully'
                ]);

        // Verify MAC address was reset
        $device->refresh();
        $this->assertNull($device->mac_address);
        $this->assertNotNull($device->mac_address_reset_at);
        $this->assertEquals($masterAdmin->id, $device->mac_address_reset_by);
    }
}
