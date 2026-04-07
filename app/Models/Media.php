<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Scopes\TenantScope;
use Spatie\MediaLibrary\MediaCollections\Models\Media as BaseMedia;

/**
 * Media con {@see TenantScope} y copia de {@see tenant_id} desde el modelo relacionado.
 */
class Media extends BaseMedia
{
    protected static function booted(): void
    {
        static::addGlobalScope(new TenantScope);

        static::creating(function (Media $media): void {
            if (filled($media->tenant_id)) {
                return;
            }

            $related = $media->model;
            if ($related !== null && array_key_exists('tenant_id', $related->getAttributes())) {
                $tid = $related->getAttribute('tenant_id');
                if (filled($tid)) {
                    $media->tenant_id = $tid;

                    return;
                }
            }

            if (function_exists('tenancy') && tenancy()->initialized && tenant()) {
                $media->tenant_id = tenant('id');
            }
        });
    }
}
