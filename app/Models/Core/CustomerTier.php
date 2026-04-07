<?php

namespace App\Models\Core;

use App\Models\User;
use App\Traits\RecordSignature;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class CustomerTier extends Model
{
    use RecordSignature, SoftDeletes;

    protected $guarded = ['id'];

    protected $casts = [
        'is_active' => 'boolean',
        'priority' => 'integer',
    ];

    protected static function booted()
    {
        static::creating(function ($tier) {
            if (! $tier->slug && $tier->name) {
                $tier->slug = Str::slug($tier->name);
            }
        });
    }

    /**
     * Relación con clientes
     */
    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class);
    }

    /**
     * Relación con beneficios
     */
    public function benefits(): HasMany
    {
        return $this->hasMany(CustomerTierBenefit::class)->where('is_active', true)->orderBy('priority');
    }

    /**
     * Relación con todos los beneficios (incluyendo inactivos)
     */
    public function allBenefits(): HasMany
    {
        return $this->hasMany(CustomerTierBenefit::class)->orderBy('priority');
    }

    /**
     * Relación con promociones
     */
    public function promotions(): HasMany
    {
        return $this->hasMany(Promotion::class);
    }

    /**
     * Relación con historial
     */
    public function history(): HasMany
    {
        return $this->hasMany(CustomerTierHistory::class);
    }

    /**
     * Relación con usuario creador
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Relación con usuario actualizador
     */
    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Scope para categorías activas
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope para ordenar por prioridad
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('priority', 'desc')->orderBy('name');
    }

    /**
     * Verificar si es VIP
     */
    public function isVip(): bool
    {
        return $this->slug === 'vip';
    }

    /**
     * Verificar si es Premium
     */
    public function isPremium(): bool
    {
        return $this->slug === 'premium';
    }
}
