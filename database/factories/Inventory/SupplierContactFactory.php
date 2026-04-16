<?php

declare(strict_types=1);

namespace Database\Factories\Inventory;

use App\Models\Inventory\Supplier;
use App\Models\Inventory\SupplierContact;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SupplierContact>
 */
class SupplierContactFactory extends Factory
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
            'name' => fake()->name(),
            'job_title' => fake()->optional()->jobTitle(),
            'email' => fake()->optional()->safeEmail(),
            'phone' => fake()->optional()->phoneNumber(),
            'contact_type' => fake()->randomElement(['general', 'billing', 'sales']),
            'is_primary' => false,
            'status' => 'active',
        ];
    }
}
