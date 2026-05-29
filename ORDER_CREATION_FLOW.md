# Order Creation Flow Documentation

## Overview
This document outlines the complete order creation process in the Laravel application, from API request to database persistence. The flow involves multiple layers including controllers, actions, models, and validation.

## API Endpoint
**Route:** `POST /api/v1/customer/order`  
**Controller:** `App\Http\Controllers\Api\V1\Order\OrderController@store`  
**Middleware:** `auth:customer-api`, `throttle:60,1`

## Request JSON Structure

```json
{
  "items": [
    {
      "product_id": 1,
      "category_id": 1,
      "cut_id": 1,
      "product_name": "Chicken Breast",
      "cut_name": "Boneless",
      "unit_price": 250.00,
      "quantity": 1.5,
      "weight": 1.5,
      "weight_unit": "kg",
      "total_price": 375.00,
      "special_instructions": "Clean thoroughly"
    }
  ],
  "delivery_address_id": 1,
  "billing_address_id": 1,
  "delivery_date": "2024-12-25",
  "delivery_time_slot": "10:00 AM - 12:00 PM",
  "special_instructions": "Ring the doorbell twice",
  "coupon_code": "SAVE20",
  "billing_type_id": 1,
  "payment_method": "cod",
  "provider": "cod"
}
```

## Flow Steps

### 1. Request Validation
**File:** `app/Http/Requests/Api/V1/Order/StoreOrderRequest.php`

- Validates all required fields
- Validates array structure for items
- Validates foreign key constraints (product_id, category_id, cut_id from cut_types, addresses, billing_type)
- Validates coupon_code exists in coupons table
- **Custom validation:** Checks if product has the selected cut via cuttype_product table
- Validates delivery_date is not in past
- Validates numeric ranges and string lengths
- Validates provider is one of: razorpay, phonepe, paytm, cod

### 2. Controller Processing
**File:** `app/Http/Controllers/Api/V1/Order/OrderController.php`

```php
public function store(StoreOrderRequest $request): JsonResponse
{
    try {
        $customer = auth('customer-api')->user();
        
        $orderData = array_merge($request->validated(), [
            'customer_id' => $customer->id,
        ]);
        
        $order = app(CreateOrder::class)->executeInTransaction($orderData);
        
        $provider = $request->validated('provider', 'razorpay');
        $gateway = PaymentManager::gateway((string) $provider);
        $payment = $gateway->createOrder($order);
        
        return $this->returnResponse([
            'order' => $order,
            'payment_provider' => $provider,
            'payment' => $payment,
        ], 'Order created successfully.', 201);
        
    } catch (InvalidArgumentException $e) {
        return $this->sendError($e->getMessage(), 422);
    } catch (Exception $e) {
        return $this->sendError('Failed to create order: '.$e->getMessage(), 500);
    }
}
```

### 3. CreateOrder Action
**File:** `app/Actions/Order/CreateOrder.php`

**Main Method:** `execute(array $data): Orders`

#### Steps in execute():
1. **Calculate Totals:** Calls `CalculateOrderTotals::execute($data)`
2. **Create Order:** Calls `createOrder($data, $totals)`
3. **Create Order Items:** Calls `createOrderItems($order, $data['items'], $totals['tax_percentage'])`
4. **After Execution:** Calls `afterExecution($order, $data)` - loads relationships

#### createOrder() Method:
Creates `Orders` model with:
- Customer and address IDs
- Delivery details
- Financial data (subtotal, discount, tax, delivery, total)
- Status fields (payment_status: 0=pending, status: 1=active, current_status: 'pending')

#### createOrderItems() Method:
For each item in the request:
- Creates `OrderItems` model
- Calculates line tax: `($item['total_price'] * $taxPercentage) / 100`
- Sets line_total = total_price + line_tax

### 4. CalculateOrderTotals Action
**File:** `app/Actions/Order/CalculateOrderTotals.php`

**Current Implementation Issues:**
- Tax percentage hardcoded to 18.00%
- Discount amount hardcoded to 0 (TODO: implement coupon logic)
- Delivery charge hardcoded to 0 (TODO: implement delivery logic)

**Method:** `execute(array $data): array`

Returns:
```php
[
    'subtotal' => float,      // Sum of all item total_price
    'tax_percentage' => 18.00,
    'tax_amount' => float,    // (subtotal * tax_percentage) / 100
    'discount_amount' => 0,   // TODO: Not implemented
    'delivery_charge' => 0,   // TODO: Not implemented
    'total_amount' => float,  // subtotal - discount + tax + delivery
]
```

### 5. Payment Processing
**File:** `app/Services/Payments/PaymentManager.php`

After order creation:
- **Online Payment:** Creates payment order via payment gateway (Razorpay/PhonePe/Paytm)
- **Cash on Delivery (COD):** Returns minimal COD payment data without gateway integration

**COD Response Example:**
```json
{
  "id": "cod_123",
  "amount": 450.00,
  "currency": "INR", 
  "status": "cod_pending"
}
```

## Database Tables Involved

### orders
- Stores main order information
- Fields: customer_id, addresses, delivery details, financial totals, coupon_code, status fields

### order_items
- Stores individual items in the order
- Fields: order_id, product details, pricing, weight, tax calculations- **Foreign Keys**: cut_id now references `cut_types.id` (updated from product_cuts)
### Related Tables
- customers (via customer_id)
- addresses (delivery_address_id, billing_address_id)
- products, categories, cut_types (via items)
- cuttype_product (validates product-cut combinations)
- billing_types (billing_type_id)
- coupons (coupon_code validation)

## Current Issues

1. **Coupon Discount Not Applied:** 
   - Coupon validation exists but discount calculation is not implemented
   - `CalculateOrderTotals` hardcodes `discount_amount = 0`

2. **Delivery Charge Not Calculated:**
   - Hardcoded to 0, no logic for distance-based or zone-based charges

3. **Tax Percentage Hardcoded:**
   - Fixed at 18%, should be configurable

4. **No Coupon Validation During Order:**
   - Only validates coupon exists, doesn't check validity, usage limits, etc.

## Files Chain
```
Request → StoreOrderRequest (validation)
       → OrderController::store()
       → CreateOrder::execute()
         → CalculateOrderTotals::execute()
         → Orders::create()
         → OrderItems::create() [for each item]
       → PaymentManager::createOrder()
       → Response
```

## Next Steps
The discount calculation needs to be implemented in `CalculateOrderTotals` to actually apply coupon discounts to the order total.</content>
<parameter name="filePath">ORDER_CREATION_FLOW.md
