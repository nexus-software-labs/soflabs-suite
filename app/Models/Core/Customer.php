<?php

declare(strict_types=1);

namespace App\Models\Core;

use App\Models\Branch;
use App\Models\Concerns\HasTenantScope;
use App\Models\User;
use App\Traits\RecordSignature;
use Database\Factories\CustomerFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

class Customer extends Model
{
    use HasTenantScope, Notifiable, RecordSignature, SoftDeletes;

    protected static function newFactory(): CustomerFactory
    {
        return static::applyTenantContextToFactory(CustomerFactory::new());
    }

    protected $guarded = ['id'];

    protected $casts = [
        'verified_at' => 'datetime',
        'phone_verified_at' => 'datetime',
    ];

    protected $appends = ['tier_name'];

    protected static function booted(): void
    {
        static::saving(function (Customer $customer): void {
            if (filled($customer->tenant_id) || ! $customer->user_id) {
                return;
            }

            $user = User::query()->find($customer->user_id);
            if ($user !== null && filled($user->tenant_id)) {
                $customer->tenant_id = $user->tenant_id;
            }
        });
    }

    public function getTierNameAttribute()
    {
        return $this->customerTier?->name;
    }

    /**
     * Relación con el usuario
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    /**
     * Relación con categoría de cliente
     */
    public function customerTier()
    {
        return $this->belongsTo(CustomerTier::class);
    }

    /**
     * Relación con historial de categorías
     */
    public function tierHistory()
    {
        return $this->hasMany(CustomerTierHistory::class)->orderBy('created_at', 'desc');
    }

    /**
     * Relación con las direcciones
     */
    public function addresses()
    {
        return $this->hasMany(CustomerAddress::class);
    }

    /**
     * Obtener la dirección por defecto
     */
    public function defaultAddress()
    {
        return $this->hasOne(CustomerAddress::class)->where('is_default', true);
    }

    /**
     * Relación con las prealertas
     */
    public function preAlertOrders()
    {
        return $this->hasMany(PreAlertOrder::class);
    }

    /**
     * Relación con cuentas de casillero
     */
    public function lockerAccounts()
    {
        return $this->hasMany(LockerAccount::class);
    }

    /**
     * Crear o actualizar customer a partir del usuario
     */
    public static function createOrUpdateFromUser($userId, $phone = null)
    {
        return static::updateOrCreate(
            ['user_id' => $userId],
            ['phone' => $phone]
        );
    }

    public function routeNotificationForMail()
    {
        return $this->email;
    }

    /**
     * Verificar si el cliente tiene una categoría específica
     */
    public function hasTier(?string $slug = null): bool
    {
        if (! $this->customerTier) {
            return false;
        }

        if ($slug) {
            return $this->customerTier->slug === $slug;
        }

        return true;
    }

    /**
     * Verificar si es VIP
     */
    public function isVip(): bool
    {
        return $this->hasTier('vip');
    }

    /**
     * Verificar si es Premium
     */
    public function isPremium(): bool
    {
        return $this->hasTier('premium');
    }

    /**
     * Obtener la categoría del cliente
     */
    public function getTier(): ?CustomerTier
    {
        return $this->customerTier;
    }

    /**
     * Cambiar la categoría del cliente y registrar en historial
     */
    public function changeTier(?int $tierId, ?string $reason = 'manual', ?string $notes = null, ?int $changedBy = null): void
    {
        $previousTierId = $this->customer_tier_id;

        $this->customer_tier_id = $tierId;
        $this->save();

        // Registrar en historial
        CustomerTierHistory::create([
            'customer_id' => $this->id,
            'customer_tier_id' => $tierId,
            'previous_tier_id' => $previousTierId,
            'change_reason' => $reason,
            'notes' => $notes,
            'changed_by' => $changedBy ?? auth()->id(),
        ]);
    }
}
