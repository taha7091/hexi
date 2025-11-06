<?php

namespace Tests\Feature;

use App\Models\PosDevice;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MacAddressSimpleTest extends TestCase
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
        $this->assertFalse(PosDevice::validate极速赛车开奖直播历史记录MacAddress('GG:HH:II:JJ:KK:LL'));
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
}
