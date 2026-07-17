# Driver App — Phase 1 Implementation Notes

Phase 1 of the driver PWA → APK plan: finish the backend endpoints that were
still stubs, so the app is functionally complete before moving to PWA icons
and the APK build (Phase 2/3).

## Why

`routes/driver-api-sample.php` (despite its name, these are the live routes
included by `routes/api.php`) had three endpoints implemented as inline
closures with `// TODO` comments and no real business logic:

- `POST /api/driver/orders/{id}/cancel` — always returned a canned success
  response without touching the database.
- `GET /api/driver/profile` — worked, but hardcoded `total_deliveries: 127`
  and `rating: '4.8'` instead of reading the driver's real stats.
- `PUT /api/driver/profile` — validated input but never persisted it.

There was also a dead file, `app/Http/Controllers/Driver/DriverOrdersController.php`
(empty namespace declaration, no class body, unreferenced anywhere).

## What changed

### Driver profile (get/update)
- `app/Http/Requests/Api/V1/Driver/UpdateDriverProfileRequest.php` — validates
  `name`, `email` (unique per driver), `vehicle_type`, `vehicle_number`.
- `app/Actions/Driver/UpdateDriverProfile.php` — mirrors the existing
  `App\Actions\Customer\UpdateCustomer` action pattern.
- `app/Http/Controllers/Api/V1/Driver/DriverProfileController.php` —
  `show()` returns the real driver record (including actual
  `total_deliveries` and `rating` columns); `update()` persists changes.

### Driver order cancellation
- `app/Http/Requests/Api/V1/Driver/CancelDriverOrderRequest.php` — validates
  `reason` against the same enum the frontend's cancel modal offers
  (`store_closed`, `product_unavailable`, `customer_request`,
  `vehicle_issue`, `other`) plus optional `notes`.
- `app/Services/DriverOrderCancellationService.php` — only allows
  cancellation while the order is still `assigned_to_driver` (i.e. before
  the driver has accepted it, matching the existing frontend and README
  behavior). On cancel it: transitions the order to `cancelled` via the
  existing `UpdateOrderStatusAction`, records an `OrderCancellation` row
  (`cancelled_by = 'driver'`), and clears `driver_id` so the order can be
  picked up/reassigned to another driver at the same store.
- `app/Http/Controllers/Api/V1/Driver/DriverOrderCancellationController.php`
  — wires the request/service together, following the same
  `ValidationException` → 422 pattern used by `DriverOrderStatusController`.

### Cleanup
- Deleted the empty `app/Http/Controllers/Driver/DriverOrdersController.php`
  stub (confirmed unreferenced in routes or elsewhere).
- Updated `routes/driver-api-sample.php` to point at the real controllers
  and dropped the stale "sample" doc comment.

### Tests
- `tests/Feature/DriverProfileTest.php` — profile retrieval, update, and
  duplicate-email rejection.
- `tests/Feature/DriverOrderCancellationTest.php` — successful
  pre-acceptance cancellation (status, `is_cancelled`, `driver_id` cleared,
  `OrderCancellation` row created), rejection once accepted, and reason
  validation.

## Bugs found and fixed along the way

While wiring the cancellation flow (which triggers the same order-status-change
code path used by `PUT /orders/{id}/status`), two **pre-existing, unrelated
production bugs** surfaced and were blocking any order transition to
`driver_accepted`, `delivered`, or `cancelled`:

1. **`app/Observers/OrdersObserver.php`** dispatched
   `SendWhatsAppEtaJob` / `SendWhatsAppDeliveredJob` /
   `SendWhatsAppOrderCancelledJob` without importing them (they live in
   `App\Jobs`, not `App\Observers`). Any status change to those three
   statuses would fatal with "Class not found". Fixed by adding the
   missing `use` imports.
2. **`app/Contracts/WhatsApp/WhatsAppServiceInterface.php`** declared
   `sendOrderCreatedTemplate()` twice, which is a fatal PHP redeclaration
   error the moment anything autoloads the interface (i.e. the first time
   any WhatsApp job runs). Removed the duplicate declaration.

Both were confirmed pre-existing (not introduced by this work) by reproducing
them against the already-committed `DriverOrderStatusUpdateTest`. They were
fixed because they made it impossible to verify — or use — order status
transitions at all, not just the new cancellation endpoint.

## Known pre-existing issue NOT fixed (flagging, out of scope)

`tests/Feature/DriverOrderStatusUpdateTest.php` and
`tests/Feature/DriverTripTrackingTest.php` fail with:

```
RoleDoesNotExist: There is no role named `store_vendor` for guard `web`.
```

This comes from `App\Models\User::booted()`, which auto-syncs a Spatie
role whenever `user_role` is set on save — but the `store_vendor` role
isn't seeded in the test database. It affects any test that creates a
`User` with `user_role: 'store_vendor'` and isn't specific to this
feature. My new cancellation test works around it locally with
`Role::findOrCreate('store_vendor', 'web')`, but the two pre-existing
test files above still fail on a clean run. Worth a follow-up ticket to
seed roles globally in `tests/Pest.php` (e.g. via a `beforeEach` role
seeder) rather than per-test.

## Test results

```
Tests\Feature\DriverProfileTest            ✓ 3 passed
Tests\Feature\DriverOrderCancellationTest  ✓ 3 passed
```

`vendor/bin/pint --dirty` run and applied clean on all touched files.

## Not done (Phase 2/3, per the original plan)

- PWA icons (`icon-192.png` / `icon-512.png`) still missing from `public/`.
- `assetlinks.json` for TWA full-screen behavior.
- Actual APK build (Bubblewrap/PWABuilder).
