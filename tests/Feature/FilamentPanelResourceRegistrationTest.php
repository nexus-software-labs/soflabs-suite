<?php

declare(strict_types=1);

use App\Filament\Admin\Resources\Branches\BranchResource;
use App\Filament\Admin\Resources\Countries\CountryResource;
use App\Filament\Admin\Resources\Customers\CustomerResource;
use App\Filament\Admin\Resources\Payments\PaymentResource;
use App\Filament\Admin\Resources\Plans\PlanResource;
use App\Filament\Admin\Resources\PrintOrders\PrintOrderResource;
use App\Filament\Admin\Resources\Promotions\PromotionResource;
use App\Filament\Admin\Resources\Tenants\TenantResource;
use App\Filament\Admin\Resources\Users\UserResource;
use Filament\Facades\Filament;

test('el panel admin solo registra recursos de plataforma', function (): void {
    $resources = Filament::getPanel('admin')->getResources();

    expect($resources)->toContain(PlanResource::class)
        ->and($resources)->toContain(TenantResource::class)
        ->and($resources)->toContain(CountryResource::class)
        ->and($resources)->toContain(UserResource::class)
        ->and($resources)->not->toContain(BranchResource::class)
        ->and($resources)->not->toContain(CustomerResource::class)
        ->and($resources)->not->toContain(PrintOrderResource::class)
        ->and($resources)->not->toContain(PaymentResource::class)
        ->and($resources)->not->toContain(PromotionResource::class);
});

test('el panel app registra recursos operativos del inquilino', function (): void {
    $resources = Filament::getPanel('app')->getResources();

    expect($resources)->toContain(UserResource::class)
        ->and($resources)->toContain(BranchResource::class)
        ->and($resources)->toContain(CustomerResource::class)
        ->and($resources)->toContain(PrintOrderResource::class)
        ->and($resources)->toContain(PaymentResource::class)
        ->and($resources)->toContain(PromotionResource::class)
        ->and($resources)->not->toContain(PlanResource::class)
        ->and($resources)->not->toContain(TenantResource::class);
});
