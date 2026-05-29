<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Site;

use App\Http\Controllers\Api\V1\ResponseController;
use App\Models\ContactInquiry;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

final class ContactInquiryController extends ResponseController
{
    /**
     * Store a new contact inquiry (Customer or Guest)
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        // Validate the request
        $validator = Validator::make($request->all(), [
            'customer_id' => 'nullable|exists:customers,id',
            'name' => 'required_without:customer_id|string|max:255',
            'phone' => 'required_without:customer_id|string|max:20',
            'email' => 'required_without:customer_id|email|max:255',
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
        ], [
            'name.required_without' => 'Name is required for guest users',
            'phone.required_without' => 'Phone is required for guest users',
            'email.required_without' => 'Email is required for guest users',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error', 422, $validator->errors()->toArray());
        }

        $data = $request->only(['customer_id', 'name', 'phone', 'email', 'subject', 'message']);

        // Determine inquiry type and populate customer data if customer_id is provided
        if ($request->customer_id) {
            $customer = Customer::find($request->customer_id);

            if (! $customer) {
                return $this->sendError('Customer not found', 404);
            }

            // Auto-populate customer data
            $data['name'] = $customer->first_name.' '.$customer->last_name;
            $data['phone'] = $customer->mobile;
            $data['email'] = $customer->email;
            $data['inquiry_type'] = 'customer';
        } else {
            // Guest user
            $data['inquiry_type'] = 'guest';
        }

        // Set default status to 'New' (0)
        $data['status'] = 0;

        // Create the contact inquiry
        $inquiry = ContactInquiry::create($data);

        $response = [
            'inquiry_id' => $inquiry->id,
            'inquiry_type' => $inquiry->inquiry_type,
            'status' => $inquiry->status,
            'message' => 'Your inquiry has been submitted successfully. We will get back to you soon.',
        ];

        return $this->returnResponse($response, 'Contact inquiry submitted successfully', 201);
    }

    /**
     * Get customer's contact inquiries (Authenticated customers only)
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCustomerInquiries(Request $request)
    {
        $authenticatedCustomer = auth('customer-api')->user();

        if (! $authenticatedCustomer) {
            return $this->sendError('Unauthorized access', 403);
        }

        $inquiries = ContactInquiry::where('customer_id', $authenticatedCustomer->id)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($inquiry) {
                return [
                    'id' => $inquiry->id,
                    'subject' => $inquiry->subject,
                    'message' => $inquiry->message,
                    'status' => $this->getStatusLabel($inquiry->status),
                    'status_code' => $inquiry->status,
                    'admin_response' => $inquiry->admin_response,
                    'responded_at' => $inquiry->responded_at?->format('Y-m-d H:i:s'),
                    'created_at' => $inquiry->created_at->format('Y-m-d H:i:s'),
                ];
            });

        $data = [
            'customer_id' => $authenticatedCustomer->id,
            'total_inquiries' => $inquiries->count(),
            'inquiries' => $inquiries,
        ];

        return $this->returnResponse($data, 'Customer inquiries retrieved successfully');
    }

    /**
     * Get status label from status code
     *
     * @param  int  $status
     * @return string
     */
    private function getStatusLabel($status)
    {
        return match ($status) {
            0 => 'New',
            1 => 'In Progress',
            2 => 'Resolved',
            3 => 'Closed',
            default => 'Unknown',
        };
    }
}
