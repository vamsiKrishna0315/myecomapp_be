# Driver Live Tracking Implementation

## Purpose

This document explains the driver live-location, trip breadcrumb, and in-app map work added on 2026-05-03.

Use this file as the handoff point for future AI agents before changing driver maps, customer live tracking, or trip persistence.

## What Was Added

### 1. Driver current-location updates while app is open

Shared driver layout now starts a browser geolocation sync loop on driver pages:

- interval: every 2 minutes
- scope: only while the driver PWA is open
- exclusions: login, OTP, forgot-password screens

Frontend file:

- `resources/views/driver/layout.blade.php`

API endpoint:

- `POST /api/driver/location`

Backend controller:

- `app/Http/Controllers/Api/V1/Driver/DriverLocationController.php`

Service:

- `app/Services/DriverTripTrackingService.php`

Behavior:

- updates `drivers.current_lat`
- updates `drivers.current_lng`
- updates `drivers.location_updated_at`

## 2. Order-specific trip breadcrumb storage

New table:

- `driver_trip_locations`

Migration:

- `database/migrations/2026_05_03_000000_create_driver_trip_locations_table.php`

Model:

- `app/Models/DriverTripLocation.php`

Relations added:

- `Driver::tripLocations()`
- `Orders::tripLocations()`

Each row stores:

- `order_id`
- `driver_id`
- `lat`
- `lng`
- `accuracy_meters`
- `source_status_code`
- `trip_phase`
- `recorded_at`

## 3. Trip capture rules

When `/api/driver/location` is called:

1. driver current location is updated
2. service finds the driver’s active trackable orders
3. one breadcrumb row is written per trackable order

Trackable status codes:

- `assigned_to_driver`
- `driver_accepted`
- `driver_at_store`
- `driver_picked_up`
- `driver_nearby`
- `driver_reached`
- `accepted`
- `picked_up`
- `out_for_delivery`

Trip phases:

- `to_vendor`
  used before pickup
- `to_customer`
  used after pickup / delivery run

This is the current best-fit interpretation of the existing order flow.

## 4. Driver in-app live map

New web route:

- `GET /driver/orders/{orderId}/map`

Controller method:

- `DriverController::orderMap()`

View:

- `resources/views/driver/order-map.blade.php`

Map data endpoint:

- `GET /api/driver/orders/{orderId}/map`

Payload includes:

- order summary
- vendor coordinates
- driver current coordinates
- customer destination coordinates
- breadcrumb trail
- ETA metadata if Google route estimate is available

Current driver UI behavior:

- dashboard map button only remains on active orders
- pending orders no longer show map action
- order details now include an embedded live route preview for active orders
- full-screen map screen shows:
  - large map area
  - order summary panel
  - ETA / distance / freshness cards

## 5. Customer live-trip payload

Updated endpoint:

- `GET /api/v1/customer/orders/{uuid}/track`

Controller:

- `app/Http/Controllers/Api/V1/Order/OrderTrackingDetailsController.php`

New response field:

- `data.live_trip`

`live_trip` includes:

- current order status summary
- assigned vendor coordinates
- driver current coordinates
- customer destination coordinates
- trip breadcrumb trail
- ETA metadata if available

This is intended to support customer-side map polling every ~30 seconds later.

## 6. Google Maps usage

Current frontend map rendering uses Google Maps JavaScript API directly in Blade views:

- full-screen driver map
- order-detail embedded map preview

Current ETA source uses backend helper:

- `fetch_google_eta_minutes()`

If Google route estimate is unavailable:

- route line may still render in frontend if JS directions call works
- backend ETA returns unavailable
- UI shows `N/A` / `No ETA available`

## Files Added

- `app/Http/Controllers/Api/V1/Driver/DriverLocationController.php`
- `app/Models/DriverTripLocation.php`
- `app/Services/DriverTripTrackingService.php`
- `database/migrations/2026_05_03_000000_create_driver_trip_locations_table.php`
- `resources/views/driver/order-map.blade.php`
- `tests/Feature/DriverTripTrackingTest.php`
- `DRIVER_LIVE_TRACKING_IMPLEMENTATION.md`

## Files Updated

- `app/Http/Controllers/DriverController.php`
- `app/Http/Controllers/Api/V1/Order/OrderTrackingDetailsController.php`
- `app/Models/Driver.php`
- `app/Models/Orders.php`
- `resources/views/driver/dashboard.blade.php`
- `resources/views/driver/layout.blade.php`
- `resources/views/driver/order-detail.blade.php`
- `routes/driver-api-sample.php`
- `routes/web.php`

## Important Limitations

### Browser-only tracking

This is not native background GPS tracking.

Current behavior is only reliable while:

- the driver app is open
- the browser tab/PWA is alive
- geolocation permission is granted

If the phone suspends the browser, updates can be delayed or skipped.

### Customer frontend not yet implemented here

Backend now exposes the trip trail, but this repository change does not add a customer map UI.

Next agent can use:

- `data.live_trip.vendor`
- `data.live_trip.driver`
- `data.live_trip.destination`
- `data.live_trip.trail`

to render a live bike flow on the customer side.

## Recommended Next Steps

1. Run migrations.
2. Verify Google Maps JavaScript API key has Maps + Directions access.
3. Test `/driver/orders/{orderId}/map` on a real phone.
4. Build customer polling UI using `GET /api/v1/customer/orders/{uuid}/track`.
5. Later move tracking to native app/background service if strict live tracking is required.
