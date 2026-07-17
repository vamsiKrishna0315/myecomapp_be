<?php

declare(strict_types=1);

namespace App\Actions\Driver;

use App\Actions\BaseAction;
use App\Models\Driver;

final class UpdateDriverProfile extends BaseAction
{
    /**
     * Execute the action to update a driver's profile.
     */
    public function execute(array $data): Driver
    {
        $driver = $data['driver'];
        unset($data['driver']);

        $preparedData = $this->prepareData($data);

        $driver->update($preparedData);

        return $this->afterExecution($driver->fresh(), $data);
    }

    /**
     * Prepare data before updating the driver.
     */
    protected function prepareData(array $data): array
    {
        $updateData = [];

        if (isset($data['name'])) {
            $updateData['name'] = $data['name'];
        }

        if (array_key_exists('email', $data)) {
            $updateData['email'] = $data['email'];
        }

        if (isset($data['vehicle_type'])) {
            $updateData['vehicle_type'] = $data['vehicle_type'];
        }

        if (isset($data['vehicle_number'])) {
            $updateData['vehicle_number'] = $data['vehicle_number'];
        }

        return $updateData;
    }
}
