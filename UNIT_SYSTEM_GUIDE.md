# Unit Configuration System - Backend Implementation Guide

## Overview
The backend now supports dynamic unit configuration for products with automatic price conversions. The system includes:
- **Product-level unit configuration** (kg, gram, piece)
- **Automatic price conversion** service
- **API endpoints** that return unit data
- **Order creation** with unit handling

## What's Implemented

### 1. Database Migrations (✅ Applied)
Three migrations have been successfully applied:
- `2026_06_20_080609_add_units_fields_to_products_table` - Adds `allowed_units` (JSON) and `grams_per_piece` (decimal)
- `2026_06_20_082843_add_base_price_unit_to_products_table` - Adds `base_price_unit` (enum)
- `2026_06_22_095824_add_grams_per_piece_type_to_products_table` - Adds `grams_per_piece_type` (enum: 'standard' or 'custom') - **NEW**

**Verify**: Run `php artisan migrate:status` to confirm all migrations show "Ran"

### 2. UnitConversionService (✅ Complete)
Location: `app/Services/UnitConversionService.php`

Provides bidirectional conversions between kg, gram, and piece with 11 passing tests.

**Key Methods**:
- `convert($quantity, $fromUnit, $toUnit, $gramsPerPiece)` - Quantity conversion
- `calculatePrice($basePrice, $baseUnit, $requestedUnit, $gramsPerPiece)` - Price conversion

**Example Usage**:
```php
$converter = app(UnitConversionService::class);

// Convert 250g to kg
$kg = $converter->convert(250, 'gram', 'kg');  // Returns 0.25

// Convert ₹50/piece to ₹/kg (250g per piece)
$pricePerKg = $converter->calculatePrice(50, 'piece', 'kg', 250);  // Returns 200
```

### 3. Product Model (✅ Updated)
Updated to include:
- `allowed_units` - array cast
- `grams_per_piece` - decimal:3 cast
- `grams_per_piece_type` - enum ('standard' or 'custom') - **NEW**
- `base_price_unit` - enum field

### 4. Filament CMS Form (✅ Enhanced)
Location: `app/Filament/Resources/Products/Schemas/ProductsForm.php`

**Units Section Features**:
- **Dynamic price label** - Shows "Price per gram (g)", "Price per kg", or "Price per piece" based on `base_price_unit`
- **Allowed Units** - Multi-select for which units customers can order in
- **Base Price Unit** - Selector for which unit the price is based on
- **Weight Per Piece** - **NEW**: Smart selector for standard vs custom weight
  - **Standard (100g)** - Perfect for most piece-based products, auto-fills 100g
  - **Custom Weight** - For products with different piece sizes, shows input field
  - Conditional visibility based on whether "piece" is in allowed_units

### 5. Order Creation Integration (✅ Complete)
Location: `app/Actions/Order/CreateOrder.php`

The CreateOrder action now:
- Uses UnitConversionService to normalize prices
- Stores `weight_unit` to track which unit the customer ordered in
- Converts all prices to kg for internal consistency
- Preserves grams_per_piece configuration for conversions

### 6. API Responses (✅ Verified)
All product API endpoints automatically return unit configuration:
```json
{
  "id": 1,
  "name": "Chicken Pieces",
  "allowed_units": ["piece", "gram", "kg"],
  "base_price_unit": "piece",
  "grams_per_piece": "250.000",
  ...
}
```

## How to Use in Filament

### Creating/Editing Products with Units

1. **Go to Product Form**
   - Navigate to Products Management → Products → Create/Edit

2. **Set Allowed Units**
   - Check which units customers can order in
   - At least one unit should be selected

3. **Set Base Price Unit**
   - Select which unit the "Price" field represents
   - Must be one of the allowed units
   - Prices for other units auto-calculate

4. **Set Price**
   - The label changes based on base_price_unit
   - Example: If base_price_unit is "piece", label shows "Price per piece"

5. **Set Weight Per Piece** (if piece is allowed)
   - **New Feature**: Choose between "Standard (100g)" or "Custom Weight"
   - **Standard (100g)**: Automatically sets 100g per piece - perfect for most products
   - **Custom Weight**: Enter a specific weight (e.g., 250g for large pieces)
   - When you select Standard, the field auto-fills with 100g
   - When you select Custom, a text input appears to enter your custom weight

6. **Save the Product**
   - All unit fields should save correctly now

### Example Workflow: Standard vs Custom

**Scenario 1: Chicken Pieces (Standard)**
1. Allowed Units: select "piece, gram, kg"
2. Base Price Unit: select "piece"
3. Price: enter ₹50
4. Weight Per Piece: select "Standard (100g)" ✓ (auto-fills 100g)
5. Save - Done! ₹50 per 100g piece

**Scenario 2: Chicken Drumsticks (Custom)**
1. Allowed Units: select "piece, gram, kg"
2. Base Price Unit: select "piece"
3. Price: enter ₹120
4. Weight Per Piece: select "Custom Weight"
5. In the custom weight field, enter: 250
6. Save - Done! ₹120 per 250g drumstick

## Testing & Verification

### Run All Tests
```bash
php artisan test tests/Unit/Services/UnitConversionServiceTest.php \
    tests/Feature/ProductApiUnitResponseTest.php \
    tests/Feature/Products/ProductUnitConfigurationTest.php \
    tests/Feature/Products/ProductPieceWeightTypeTest.php
```

**Expected Output**: 24 passed tests (11 + 2 + 6 + 5)

### Test Individual Aspects

**Test Unit Conversion Logic** (11 tests):
```bash
php artisan test tests/Unit/Services/UnitConversionServiceTest.php
```

**Test Product API Response** (2 tests):
```bash
php artisan test tests/Feature/ProductApiUnitResponseTest.php
```

**Test Product Model & Database** (6 tests):
```bash
php artisan test tests/Feature/Products/ProductUnitConfigurationTest.php
```

**Test Piece Weight Type (Standard/Custom)** (5 tests - **NEW**):
```bash
php artisan test tests/Feature/Products/ProductPieceWeightTypeTest.php
```

### Verify Database Columns Exist
The migrations are applied. You can verify by querying a product:
- `allowed_units` should be a JSON column
- `grams_per_piece` should be a decimal(10,3) column
- `grams_per_piece_type` should be an enum('standard', 'custom') - **NEW**
- `base_price_unit` should be an enum('kg', 'gram', 'piece')

## Troubleshooting

### "Can't save units in Filament"
**Solution**: 
1. Ensure migrations ran: `php artisan migrate:status`
2. The form fields are now properly labeled and validated
3. Make sure `base_price_unit` is one of the `allowed_units` - the form validates this

### "Price conversion not working"
**Solution**: 
1. Verify `grams_per_piece` is set when piece unit is used
2. Check that `base_price_unit` is correctly set
3. Use the UnitConversionService directly to test:
```php
$converter = app(\App\Services\UnitConversionService::class);
$price = $converter->calculatePrice(50, 'piece', 'kg', 250);
```

### "API not returning unit fields"
**Solution**: 
1. Verify product has unit fields set
2. Product API automatically includes all model attributes
3. Test with: `GET /api/v1/customer/products/{id}`

## Files Modified/Created

**Created**:
- `app/Services/UnitConversionService.php`
- `tests/Unit/Services/UnitConversionServiceTest.php`
- `tests/Feature/ProductApiUnitResponseTest.php`
- `tests/Feature/Products/ProductUnitConfigurationTest.php`
- `tests/Feature/Products/ProductPieceWeightTypeTest.php` - **NEW**
- `database/migrations/2026_06_22_095824_add_grams_per_piece_type_to_products_table.php` - **NEW**

**Modified**:
- `app/Models/Product.php` - Added unit fields to fillable & casts, added `grams_per_piece_type`
- `app/Filament/Resources/Products/Schemas/ProductsForm.php` - Enhanced form with unit controls and standard/custom selector
- `app/Actions/Order/CreateOrder.php` - Integrated unit conversion
- `database/migrations/2026_06_20_080609_add_units_fields_to_products_table.php` - Implemented migration
- `database/migrations/2026_06_20_082843_add_base_price_unit_to_products_table.php` - Implemented migration
- `app/Http/Requests/Api/V1/Order/StoreOrderRequest.php` - Updated validation for units

## Next Steps

### Frontend Integration (Next Phase)
1. Create Next.js unit conversion utility (mirror of Laravel service)
2. Add dynamic unit selector to ProductDetails component
3. Update cart to store selected unit
4. Update checkout to send correct unit information

### Order Creation (Already Integrated)
Orders created via API automatically use the UnitConversionService:
- Supports units: kg, gram, piece
- Automatically converts prices to normalized kg storage
- Preserves original weight_unit for reference








