<?php

declare(strict_types=1);

use App\Actions\Customer\CreateCustomer;
use App\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('stores a customer dob from the past', function (): void {
    $customer = (new CreateCustomer())->execute([
        'first_name' => 'DOB',
        'last_name' => 'Regression',
        'email' => 'dob-regression@example.com',
        'mobile' => '9000000000',
        'password' => 'password',
        'dob' => '1994-05-17',
        'status' => 1,
    ]);

    expect($customer->dob?->toDateString())->toBe('1994-05-17');

    expect(Customer::query()
        ->whereKey($customer->id)
        ->whereDate('dob', '1994-05-17')
        ->exists())->toBeTrue();
});
