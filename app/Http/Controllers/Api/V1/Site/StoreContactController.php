<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Site;

use App\Http\Controllers\Api\V1\ResponseController;
use App\Models\StoreContactInfo;

final class StoreContactController extends ResponseController
{
    /**
     * Get active store contact information
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getStoreContact()
    {
        $storeContact = StoreContactInfo::where('status', 1)
            ->where('show_live', 1)
            ->with('store:id,name,address,city,state,country,pincode')
            ->first();

        if (! $storeContact) {
            return $this->sendError('Store contact information not found', 404);
        }

        $data = [
            'store_name' => $storeContact->store->name ?? null,
            'store_address' => $storeContact->store->address ?? null,
            'city' => $storeContact->store->city ?? null,
            'state' => $storeContact->store->state ?? null,
            'country' => $storeContact->store->country ?? null,
            'pincode' => $storeContact->store->pincode ?? null,
            'phone' => $storeContact->phone,
            'whatsapp_number' => $storeContact->whatsapp_number,
            'email' => $storeContact->email,
            'bulk_order_email' => $storeContact->bulk_order_email,
            'partnership_email' => $storeContact->partnership_email,
            'business_hours' => $storeContact->business_hours,
            'whatsapp_message' => $storeContact->whatsapp_message,
        ];

        return $this->returnResponse($data, 'Store contact information retrieved successfully');
    }
}
