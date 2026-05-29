<?php

declare(strict_types=1);

use App\Http\Middleware\RedirectBasedOnUserRole;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

test('redirects non-admin users to their role panel root', function () {
    Role::findOrCreate('store_admin');

    $user = User::factory()->create();
    $user->assignRole('store_admin');

    $this->actingAs($user);

    $request = Request::create('/admin', 'GET');

    $response = app(RedirectBasedOnUserRole::class)->handle($request, fn () => response('ok'));

    expect($response->isRedirection())->toBeTrue();
    expect(Str::endsWith((string) $response->headers->get('Location'), '/store_admin/dashboard'))->toBeTrue();
});

test('allows admin users to access the admin panel', function () {
    Role::findOrCreate('admin');

    $user = User::factory()->create();
    $user->assignRole('admin');

    $this->actingAs($user);

    $request = Request::create('/admin', 'GET');

    $response = app(RedirectBasedOnUserRole::class)->handle($request, fn () => response('ok'));

    expect($response->getStatusCode())->toBe(200);
});
