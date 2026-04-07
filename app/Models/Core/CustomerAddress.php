<?php

declare(strict_types=1);

namespace App\Models\Core;

use App\Models\Concerns\HasTenantScope;
use App\Traits\RecordSignature;
use Database\Factories\CustomerAddressFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CustomerAddress extends Model
{
    use HasTenantScope, RecordSignature, SoftDeletes;

    protected static function newFactory(): CustomerAddressFactory
    {
        return static::applyTenantContextToFactory(CustomerAddressFactory::new());
    }

    protected $guarded = ['id'];

    protected $casts = [
        'is_default' => 'boolean',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
    ];

    protected static function booted(): void
    {
        static::saving(function (CustomerAddress $address): void {
            if (filled($address->tenant_id) || ! $address->customer_id) {
                return;
            }

            $customer = Customer::withoutGlobalScopes()->find($address->customer_id);
            if ($customer !== null && filled($customer->tenant_id)) {
                $address->tenant_id = $customer->tenant_id;
            }
        });
    }

    /**
     * Relación con el customer
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Relación con las prealertas que usaron esta dirección
     */
    public function prealertas()
    {
        return $this->hasMany(PreAlertOrder::class);
    }

    /**
     * Obtener la dirección completa formateada
     */
    public function getFullAddressAttribute()
    {
        $parts = array_filter([
            $this->address,
            $this->municipality,
            $this->department,
            $this->state,
        ]);

        return implode(', ', $parts);
    }

    /**
     * Verificar si tiene coordenadas
     */
    public function hasCoordinates()
    {
        return ! is_null($this->latitude) && ! is_null($this->longitude);
    }

    /**
     * Marcar como dirección por defecto
     * (y desmarcar las demás del mismo customer)
     */
    public function setAsDefault()
    {
        // Desmarcar todas las direcciones del customer
        static::where('customer_id', $this->customer_id)
            ->where('id', '!=', $this->id)
            ->update(['is_default' => false]);

        // Marcar esta como default
        $this->update(['is_default' => true]);
    }

    /**
     * Scope para obtener solo direcciones por defecto
     */
    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    /**
     * Scope para filtrar por departamento
     */
    public function scopeByDepartment($query, $department)
    {
        return $query->where('department', $department);
    }

    /**
     * Boot del modelo
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($address) {
            if ($address->is_default) {
                static::where('customer_id', $address->customer_id)
                    ->update(['is_default' => false]);
            }
        });

        static::updating(function ($address) {
            if ($address->is_default && $address->isDirty('is_default')) {
                static::where('customer_id', $address->customer_id)
                    ->where('id', '!=', $address->id)
                    ->update(['is_default' => false]);
            }
        });
    }
}
