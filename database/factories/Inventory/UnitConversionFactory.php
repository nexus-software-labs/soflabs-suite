<?php

declare(strict_types=1);

namespace Database\Factories\Inventory;

use App\Models\Inventory\Unit;
use App\Models\Inventory\UnitConversion;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<UnitConversion>
 */
class UnitConversionFactory extends Factory
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
            'from_unit_id' => Unit::factory(),
            'to_unit_id' => Unit::factory(),
            'factor' => fake()->randomFloat(4, 0.1, 9999),
        ];
    }
}
