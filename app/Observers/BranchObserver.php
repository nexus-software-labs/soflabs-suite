<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Branch;
use Illuminate\Database\Eloquent\Builder;

final class BranchObserver
{
    /**
     * Garantiza una sola sucursal principal por inquilino.
     */
    public function saving(Branch $branch): void
    {
        if (! $branch->is_main) {
            return;
        }

        if (! filled($branch->tenant_id)) {
            return;
        }

        Branch::query()
            ->where('tenant_id', $branch->tenant_id)
            ->when(
                $branch->exists,
                fn (Builder $query): Builder => $query->whereKeyNot($branch->getKey()),
            )
            ->update(['is_main' => false]);
    }
}
