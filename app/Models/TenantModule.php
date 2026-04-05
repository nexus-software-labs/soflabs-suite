<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\HasTenantScope;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;

class TenantModule extends Model
{
    /** @use \Illuminate\Database\Eloquent\Factories\HasFactory<\Database\Factories\TenantModuleFactory> */
    use HasTenantScope, HasUlids;

    protected $fillable = [
        'tenant_id',
        'module',
        'is_active',
        'activated_at',
        'expires_at',
        'settings',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'settings' => 'array',
            'is_active' => 'boolean',
            'activated_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }
}
