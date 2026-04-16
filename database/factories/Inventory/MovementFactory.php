<?php

declare(strict_types=1);

namespace Database\Factories\Inventory;

use App\Models\Inventory\Movement;
use App\Models\Inventory\Product;
use App\Models\Inventory\Warehouse;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Movement>
 */
class MovementFactory extends Factory
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
            'product_id' => Product::factory(),
            'warehouse_id' => Warehouse::factory(),
            'movement_type' => fake()->randomElement(['inbound', 'outbound', 'adjustment_increase', 'adjustment_decrease']),
            'quantity' => fake()->randomFloat(2, 1, 500),
            'stock_before' => fake()->randomFloat(2, 0, 500),
            'stock_after' => fake()->randomFloat(2, 0, 500),
            'reference_type' => fake()->optional()->word(),
            'reference_id' => fake()->optional()->bothify('REF-####'),
            'notes' => fake()->optional()->sentence(),
            'performed_by' => User::factory(),
            'moved_at' => now(),
        ];
    }
}
