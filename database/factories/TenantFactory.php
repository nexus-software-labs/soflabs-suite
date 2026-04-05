<?php

namespace Database\Factories;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Tenant>
 */
class TenantFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id' => (string) Str::uuid(),
            'plan_id' => null,
            'db_mode' => 'shared',
            'is_active' => true,
            'trial_ends_at' => null,
            'subscribed_at' => null,
            'company_name' => fake()->optional()->company(),
            'phone' => null,
            'country' => null,
        ];
    }
}
