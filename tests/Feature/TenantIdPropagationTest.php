<?php

declare(strict_types=1);

use App\Models\Branch;
use App\Models\Printing\PrintOrder;
use App\Models\Tenant;

it('copia tenant_id de la sucursal al pedido de impresión', function () {
    $tenant = Tenant::factory()->create();
    $branch = Branch::factory()->create(['tenant_id' => $tenant->id]);

    $order = PrintOrder::factory()->create([
        'branch_id' => $branch->id,
        'tenant_id' => null,
    ]);

    expect($order->fresh()->tenant_id)->toBe($tenant->id);
});
