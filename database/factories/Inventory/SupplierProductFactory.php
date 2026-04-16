<?php

declare(strict_types=1);

namespace Database\Factories\Inventory;

use App\Models\Inventory\Product;
use App\Models\Inventory\Supplier;
use App\Models\Inventory\SupplierProduct;
use App\Models\Inventory\Unit;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SupplierProduct>
 */
class SupplierProductFactory extends Factory
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
            'supplier_id' => Supplier::factory(),
            'product_id' => Product::factory(),
            'price' => fake()->optional()->randomFloat(2, 1, 1000),
            'unit_id' => Unit::factory(),
            'supplier_sku' => fake()->optional()->bothify('SUP-####'),
            'status' => 'active',
        ];
    }
}
