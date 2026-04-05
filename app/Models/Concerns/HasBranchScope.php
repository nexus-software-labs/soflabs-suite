<?php

declare(strict_types=1);

namespace App\Models\Concerns;

use App\Models\Scopes\BranchScope;
use App\Services\TenantContext;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Aplica {@see TenantScope} y {@see BranchScope}. Incluye {@see HasTenantScope};
 * no combines manualmente con {@see HasTenantScope} en el mismo modelo.
 */
trait HasBranchScope
{
    use HasTenantScope {
        newFactory as tenantScopedNewFactory;
    }

    protected static function bootHasBranchScope(): void
    {
        static::addGlobalScope(new BranchScope());
    }

    protected static function newFactory()
    {
        $factory = static::tenantScopedNewFactory();

        return static::applyBranchContextToFactory($factory);
    }

    /**
     * @template T
     *
     * @param  callable(): T  $callback
     * @return T
     */
    public static function factoryWithoutBranchScope(callable $callback): mixed
    {
        return BranchScope::withoutBranchScope($callback);
    }

    protected static function applyBranchContextToFactory(Factory $factory): Factory
    {
        return $factory->state(function () {
            $branchId = app(TenantContext::class)->getBranchId();

            if ($branchId === null) {
                return [];
            }

            return ['branch_id' => $branchId];
        });
    }
}
