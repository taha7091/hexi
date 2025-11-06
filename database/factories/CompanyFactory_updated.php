<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Company>
 */
class CompanyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->company,
            'license_key' => $this->faker->uuid,
            'license_expiry' => $this->faker->dateTimeBetween('now', '+1 year'),
            'max_devices' => $this>faker->numberBetween(1, 10),
            'active_devices' => 0, // Start with 0 active devices
            'status' => 'active', // Default status
            'contact_email' => $this->faker->email,
            'contact_phone' => $this->faker->phoneNumber,
            'address' => $this->faker->address,
            'notes' => $this->faker->text,
            'allow_device_transfer' => true,
            'device_restrictions' => json_encode([]), // Empty restrictions
        ];
    }
}
