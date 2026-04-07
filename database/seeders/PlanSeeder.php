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
                'price_yearly' => '290.00',
                'price' => '29.00',
                'signup_fee' => '0.00',
                'currency' => 'USD',
                'trial_period' => 0,
                'trial_interval' => 'day',
                'invoice_period' => 1,
                'invoice_interval' => 'month',
                'grace_period' => 7,
                'grace_interval' => 'day',
                'is_active' => true,
                'modules' => ['inventory'],
                'features' => [],
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
                'price_yearly' => '790.00',
                'price' => '79.00',
                'signup_fee' => '0.00',
                'currency' => 'USD',
                'trial_period' => 0,
                'trial_interval' => 'day',
                'invoice_period' => 1,
                'invoice_interval' => 'month',
                'grace_period' => 7,
                'grace_interval' => 'day',
                'is_active' => true,
                'modules' => ['inventory', 'packages'],
                'features' => [],
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
                'price_yearly' => '1990.00',
                'price' => '199.00',
                'signup_fee' => '0.00',
                'currency' => 'USD',
                'trial_period' => 0,
                'trial_interval' => 'day',
                'invoice_period' => 1,
                'invoice_interval' => 'month',
                'grace_period' => 7,
                'grace_interval' => 'day',
                'is_active' => true,
                'modules' => ['inventory', 'packages', 'printing'],
                'features' => [],
                'limits' => [
                    'max_branches' => null,
                    'max_users' => null,
                ],
            ],
        );
    }
}
