<?php

declare(strict_types=1);

namespace Database\Factories\Inventory;

use App\Models\Inventory\Brand;
use App\Models\Inventory\Group;
use App\Models\Inventory\Product;
use App\Models\Inventory\Unit;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
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
            'group_id' => Group::factory(),
            'brand_id' => Brand::factory(),
            'sku' => fake()->unique()->bothify('SKU-####'),
            'name' => fake()->words(3, true),
            'purchase_unit_id' => Unit::factory(),
            'stock_unit_id' => Unit::factory(),
            'sales_unit_id' => Unit::factory(),
            'valuation_method' => fake()->randomElement(['fifo', 'lifo', 'weighted_average']),
            'minimum_stock' => fake()->randomFloat(2, 0, 1000),
            'status' => 'active',
            'embedding' => null,
        ];
    }
}
