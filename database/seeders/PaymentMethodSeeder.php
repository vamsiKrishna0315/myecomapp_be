<?php

namespace Database\Seeders;

use App\Models\PaymentMethod;
use Illuminate\Database\Seeder;

class PaymentMethodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $paymentMethods = [
            [
                'code' => 'cod',
                'title' => 'Cash on Delivery',
                'description' => 'Pay when your order reaches your doorstep.',
                'sort_order' => 1,
            ],
            [
                'code' => 'wallet',
                'title' => 'Wallet',
                'description' => 'Use your wallet balance for instant checkout.',
                'sort_order' => 2,
            ],
            [
                'code' => 'razorpay',
                'title' => 'Razorpay',
                'description' => 'Cards, netbanking and secure online payments.',
                'sort_order' => 3,
            ],
            [
                'code' => 'phonepe_upi',
                'title' => 'PhonePe UPI',
                'description' => 'Complete the payment through your preferred UPI app.',
                'sort_order' => 4,
            ],
        ];

        foreach ($paymentMethods as $paymentMethod) {
            PaymentMethod::updateOrCreate(
                ['code' => $paymentMethod['code']],
                $paymentMethod
            );
        }
    }
}
