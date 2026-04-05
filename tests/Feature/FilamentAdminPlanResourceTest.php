<?php

declare(strict_types=1);

use App\Filament\Admin\Resources\Plans\Pages\CreatePlan;
use App\Filament\Admin\Resources\Plans\Pages\ListPlans;
use App\Models\Plan;
use App\Models\User;
use Filament\Facades\Filament;
use Livewire\Livewire;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('crear plan desde Filament guarda módulos y límites', function (): void {
    $admin = User::factory()->superAdmin()->create();

    $this->actingAs($admin);

    Filament::setCurrentPanel(Filament::getPanel('admin'));

    Livewire::test(CreatePlan::class)
        ->fillForm([
            'name' => 'Plan Pro',
            'slug' => 'plan-pro',
            'description' => 'Descripción',
            'price_monthly' => 99.5,
            'price_yearly' => null,
            'is_active' => true,
            'modules' => ['inventario', 'logistica'],
            'limits' => ['max_branches' => '3', 'max_users' => '10'],
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $plan = Plan::query()->where('slug', 'plan-pro')->first();

    expect($plan)->not->toBeNull()
        ->and($plan->modules)->toBe(['inventario', 'logistica'])
        ->and($plan->limits)->toMatchArray([
            'max_branches' => '3',
            'max_users' => '10',
        ]);
});

test('duplicar plan desde la tabla crea copia con slug único', function (): void {
    $admin = User::factory()->superAdmin()->create();

    $this->actingAs($admin);

    Filament::setCurrentPanel(Filament::getPanel('admin'));

    $plan = Plan::factory()->create([
        'name' => 'Basico',
        'slug' => 'basico',
        'modules' => ['inventario'],
    ]);

    expect(Plan::query()->count())->toBe(1);

    Livewire::test(ListPlans::class)
        ->callTableAction('replicate', $plan);

    expect(Plan::query()->count())->toBe(2);

    $copy = Plan::query()->where('name', 'Basico (copia)')->first();

    expect($copy)->not->toBeNull()
        ->and($copy->slug)->toStartWith('basico-copia')
        ->and($copy->is($plan))->toBeFalse();
});
