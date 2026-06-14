<?php

declare(strict_types=1);

use App\Models\User;
use Filament\Panel;
use Spatie\Permission\Models\Role;

test('admin users can access the admin panel from their user role', function (): void {
    $panel = Mockery::mock(Panel::class);
    $panel->shouldReceive('getId')->andReturn('admin');

    $user = User::factory()->create([
        'user_role' => 'admin',
    ]);

    expect($user->canAccessPanel($panel))->toBeTrue();
});

test('users can access the panel that matches their spatie role', function (): void {
    Role::findOrCreate('store_admin');

    $panel = Mockery::mock(Panel::class);
    $panel->shouldReceive('getId')->andReturn('store_admin');

    $user = User::factory()->create();
    $user->assignRole('store_admin');

    expect($user->canAccessPanel($panel))->toBeTrue();
});

test('to array', function (): void {
    $user = User::factory()->create()->refresh();

    expect(array_keys($user->toArray()))
        ->toBe([
            'id',
            'name',
            'email',
            'email_verified_at',
            'created_at',
            'updated_at',
        ]);
});
