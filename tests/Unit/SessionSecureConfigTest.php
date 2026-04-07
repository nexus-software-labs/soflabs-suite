<?php

declare(strict_types=1);

use Tests\TestCase;

uses(TestCase::class);

test('session cookies are not forced secure in testing env', function () {
    expect(config('app.env'))->toBe('testing')
        ->and(config('session.secure'))->toBeFalse();
});
