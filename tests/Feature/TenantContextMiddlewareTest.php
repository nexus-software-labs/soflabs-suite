<?php

use App\Http\Middleware\CheckModuleAccess;
use App\Http\Middleware\InjectTenantContext;
use App\Models\User;
use App\Services\TenantContext;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

test('inject tenant context assigns the authenticated user', function () {
    $user = User::factory()->create();
    $request = Request::create('/');
    $request->setUserResolver(fn () => $user);

    app(InjectTenantContext::class)->handle($request, fn () => response('ok'));

    expect(app(TenantContext::class)->user)->toBe($user);
});

test('check module access aborts with 403 when tenancy is not initialized', function () {
    try {
        app(CheckModuleAccess::class)->handle(
            Request::create('/'),
            fn () => response('ok'),
            'inventory',
        );
        $this->fail('Expected HttpException');
    } catch (HttpException $e) {
        expect($e->getStatusCode())->toBe(403)
            ->and($e->getMessage())->toBe('Módulo no disponible en tu plan.');
    }
});
