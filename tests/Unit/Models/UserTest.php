<?php

declare(strict_types=1);

use App\Models\User;
use Filament\Panel;
use Spatie\Permission\Models\Role;

test('admin users can access the admin panel from their user role', function (): void {
    Role::findOrCreate('admin');

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

test('it syncs the selected user role after creating a user', function (): void {
    Role::findOrCreate('admin');

    $user = User::factory()->create([
        'user_role' => 'admin',
    ]);

    expect($user->fresh()->hasRole('admin'))->toBeTrue();
});

test('to array', function (): void {
    $user = User::factory()->create()->refresh();

    $payload = $user->toArray();

    expect($payload)
        ->toHaveKeys([
            'id',
            'name',
            'email',
            'email_verified_at',
            'created_at',
            'updated_at',
        ])
        ->not->toHaveKeys([
            'password',
            'remember_token',
        ]);
});
