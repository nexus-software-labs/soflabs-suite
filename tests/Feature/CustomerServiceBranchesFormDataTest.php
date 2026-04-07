<?php

declare(strict_types=1);

use App\Services\CustomerService;

it('expone branches y suggestedBranchId sin claves legacy de stores', function () {
    $data = app(CustomerService::class)->getFormData(null);

    expect($data)->toHaveKey('branches')
        ->not->toHaveKey('stores')
        ->toHaveKey('suggestedBranchId')
        ->not->toHaveKey('suggestedStoreId');
});
