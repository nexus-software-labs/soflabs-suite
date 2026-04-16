<?php

declare(strict_types=1);

namespace Database\Factories\Inventory;

use App\Models\Inventory\Warehouse;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Warehouse>
 */
class WarehouseFactory extends Factory
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
            'name' => fake()->unique()->words(2, true),
            'warehouse_type' => fake()->randomElement(['main', 'transit', 'returns']),
            'location' => fake()->optional()->address(),
            'responsible_user_id' => User::factory(),
            'status' => 'active',
        ];
    }
}
