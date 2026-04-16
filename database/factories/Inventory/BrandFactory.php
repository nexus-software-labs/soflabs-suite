<?php

declare(strict_types=1);

namespace Database\Factories\Inventory;

use App\Models\Inventory\Brand;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Brand>
 */
class BrandFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'name' => fake()->unique()->company(),
            'description' => fake()->optional()->sentence(),
            'status' => 'active',
        ];
    }
}
