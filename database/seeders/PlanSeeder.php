<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Plan::query()->updateOrCreate(
            ['slug' => 'starter'],
            [
                'name' => 'Starter',
                'description' => 'Starter plan with inventory module.',
                'price_monthly' => '29.00',
                'price_yearly' => null,
                'is_active' => true,
                'modules' => ['inventory'],
                'limits' => [
                    'max_branches' => 1,
                    'max_users' => 5,
                ],
            ],
        );

        Plan::query()->updateOrCreate(
            ['slug' => 'professional'],
            [
                'name' => 'Professional',
                'description' => 'Inventory and packages modules.',
                'price_monthly' => '79.00',
                'price_yearly' => null,
                'is_active' => true,
                'modules' => ['inventory', 'packages'],
                'limits' => [
                    'max_branches' => 5,
                    'max_users' => 20,
                ],
            ],
        );

        Plan::query()->updateOrCreate(
            ['slug' => 'enterprise'],
            [
                'name' => 'Enterprise',
                'description' => 'All modules, expanded limits.',
                'price_monthly' => '199.00',
                'price_yearly' => null,
                'is_active' => true,
                'modules' => ['inventory', 'packages', 'printing'],
                'limits' => [
                    'max_branches' => null,
                    'max_users' => null,
                ],
            ],
        );
    }
}
