<?php

declare(strict_types=1);

namespace App\Models\Core;

use App\Models\Concerns\HasTenantScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class PromotionApplication extends Model
{
    use HasTenantScope;

    protected $guarded = ['id'];

    protected $casts = [
        'original_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'applied_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::saving(function (PromotionApplication $application): void {
            if (filled($application->tenant_id) || ! $application->promotion_id) {
                return;
            }

            $promotion = Promotion::query()->find($application->promotion_id);
            if ($promotion !== null && filled($promotion->tenant_id)) {
                $application->tenant_id = $promotion->tenant_id;
            }
        });
    }

    public function promotion(): BelongsTo
    {
        return $this->belongsTo(Promotion::class);
    }

    public function applicable(): MorphTo
    {
        return $this->morphTo();
    }
}
