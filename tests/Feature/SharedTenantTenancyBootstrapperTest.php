<?php

declare(strict_types=1);

use App\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

afterEach(function (): void {
    if (tenancy()->initialized) {
        tenancy()->end();
    }
});

test('shared db_mode no cambia la conexión por defecto a una BD tenant', function (): void {
    $tenant = Tenant::withoutEvents(fn (): Tenant => Tenant::factory()->create([
        'id' => 'shared-tenancy-test',
        'db_mode' => 'shared',
    ]));
    $tenant->domains()->create(['domain' => 'shared-tenancy-test']);

    $defaultBefore = config('database.default');

    tenancy()->initialize($tenant);

    expect(config('database.default'))->toBe($defaultBefore);
});
