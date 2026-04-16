<?php

declare(strict_types=1);

namespace Database\Factories\Inventory;

use App\Models\Inventory\OutboundRequest;
use App\Models\Inventory\Warehouse;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OutboundRequest>
 */
class OutboundRequestFactory extends Factory
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
            'warehouse_id' => Warehouse::factory(),
            'request_number' => fake()->optional()->bothify('OUT-#####'),
            'requested_by_name' => fake()->name(),
            'destination' => fake()->optional()->city(),
            'status' => fake()->randomElement(['requested', 'reserved', 'dispatched', 'cancelled']),
            'created_by' => User::factory(),
            'processed_by' => null,
            'reserved_at' => null,
            'dispatched_at' => null,
            'cancelled_at' => null,
            'cancellation_reason' => null,
        ];
    }
}
