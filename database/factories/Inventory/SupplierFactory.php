<?php

declare(strict_types=1);

namespace Database\Factories\Inventory;

use App\Models\Inventory\Supplier;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Supplier>
 */
class SupplierFactory extends Factory
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
            'tax_id' => fake()->optional()->bothify('TAX-#####'),
            'supplier_type' => fake()->randomElement(['manufacturer', 'distributor', 'importer', 'service']),
            'country_code' => fake()->optional()->countryCode(),
            'payment_terms' => fake()->randomElement(['cash', 'net_30', 'net_60']),
            'status' => 'active',
        ];
    }
}
