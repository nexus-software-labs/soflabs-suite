<?php

namespace Database\Factories;

use App\Models\Plan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Plan>
 */
class PlanFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->words(3, true);

        return [
            'name' => $name,
            'slug' => fake()->unique()->slug(),
            'description' => fake()->optional()->paragraph(),
            'price_monthly' => fake()->randomFloat(2, 10, 500),
            'price_yearly' => fake()->optional()->randomFloat(2, 100, 5000),
            'is_active' => true,
            'modules' => ['inventory', 'packages'],
            'limits' => [
                'max_branches' => 5,
                'max_users' => 10,
            ],
        ];
    }
}
