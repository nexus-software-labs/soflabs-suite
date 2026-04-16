<?php

declare(strict_types=1);

namespace Database\Factories\Inventory;

use App\Models\Inventory\Unit;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Unit>
 */
class UnitFactory extends Factory
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
            'name' => fake()->unique()->randomElement(['Unit', 'Box', 'Pack', 'Kilogram', 'Liter']),
            'abbreviation' => fake()->unique()->lexify('???'),
            'unit_type' => fake()->randomElement(['quantity', 'weight', 'volume']),
            'is_system' => false,
            'status' => 'active',
        ];
    }
}
