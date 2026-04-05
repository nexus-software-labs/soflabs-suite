<?php

namespace Database\Factories;

use App\Models\Tenant;
use App\Models\TenantModule;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TenantModule>
 */
class TenantModuleFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'module' => fake()->randomElement(['inventory', 'packages', 'printing']),
            'is_active' => true,
            'activated_at' => now(),
            'expires_at' => null,
            'settings' => null,
        ];
    }
}
