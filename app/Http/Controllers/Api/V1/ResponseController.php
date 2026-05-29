<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;

abstract class ResponseController extends Controller
{
    /**
     * Success response method.
     *
     * @param  mixed  $data
     * @param  string  $message
     * @param  int  $code
     * @return \Illuminate\Http\JsonResponse
     */
    final public function returnResponse($data, $message = 'Success', $code = 200)
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $code);
    }

    /**
     * Return error response.
     *
     * @param  string  $error
     * @param  int  $code
     * @param  array  $errorMessages
     * @return \Illuminate\Http\JsonResponse
     */
    final public function sendError($error, $code = 400, $errorMessages = [])
    {
        return response()->json([
            'success' => false,
            'message' => $error,
            'errors' => $errorMessages,
        ], $code);
    }

    // /**
    //  * Customer login/registration response with token.
    //  *
    //  * @param \App\Models\Customer $customer
    //  * @param string $token
    //  * @param string $message
    //  * @param int $code
    //  * @return \Illuminate\Http\JsonResponse
    //  */
    // public function customerLoginResponse($customer, $token, $message = 'Success', $code = 200)
    // {
    //     $data = [
    //         'token' => $token,
    //         'token_type' => 'bearer',
    //         'expires_in' => auth('customer-api')->factory()->getTTL() * 60,
    //         'customer' => [
    //             'id' => $customer->id,
    //             'first_name' => $customer->first_name,
    //             'last_name' => $customer->last_name,
    //             'full_name' => $customer->full_name,
    //             'email' => $customer->email,
    //             'mobile' => $customer->mobile,
    //             'dob' => $customer->dob?->format('Y-m-d'),
    //             'status' => $customer->status,
    //         ],
    //     ];

    //     return $this->returnResponse($data, $message, $code);
    // }
}
