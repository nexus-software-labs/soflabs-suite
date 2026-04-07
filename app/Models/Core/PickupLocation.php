<?php

namespace App\Models\Core;

use App\Models\Printing\PrintOrder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PickupLocation extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'is_active' => 'boolean',
        'opening_hours' => 'array', // JSON
    ];

    /**
     * Relación: Una sucursal tiene muchos pedidos
     */
    public function printOrders(): HasMany
    {
        return $this->hasMany(PrintOrder::class);
    }

    /**
     * Scope: Solo sucursales activas
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Accessor: Nombre completo con dirección
     */
    public function getFullAddressAttribute(): string
    {
        return "{$this->name} - {$this->address}";
    }
}
