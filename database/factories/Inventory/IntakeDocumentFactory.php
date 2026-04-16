<?php

declare(strict_types=1);

namespace Database\Factories\Inventory;

use App\Models\Inventory\IntakeDocument;
use App\Models\Inventory\Supplier;
use App\Models\Inventory\Warehouse;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<IntakeDocument>
 */
class IntakeDocumentFactory extends Factory
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
            'warehouse_id' => Warehouse::factory(),
            'document_number' => fake()->optional()->bothify('DOC-#####'),
            'document_date' => fake()->optional()->date(),
            'currency_code' => fake()->randomElement(['USD', 'EUR', 'GTQ']),
            'subtotal' => fake()->optional()->randomFloat(2, 1, 5000),
            'tax' => fake()->optional()->randomFloat(2, 0, 800),
            'total' => fake()->optional()->randomFloat(2, 1, 5800),
            'status' => fake()->randomElement(['received', 'processing', 'review', 'approved', 'rejected']),
            'origin' => fake()->randomElement(['manual', 'ai']),
            'source_file_path' => fake()->optional()->filePath(),
            'ai_confidence' => fake()->optional()->randomFloat(4, 0.5, 0.99),
            'warnings' => null,
            'raw_extraction' => null,
            'created_by' => User::factory(),
            'approved_by' => null,
            'processed_at' => null,
            'approved_at' => null,
            'rejected_at' => null,
            'rejection_reason' => null,
        ];
    }
}
