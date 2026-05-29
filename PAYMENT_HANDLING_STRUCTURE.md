# Payment Handling Structure Documentation

## Overview
The payment system is designed to handle multiple payment providers including online gateways (Razorpay, PhonePe, Paytm) and Cash on Delivery (COD). The system uses a flexible architecture with contracts and gateways to support different payment methods.

## Architecture Components

### 1. Payment Manager
**File:** `app/Services/Payments/PaymentManager.php`

**Purpose:** Central hub for managing different payment providers.

```php
final class PaymentManager
{
    public static function gateway(string $provider): PaymentInterface
    {
        return match (strtolower(trim($provider))) {
            'razorpay' => new RazorpayPayment(),
            'phonepe' => new PhonePePayment(),
            'paytm' => new PaytmPayment(),
            'cod' => new CodPayment(),
            default => throw new InvalidArgumentException('Unsupported payment provider.'),
        };
    }
}
```

### 2. Payment Interface Contract
**File:** `app/Services/Payments/Contracts/PaymentInterface.php`

**Purpose:** Defines the contract that all payment gateways must implement.

```php
interface PaymentInterface
{
    public function createOrder(Orders $order): mixed;
    public function verifyPayment(array $data): bool;
}
```

## Payment Gateways

### Online Payment Gateways

#### Razorpay Gateway
**File:** `app/Services/Payments/Gateways/RazorpayPayment.php`

**Features:**
- Creates payment orders via Razorpay API
- Handles payment verification
- Converts amounts to paisa (multiplies by 100)

**createOrder() Response:**
```json
{
  "id": "order_xyz123",
  "entity": "order",
  "amount": 50000,
  "amount_paid": 0,
  "amount_due": 50000,
  "currency": "INR",
  "receipt": "order_123",
  "status": "created"
}
```

#### PhonePe & Paytm Gateways
**Files:**
- `app/Services/Payments/Gateways/PhonePePayment.php`
- `app/Services/Payments/Gateways/PaytmPayment.php`

**Note:** Implementation details would follow similar patterns to Razorpay.

### Cash on Delivery (COD) Gateway
**File:** `app/Services/Payments/Gateways/CodPayment.php`

**Features:**
- No external API calls required
- Returns minimal order data for tracking
- Payment verification always returns true (handled during delivery)

**createOrder() Response:**
```json
{
  "id": "cod_123",
  "amount": 450.00,
  "currency": "INR",
  "status": "cod_pending"
}
```

## Order Integration

### Order Model Payment Fields
**File:** `app/Models/Orders.php`

**Payment-related fields:**
```php
protected $fillable = [
    // ... other fields
    'billing_type_id',
    'payment_status',     // 0 = pending, 1 = paid, etc.
    'status',             // Order status
    // ... other fields
];
```

### Order Creation Flow
**File:** `app/Http/Controllers/Api/V1/Order/OrderController.php`

**Payment Processing Steps:**
1. **Order Creation:** Create order record with payment_status = 0 (pending)
2. **Payment Gateway:** Call appropriate gateway based on `provider` parameter
3. **Payment Data:** Return payment information to frontend
4. **Response Structure:**
```json
{
  "success": true,
  "message": "Order created successfully.",
  "data": {
    "order": { /* order details */ },
    "payment_provider": "razorpay",
    "payment": { /* gateway response */ }
  }
}
```

## API Request Structure

### Order Creation Request
```json
{
  "items": [/* order items */],
  "delivery_address_id": 1,
  "billing_address_id": 1,
  "delivery_date": "2024-12-25",
  "delivery_time_slot": "10:00 AM - 12:00 PM",
  "billing_type_id": 1,
  "payment_method": "online",
  "provider": "razorpay"
}
```

**Provider Options:**
- `"razorpay"` - Razorpay payment gateway
- `"phonepe"` - PhonePe payment gateway
- `"paytm"` - Paytm payment gateway
- `"cod"` - Cash on Delivery

## Payment Status Flow

### Online Payments
1. **Order Created** → payment_status = 0 (pending)
2. **Payment Initiated** → Gateway creates payment order
3. **Payment Completed** → Webhook/API call updates payment_status = 1 (paid)
4. **Order Confirmed** → status updated to confirmed

### Cash on Delivery
1. **Order Created** → payment_status = 0 (pending)
2. **Order Delivered** → payment_status updated to 1 (paid) upon delivery confirmation
3. **No Gateway Integration** → COD orders skip payment gateway processing

## Configuration

### Environment Variables
```env
# Razorpay
RAZORPAY_KEY=your_razorpay_key
RAZORPAY_SECRET=your_razorpay_secret

# PhonePe
PHONEPE_MERCHANT_ID=your_merchant_id
PHONEPE_SALT_KEY=your_salt_key

# Paytm
PAYTM_MERCHANT_ID=your_merchant_id
PAYTM_MERCHANT_KEY=your_merchant_key
```

### Service Configuration
**File:** `config/services.php`
```php
'razorpay' => [
    'key' => env('RAZORPAY_KEY'),
    'secret' => env('RAZORPAY_SECRET'),
],
```

## Security Considerations

1. **Provider Validation:** Only allowed providers can be used
2. **Amount Validation:** Order amounts are validated before payment creation
3. **Customer Verification:** Orders can only be created by authenticated customers
4. **Address Validation:** Delivery/billing addresses must belong to the customer

## Error Handling

### Payment Creation Failures
- **Invalid Provider:** `InvalidArgumentException` for unsupported providers
- **SDK Missing:** `RuntimeException` when payment SDK not installed
- **API Errors:** Gateway-specific errors returned from payment providers

### Validation Errors
- Provider must be one of: razorpay, phonepe, paytm, cod
- Billing type must exist
- All required order fields must be valid

## Future Enhancements

1. **Payment Verification Webhooks:** Implement webhook endpoints for payment status updates
2. **Refund Processing:** Add refund capabilities for different gateways
3. **Partial Payments:** Support for partial payment scenarios
4. **Payment Methods:** Add more payment options (UPI, wallets, etc.)
5. **Multi-currency:** Support for different currencies beyond INR

## Testing

### Unit Tests
- Test each gateway implementation
- Mock external API calls
- Test error scenarios

### Integration Tests
- End-to-end order creation with payments
- Webhook processing tests
- COD order flow tests

## Files Summary

```
app/
├── Services/
│   └── Payments/
│       ├── PaymentManager.php
│       ├── Contracts/
│       │   └── PaymentInterface.php
│       └── Gateways/
│           ├── RazorpayPayment.php
│           ├── PhonePePayment.php
│           ├── PaytmPayment.php
│           └── CodPayment.php
├── Http/Controllers/Api/V1/Order/
│   └── OrderController.php
└── Models/
    └── Orders.php
```</content>
<parameter name="filePath">c:\Users\ADMIN\Herd\laravel_project\PAYMENT_HANDLING_STRUCTURE.md
