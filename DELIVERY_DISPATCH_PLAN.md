# Delivery Dispatch Plan

## Purpose

This document explains:

- what was implemented for vendor and driver ETA planning
- how preview works before order placement
- how actual driver assignment works after order creation
- what is still pending

## Business Goal

The original `NearbyVendorController` only returned:

- nearest vendor to customer
- vendor to customer ETA

That was not enough for delivery operations because real dispatch also needs:

- driver to vendor ETA
- vendor preparation time
- pickup alignment between bag readiness and driver arrival

The current work introduces a shared planning layer for both:

1. preview before order placement
2. real driver assignment after order creation

## What Was Implemented

### 1. Shared planning service

Added:

- [app/Services/DeliveryPlanningService.php](/abs/path/C:/Users/ADMIN/Herd/laravel_project/app/Services/DeliveryPlanningService.php:1)

This service now handles:

- vendor discovery
- vendor to customer ETA
- driver discovery near vendor
- driver to vendor ETA
- driver ranking
- prep-time alignment
- final dispatch preview

### 2. Preview before order placement

Updated:

- [app/Http/Controllers/Api/V1/NearbyVendorController.php](/abs/path/C:/Users/ADMIN/Herd/laravel_project/app/Http/Controllers/Api/V1/NearbyVendorController.php:1)
- [app/Helpers/helpers.php](/abs/path/C:/Users/ADMIN/Herd/laravel_project/app/Helpers/helpers.php:1)

`NearbyVendorController` now:

- accepts `session-latitude` / `session-longitude`
- still accepts `latitude` / `longitude`
- accepts optional `estimated_preparation_minutes`

Response now includes, per vendor:

- nearest vendor data
- `best_driver`
- `dispatch_preview`

Example dispatch preview fields:

- `estimated_preparation_minutes`
- `driver_to_vendor_eta_minutes`
- `estimated_pickup_minutes`
- `vendor_to_customer_eta_minutes`
- `estimated_delivery_minutes`
- `driver_wait_minutes`
- `driver_delay_minutes`

This means the app can now show a delivery estimate even before the order is placed.

### 3. Real driver assignment after order creation

Updated:

- [app/Jobs/AssignDriverToOrderJob.php](/abs/path/C:/Users/ADMIN/Herd/laravel_project/app/Jobs/AssignDriverToOrderJob.php:1)

Old behavior:

- picked the first active available driver

New behavior:

- loads the order
- loads assigned vendor
- loads delivery address
- estimates order preparation time
- finds candidate nearby drivers
- computes `driver -> vendor` ETA
- combines that with `vendor -> customer` ETA
- chooses the best driver based on alignment and delivery outcome
- writes `driver_id`
- creates `assigned_to_driver` tracking entry

Important:

- this assignment does **not** force the main order status forward
- this avoids breaking the current status sequence where `assigned_to_driver` comes after `ready_for_pickup`

### 4. Driver location freshness support

Added migration:

- [database/migrations/2026_05_02_000000_add_location_updated_at_to_drivers_table.php](/abs/path/C:/Users/ADMIN/Herd/laravel_project/database/migrations/2026_05_02_000000_add_location_updated_at_to_drivers_table.php:1)

Updated model:

- [app/Models/Driver.php](/abs/path/C:/Users/ADMIN/Herd/laravel_project/app/Models/Driver.php:1)

New field:

- `location_updated_at`

Why:

- `updated_at` is not reliable for dispatch freshness
- admin edits can change it
- dispatch should use actual location freshness

### 5. Prep-time estimation support

Updated:

- [app/Models/OrderItems.php](/abs/path/C:/Users/ADMIN/Herd/laravel_project/app/Models/OrderItems.php:1)

Added relation:

- `cut()`

The service estimates preparation time using:

1. `product_cuts.preparation_time`
2. fallback to `products.preparation_time`
3. fallback default of `15` minutes

Current formula:

- `max(item prep time) + 2 minutes per additional item`

This is a reasonable first version because item prep often overlaps in parallel.

## Dispatch Logic Summary

### Preview flow

1. customer sends location
2. system finds candidate vendors
3. system computes `vendor -> customer` ETA
4. system finds nearby available drivers around each vendor
5. system computes `driver -> vendor` ETA
6. if prep estimate is given, driver ranking uses prep alignment
7. API returns best vendor plus best driver preview

### Assignment flow

1. order is created
2. vendor assignment exists on `store_vendor_orders`
3. `AssignDriverToOrderJob` runs
4. service computes best driver for that vendor
5. order gets `driver_id`
6. tracking row is created for `assigned_to_driver`

## Driver Ranking Logic

The service does not choose only the closest driver.

It considers:

- travel time from driver to vendor
- vendor prep time
- gap between driver arrival and bag readiness
- final delivery ETA

Main idea:

- a driver arriving too early wastes time
- a driver arriving too late delays the customer
- best driver is the one that best matches prep timing while keeping delivery fast

## Files Changed

Main implementation:

- [app/Services/DeliveryPlanningService.php](/abs/path/C:/Users/ADMIN/Herd/laravel_project/app/Services/DeliveryPlanningService.php:1)
- [app/Jobs/AssignDriverToOrderJob.php](/abs/path/C:/Users/ADMIN/Herd/laravel_project/app/Jobs/AssignDriverToOrderJob.php:1)
- [app/Http/Controllers/Api/V1/NearbyVendorController.php](/abs/path/C:/Users/ADMIN/Herd/laravel_project/app/Http/Controllers/Api/V1/NearbyVendorController.php:1)
- [app/Helpers/helpers.php](/abs/path/C:/Users/ADMIN/Herd/laravel_project/app/Helpers/helpers.php:1)
- [app/Models/Driver.php](/abs/path/C:/Users/ADMIN/Herd/laravel_project/app/Models/Driver.php:1)
- [app/Models/OrderItems.php](/abs/path/C:/Users/ADMIN/Herd/laravel_project/app/Models/OrderItems.php:1)
- [database/migrations/2026_05_02_000000_add_location_updated_at_to_drivers_table.php](/abs/path/C:/Users/ADMIN/Herd/laravel_project/database/migrations/2026_05_02_000000_add_location_updated_at_to_drivers_table.php:1)

Tests added/updated:

- [tests/Feature/NearbyVendorApiTest.php](/abs/path/C:/Users/ADMIN/Herd/laravel_project/tests/Feature/NearbyVendorApiTest.php:1)
- [tests/Feature/AssignDriverToOrderJobTest.php](/abs/path/C:/Users/ADMIN/Herd/laravel_project/tests/Feature/AssignDriverToOrderJobTest.php:1)

## What Still Needs To Be Done

### Required next steps

1. Run migrations.

Required for:

- `drivers.location_updated_at`

2. Update driver location cron/job.

When driver coordinates are updated every 2 minutes, also update:

- `location_updated_at = now()`

Without this, location freshness filtering will be weaker.

3. Run tests locally.

I could not run PHP tests from this shell because PHP was not available in the current sandboxed environment.

Recommended tests:

- `tests/Feature/NearbyVendorApiTest.php`
- `tests/Feature/AssignDriverToOrderJobTest.php`

### Recommended near-term improvements

1. Add a dedicated preview API payload contract.

Right now preview is embedded inside nearby vendor response. That is fine for now, but a separate delivery quote endpoint may be cleaner later.

2. Revisit status flow.

Current seeded order statuses place:

- `assigned_to_driver` after `ready_for_pickup`

But dispatch is now happening earlier in practice.

This should be cleaned up later either by:

- changing status sequence
- or introducing a dispatch state separate from customer-facing order status

3. Persist planning metadata if needed.

If ops wants auditability, add fields or table for:

- selected prep estimate
- selected driver ETA
- selected vendor to customer ETA
- score used during assignment

4. Add vendor-specific prep overrides.

Current prep estimate is derived from product/cut values. Later this can be improved with:

- vendor-specific prep baseline
- time-of-day adjustments
- live kitchen load
- manual vendor ready time

5. Add ETA caching.

Google Distance Matrix calls can become expensive.

Later improvement:

- cache identical route estimates for a short duration

6. Consider fairness balancing.

Current driver selection focuses on timing quality. Later balance with:

- last assigned time
- workload rotation
- rating

## Important Notes

### Existing status mismatch

There is a business mismatch already present in current code:

- operationally we want to assign driver during preparation
- seeded statuses place `assigned_to_driver` later

Current implementation avoids breaking existing order flow by:

- assigning `driver_id`
- writing tracking row
- not forcing main order status change

This is intentional.

### Existing fallback behavior

If Google ETA fails:

- system falls back to Haversine air distance
- `eta_minutes` may be null
- `has_google_estimate` becomes false

This behavior still exists and is unchanged.

## Suggested Tomorrow Start Point

If another model or engineer continues this work tomorrow, start in this order:

1. Read [app/Services/DeliveryPlanningService.php](/abs/path/C:/Users/ADMIN/Herd/laravel_project/app/Services/DeliveryPlanningService.php:1)
2. Read [app/Jobs/AssignDriverToOrderJob.php](/abs/path/C:/Users/ADMIN/Herd/laravel_project/app/Jobs/AssignDriverToOrderJob.php:1)
3. Read [app/Http/Controllers/Api/V1/NearbyVendorController.php](/abs/path/C:/Users/ADMIN/Herd/laravel_project/app/Http/Controllers/Api/V1/NearbyVendorController.php:1)
4. Run migrations
5. Verify driver location updater writes `location_updated_at`
6. Run the two feature tests

