<?php

declare(strict_types=1);

namespace App\Actions\Customer;

use App\Actions\BaseAction;
use App\Models\Customer;
use Illuminate\Support\Facades\Hash;

final class CreateCustomer extends BaseAction
{
    /**
     * Execute the action to create a new customer.
     */
    public function execute(array $data): Customer
    {
        $preparedData = $this->prepareData($data);
        $customer = Customer::create($preparedData);

        return $this->afterExecution($customer, $data);
    }

    /**
     * Prepare data before creating the customer.
     */
    protected function prepareData(array $data): array
    {
        return [
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'email' => $data['email'],
            'mobile' => $data['mobile'],
            'password' => Hash::make($data['password']),
            'dob' => $data['dob'] ?? null,
            'status' => $data['status'] ?? 1, // Active by default
        ];
    }
}
