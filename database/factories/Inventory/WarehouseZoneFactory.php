<?php

declare(strict_types=1);

namespace Database\Factories\Inventory;

use App\Models\Inventory\Warehouse;
use App\Models\Inventory\WarehouseZone;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WarehouseZone>
 */
class WarehouseZoneFactory extends Factory
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
            'warehouse_id' => Warehouse::factory(),
            'name' => fake()->unique()->word(),
            'storage_condition' => fake()->randomElement(['ambient', 'refrigerated', 'frozen']),
            'description' => fake()->optional()->sentence(),
            'status' => 'active',
        ];
    }
}
