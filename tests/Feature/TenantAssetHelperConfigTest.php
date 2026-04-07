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

test('con tenancy inicializado asset() no usa la ruta stancl de tenancy assets', function (): void {
    config(['app.url' => 'http://myapp.test']);

    $tenant = Tenant::withoutEvents(fn (): Tenant => Tenant::factory()->create(['id' => 'asset-cfg-t']));
    $tenant->domains()->create(['domain' => 'asset-cfg-t']);

    tenancy()->initialize($tenant);

    $url = asset('build/test.css');

    expect($url)->not->toContain('tenancy/assets');
});
