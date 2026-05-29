<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Api\V1\StoreCustomerAddressRequest;
use App\Http\Requests\Api\V1\UpdateCustomerAddressRequest;
use App\Models\Address;
use Illuminate\Http\Request;

final class CustomerAddressController extends ResponseController
{
    /**
     * Get customer addresses
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $customer = auth('customer-api')->user();

        $addresses = $customer->addresses()
            ->where('status', 1)
            ->get();

        $formattedAddresses = format_address_collection($addresses, $request->boolean('grouped'));
        $data = [
            'customer_id' => $customer->id,
            'total_addresses' => $addresses->count(),
            'addresses' => $formattedAddresses,
        ];

        return $this->returnResponse($data, 'Addresses retrieved successfully', 200);
    }

    /**
     * Store a new customer address.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreCustomerAddressRequest $request)
    {
        $customer = auth('customer-api')->user();
        $validated = $request->validated();

        if (isset($validated['customer_id']) && $validated['customer_id'] !== $customer->id) {
            return $this->sendError('Unauthorized access', 403);
        }

        unset($validated['customer_id']);
        $validated['customer_id'] = $customer->id;

        $setAsDefault = array_key_exists('is_default', $validated) && $validated['is_default'] === true;
        if (! isset($validated['status'])) {
            $validated['status'] = 1;
        }

        $address = Address::create($validated);

        if ($setAsDefault) {
            $address->setAsDefault();
        }

        return $this->returnResponse($address->fresh(), 'Address created successfully', 201);
    }

    /**
     * Update a customer address.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(UpdateCustomerAddressRequest $request, Address $address)
    {
        $customer = auth('customer-api')->user();

        if ($address->customer_id !== $customer->id) {
            return $this->sendError('Address not found.', 404);
        }

        $validated = $request->validated();
        $setAsDefault = array_key_exists('is_default', $validated) && $validated['is_default'] === true;

        if ($setAsDefault) {
            unset($validated['is_default']);
        }

        $address->update($validated);

        if ($setAsDefault) {
            $address->setAsDefault();
        }

        return $this->returnResponse($address->fresh(), 'Address updated successfully', 200);
    }
}
