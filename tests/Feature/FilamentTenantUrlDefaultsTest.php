<?php

declare(strict_types=1);

use App\Models\Tenant;

afterEach(function (): void {
    if (tenancy()->initialized) {
        tenancy()->end();
    }
});

test('las rutas de recursos de inventario del panel app están definidas', function (): void {
    expect(Route::has('filament.app.resources.inventory.intake-documents.index'))->toBeTrue();
});

test('invitado en el dominio del panel app es redirigido al login sin UrlGenerationException', function (): void {
    $id = 'urlgen-tenant';
    $tenant = Tenant::withoutEvents(fn (): Tenant => Tenant::factory()->create(['id' => $id]));
    $tenant->domains()->create(['domain' => $id]);

    $dashboardUrl = sprintf('http://%s.%s/panel/dashboard', $id, config('app.domain'));
    $loginUrl = route('filament.app.auth.login', ['tenant' => $id]);

    $this->get($dashboardUrl)
        ->assertRedirect($loginUrl);
});
