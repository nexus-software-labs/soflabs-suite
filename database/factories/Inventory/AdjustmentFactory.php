<?php

declare(strict_types=1);

namespace Database\Factories\Inventory;

use App\Models\Inventory\Adjustment;
use App\Models\Inventory\Movement;
use App\Models\Inventory\Product;
use App\Models\Inventory\Warehouse;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Adjustment>
 */
class AdjustmentFactory extends Factory
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
            'movement_id' => Movement::factory(),
            'product_id' => Product::factory(),
            'warehouse_id' => Warehouse::factory(),
            'adjustment_type' => fake()->randomElement(['positive', 'negative']),
            'difference_quantity' => fake()->randomFloat(2, 1, 200),
            'reason' => fake()->randomElement(['count_error', 'damage', 'loss', 'donation']),
            'evidence_path' => fake()->optional()->filePath(),
            'notes' => fake()->optional()->sentence(),
            'performed_by' => User::factory(),
            'adjusted_at' => now(),
        ];
    }
}
