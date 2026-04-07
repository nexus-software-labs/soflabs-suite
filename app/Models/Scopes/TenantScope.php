<?php

declare(strict_types=1);

namespace App\Models\Scopes;

use App\Services\TenantContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class TenantScope implements Scope
{
    private static int $ignoreDepth = 0;

    /**
     * @template T
     *
     * @param  callable(): T  $callback
     * @return T
     */
    public static function withoutTenantScope(callable $callback): mixed
    {
        self::$ignoreDepth++;
        try {
            return $callback();
        } finally {
            self::$ignoreDepth--;
        }
    }

    public function apply(Builder $builder, Model $model): void
    {
        if (self::$ignoreDepth > 0) {
            return;
        }

        $context = app(TenantContext::class);

        if (! $context->hasTenant()) {
            return;
        }

        $builder->where($model->getTable().'.tenant_id', $context->getTenantId());
    }
}
