<?php

declare(strict_types=1);

namespace App\Actions\Customer;

use App\Actions\BaseAction;
use App\Models\Customer;

final class UpdateCustomer extends BaseAction
{
    /**
     * Execute the action to update a customer.
     */
    public function execute(array $data): Customer
    {
        $customer = $data['customer'];
        unset($data['customer']);

        $preparedData = $this->prepareData($data);

        $customer->update($preparedData);

        return $this->afterExecution($customer->fresh(), $data);
    }

    /**
     * Prepare data before updating the customer.
     */
    protected function prepareData(array $data): array
    {
        $updateData = [];

        if (isset($data['first_name'])) {
            $updateData['first_name'] = $data['first_name'];
        }

        if (isset($data['last_name'])) {
            $updateData['last_name'] = $data['last_name'];
        }

        if (isset($data['mobile'])) {
            $updateData['mobile'] = $data['mobile'];
        }

        if (isset($data['dob'])) {
            $updateData['dob'] = $data['dob'];
        }

        return $updateData;
    }
}
