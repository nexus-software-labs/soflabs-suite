<?php

declare(strict_types=1);

namespace App\Traits;

use Illuminate\Database\Eloquent\Model;

/**
 * Asigna created_by / updated_by en create y update cuando hay usuario autenticado.
 */
trait RecordSignature
{
    public static function bootRecordSignature(): void
    {
        static::creating(function (Model $model): void {
            if ($model->getAttribute('created_by') === null && auth()->check()) {
                $model->setAttribute('created_by', auth()->id());
            }
        });

        static::updating(function (Model $model): void {
            if (auth()->check()) {
                $model->setAttribute('updated_by', auth()->id());
            }
        });
    }
}
