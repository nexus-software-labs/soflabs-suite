<?php

declare(strict_types=1);

namespace App\Models\Core;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Región geográfica (agrupa países). Tabla esperada: regions.
 * Stub mínimo: completar migraciones y campos según el negocio.
 */
class Region extends Model
{
    protected $guarded = ['id'];

    public function countries(): HasMany
    {
        return $this->hasMany(Country::class);
    }

    public function franchisee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'franchisee_id');
    }
}
