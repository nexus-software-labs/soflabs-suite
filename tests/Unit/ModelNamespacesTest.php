<?php

declare(strict_types=1);
use App\Models\Branch;
use App\Models\Core\Customer;
use App\Models\Core\Promotion;
use App\Models\Core\Region;
use App\Models\Printing\PrintOrder;
use App\Models\Printing\PrintOrderHistory;

test('modelos core e impresión resuelven bajo App\\Models\\Core y App\\Models\\Printing', function () {
    expect(class_exists(Customer::class))->toBeTrue();
    expect(class_exists(Region::class))->toBeTrue();
    expect(class_exists(Branch::class))->toBeTrue();
    expect(class_exists(Promotion::class))->toBeTrue();
    expect(class_exists(PrintOrder::class))->toBeTrue();
    expect(class_exists(PrintOrderHistory::class))->toBeTrue();
});
