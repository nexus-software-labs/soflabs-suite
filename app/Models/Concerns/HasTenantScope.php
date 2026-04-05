<?php

declare(strict_types=1);

namespace App\Models\Concerns;

use App\Models\Scopes\TenantScope;
use App\Services\TenantContext;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;

trait HasTenantScope
{
    use HasFactory {
        newFactory as laravelNewFactory;
    }

    protected static function bootHasTenantScope(): void
    {
        static::addGlobalScope(new TenantScope());
    }

    protected static function newFactory()
    {
        $factory = static::laravelNewFactory() ?? Factory::factoryForModel(static::class);

        return static::applyTenantContextToFactory($factory);
    }

    /**
     * @template T
     *
     * @param  callable(): T  $callback
     * @return T
     */
    public static function factoryWithoutTenantScope(callable $callback): mixed
    {
        return TenantScope::withoutTenantScope($callback);
    }

    protected static function applyTenantContextToFactory(Factory $factory): Factory
    {
        return $factory->state(function () {
            $context = app(TenantContext::class);

            if (! $context->hasTenant()) {
                return [];
            }

            return ['tenant_id' => $context->getTenantId()];
        });
    }
}
