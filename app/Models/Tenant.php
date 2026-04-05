<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\Database\Concerns\HasDatabase;
use Stancl\Tenancy\Database\Concerns\HasDomains;
use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;

class Tenant extends BaseTenant implements TenantWithDatabase
{
    /** @use HasFactory<\Database\Factories\TenantFactory> */
    use HasDatabase;
    use HasDomains;
    use HasFactory;

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class, 'plan_id');
    }

    public function branches(): HasMany
    {
        return $this->hasMany(Branch::class, 'tenant_id');
    }

    public function modules(): HasMany
    {
        return $this->hasMany(TenantModule::class, 'tenant_id');
    }

    public function hasModule(string $module): bool
    {
        return $this->modules()
            ->where('module', $module)
            ->where('is_active', true)
            ->exists();
    }

    public static function getCustomColumns(): array
    {
        return [
            'id',
            'created_at',
            'updated_at',
            'company_name',
            'phone',
            'country',
            'plan_id',
            'db_mode',
            'is_active',
            'trial_ends_at',
            'subscribed_at',
        ];
    }
}
