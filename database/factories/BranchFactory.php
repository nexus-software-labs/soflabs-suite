<?php

namespace Database\Factories;

use App\Models\Branch;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Branch>
 */
class BranchFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'name' => fake()->company(),
            'code' => fake()->optional()->bothify('???##'),
            'address' => fake()->optional()->streetAddress(),
            'city' => fake()->optional()->city(),
            'country' => fake()->optional()->countryCode(),
            'phone' => fake()->optional()->phoneNumber(),
            'email' => fake()->optional()->companyEmail(),
            'is_main' => false,
            'is_active' => true,
            'settings' => null,
        ];
    }
}
