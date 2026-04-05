<?php

declare(strict_types=1);

use App\Models\Branch;
use App\Models\Tenant;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('al marcar is_main en una sucursal se desmarca en las demás del mismo tenant', function (): void {
    $tenant = Tenant::withoutEvents(fn () => Tenant::factory()->create(['id' => 'br-main-t']));

    $a = Branch::factory()->create(['tenant_id' => $tenant->id, 'is_main' => true]);
    $b = Branch::factory()->create(['tenant_id' => $tenant->id, 'is_main' => false]);

    expect($a->fresh()->is_main)->toBeTrue()
        ->and($b->fresh()->is_main)->toBeFalse();

    $b->update(['is_main' => true]);

    expect($a->fresh()->is_main)->toBeFalse()
        ->and($b->fresh()->is_main)->toBeTrue();
});

test('crear sucursal principal desmarca la anterior', function (): void {
    $tenant = Tenant::withoutEvents(fn () => Tenant::factory()->create(['id' => 'br-main-t2']));

    $a = Branch::factory()->create(['tenant_id' => $tenant->id, 'is_main' => true]);

    $c = Branch::factory()->create(['tenant_id' => $tenant->id, 'is_main' => true]);

    expect($a->fresh()->is_main)->toBeFalse()
        ->and($c->fresh()->is_main)->toBeTrue();
});

test('otro tenant no se ve afectado al fijar principal', function (): void {
    $t1 = Tenant::withoutEvents(fn () => Tenant::factory()->create(['id' => 'br-t1']));
    $t2 = Tenant::withoutEvents(fn () => Tenant::factory()->create(['id' => 'br-t2']));

    Branch::factory()->create(['tenant_id' => $t1->id, 'is_main' => true]);
    $b2 = Branch::factory()->create(['tenant_id' => $t2->id, 'is_main' => true]);

    Branch::factory()->create(['tenant_id' => $t1->id, 'is_main' => true]);

    expect($b2->fresh()->is_main)->toBeTrue();
});
