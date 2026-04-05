<?php

declare(strict_types=1);

namespace App\Models\Scopes;

use App\Services\TenantContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class BranchScope implements Scope
{
    private static int $ignoreDepth = 0;

    /**
     * @template T
     *
     * @param  callable(): T  $callback
     * @return T
     */
    public static function withoutBranchScope(callable $callback): mixed
    {
        ++self::$ignoreDepth;
        try {
            return $callback();
        } finally {
            --self::$ignoreDepth;
        }
    }

    public function apply(Builder $builder, Model $model): void
    {
        if (self::$ignoreDepth > 0) {
            return;
        }

        $branchId = app(TenantContext::class)->getBranchId();

        if ($branchId === null) {
            return;
        }

        $builder->where($model->getTable().'.branch_id', $branchId);
    }
}
