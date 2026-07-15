# Referral Coupon Feature — What & Why

Customers get a personal, shareable coupon link. When someone else places an order
using it, the referrer can track how many of those orders actually succeeded
(paid + delivered). One coupon per customer, self-referral blocked, discount
percentage is a single global setting for all referral coupons.

## Backend (this repo) — done

### Data model
- `coupons` table gained `customer_id` (nullable FK to `customers`, the coupon owner)
  and `is_referral` (bool). A referral coupon is just a normal `Coupon` row with
  these two fields set — no new coupon type/table, reuses all existing
  validity/usage-limit logic in `CouponController` and `CalculateOrderTotals`.
- `coupon_settings` — single row, global config for referral coupons (not
  per-coupon, matches "it will be common for all" from the brief). Mirrors the
  real `Coupon` fields rather than a bare percentage: `referral_discount_type`
  (Percentage/Fixed, same `CouponType` enum), `referral_discount_value`,
  `referral_min_order_amount`, `referral_max_discount_amount` (cap, only
  meaningful for percentage). `CouponSetting::current()` returns the row (or
  an in-memory default if none exists yet).
  **Note:** changing these settings only affects newly generated referral
  coupons; existing customers' coupons keep whatever config they were created
  with. If you want it to apply retroactively, that needs a follow-up
  migration/job.
- `Coupon::orders()` — a `hasMany` keyed on `coupon_code = code` (not a real FK,
  since `orders.coupon_code` was already a plain string column). Used for both
  the analytics service and the Filament leaderboard, so usage-counting logic
  lives in one place.

### Why no new "coupon_usages" pivot table
`orders` already stores `coupon_code`, `customer_id`, `payment_status`, and
`delivered_at`. "Successful order" is just `payment_status = 1 AND delivered_at
IS NOT NULL AND is_cancelled = false` on that existing table — a pivot table
would have duplicated state that's already there. If usage patterns get more
complex later (e.g. tracking pre-order-placement link opens for a conversion
funnel), that's the point to introduce one.

### Service — `app/Services/ReferralCouponService.php`
- `getOrCreateForCustomer()` — one referral coupon per customer (per your
  answer), created lazily on first request.
- `checkEligibility($code, $checkoutCustomerId = null)` — validity/expiry/limit
  checks (mirrors `CouponController::validateCoupon`) plus the self-referral
  block when the checkout customer is known.
- `getAnalytics($customer)` — total orders, successful orders, pending orders,
  total discount given, all off the `orders()` relation.

### API — `app/Http/Controllers/Api/V1/Coupon/ReferralController.php`
- `POST /api/v1/customer/referral/generate` (auth) — get-or-create + share URL.
- `GET /api/v1/customer/referral/check?code=` (**public**, throttled) — a guest
  opening a share link before logging in needs this to work without a token.
  If a customer-api token *is* present it's still read (soft auth), so
  self-referral gets caught here too whenever possible.
- `GET /api/v1/customer/referral/analytics` (auth) — the caller's own stats.
- `config('app.frontend_url')` (env `FRONTEND_URL`) drives the generated
  share URL — set this per environment.

### Self-referral — enforced twice
1. `ReferralController::check` — best-effort, only if the checker happens to
   be authenticated (guests can't be checked yet).
2. `StoreOrderRequest::withValidator` — the real guard. At order placement the
   customer is always authenticated, so this is the point that actually blocks
   a self-referral order, with a normal 422 validation error on `coupon_code`.

### Filament
- `CouponsResource` form/table now show the owning customer and an `is_referral`
  toggle, so support staff can see/debug referral coupons in the existing
  coupon list rather than a separate screen.
- **Referral Settings** (`app/Filament/Pages/ReferralSettings.php`) — single
  form: discount type (Percentage/Fixed), value (label/suffix flips with
  type, same UX as `CouponsForm`), minimum order amount, and max discount
  amount (only shown for Percentage) — all 4 fields feed straight into new
  referral coupons via `ReferralCouponService::getOrCreateForCustomer()`.
- **Referral Performance** (`app/Filament/Pages/ReferralPerformance.php`) —
  read-only leaderboard: customer, code, total orders, successful orders,
  conversion %, total discount given. Built as a page (not a Resource) since
  it's derived/read-only data, not a CRUD entity.

### Pre-existing bug fixed: percentage coupons gave ₹0 discount
`CalculateOrderTotals::calculateDiscount()` checked `$coupon->type === 1` for
percentage and `=== 2` for fixed, but `CouponType` enum + the coupons migration
define `0 = Percentage, 1 = FixedAmount` (and the Filament admin form,
`OrdersForm.php`, already used the correct enum values). Every percentage
coupon placed through the customer-facing order API was silently computing
₹0 discount. Fixed to compare against `CouponType::Percentage->value` /
`CouponType::FixedAmount->value`, matching the admin form's logic. Covered by
`tests/Feature/CalculateOrderTotalsTest.php` (percentage + fixed cases).

### Other pre-existing issue found, not fixed (out of scope)
- `tests/Feature/OrderControllerTest.php` was already failing before this work
  (Mockery can't mock the `final class PaymentManager`) — unrelated to
  referrals or the coupon-type bug, left as found.

### Tests
- `tests/Feature/ReferralCouponServiceTest.php` — coupon reuse, eligibility
  (invalid/expired/self-referral/valid), analytics (zero state + paid/delivered
  counting).
- `tests/Feature/ReferralControllerTest.php` — generate/check/analytics
  endpoints, auth requirements, guest check.

## Frontend (licious-clone-nextjs) — done

Backend env: added `FRONTEND_URL=http://localhost:3000` to the Laravel `.env`
so `ReferralController::generate` builds a working `share_url`. Update this
per environment (staging/prod) to the deployed FE origin.

### `utils/referral.js` (new)
localStorage helpers, following the existing `cartStorage.js` pattern (guarded
`typeof window === "undefined"` checks, try/catch): `getReferralSessionKey()`
(creates a uuid under `ReferralSessionKey` once, reused after), `getStoredReferralCode()`,
`setStoredReferralCode()`, `clearStoredReferralCode()`.

### `components/Referral/ReferralCapture.jsx` (new), wired into `components/Providers.jsx`
Renders `null` on every route (mounted once, app-wide, inside the existing
client provider tree). Reads `?ref=CODE` via `useSearchParams` (wrapped in
`Suspense` as Next.js requires), ensures a session key exists, and calls
`GET /referral/check?code=` — only stores the code in `localStorage` if the
backend says `eligible: true`. Invalid/expired/self-referral codes are
silently dropped, matching "no problem if it is empty" from the brief.

### `app/referral/page.js` + `app/referral/layout.tsx` (new)
Modeled on `app/profile/page.js`'s structure/guard pattern (`promptLogin` on
missing/expired token, Chakra `useToast`). Calls `POST /referral/generate` and
`GET /referral/analytics` on mount, shows the code + copyable share link and a
4-stat grid (total/successful/pending orders, total discount given). Linked
from the account dropdown in `components/Navbar/Navbar.jsx` ("Refer & Earn",
next to Profile/My Orders).

### Checkout — `components/Payment/NewCheckout.jsx`
- New effect (next to the existing `hasMounted` effects) prefills the
  `couponCode` field from `localStorage` **only if the field is still empty** —
  it doesn't auto-apply/auto-submit, just fills the input like you asked
  ("automatically append that key ... in the field"). The customer still hits
  Apply, and the existing `/coupon/validate` flow runs unchanged.
- `clearCheckoutState()` (already called on every successful order — COD and
  Razorpay) now also calls `clearStoredReferralCode()`, so a used code can't
  silently apply to a later order.
- Self-referral errors surface through the existing generic error toast in
  `applyCoupon()`/order-submit error handling — no special-casing needed, the
  backend messages already read fine to an end user.

### Bug fixed: referral code never survived to the checkout page
Two compounding bugs meant the `?ref=CODE` was silently lost before
`ReferralCapture` could ever store it:
1. **Backend** — `ReferralController::generate` built `share_url` as
   `/checkout?ref=CODE`, but the live checkout flow (with the coupon field) is
   `app/new-checkout/`, not `app/checkout/`. Fixed to point at `/new-checkout`.
2. **Frontend** — `app/checkout/page.js` was a Next.js **server-side**
   `redirect('/new-checkout')` with no query string forwarding. A server
   redirect happens before the client ever hydrates, so even visiting the old
   `/checkout?ref=CODE` URL directly dropped `ref` entirely on the bounce to
   `/new-checkout` — `ReferralCapture` never saw the param, so nothing was
   ever written to `localStorage`. Fixed to forward the full query string:
   `redirect(`/new-checkout?${new URLSearchParams(searchParams)}`)`.

With both fixed, opening a referral link (even with an empty cart, which
still correctly shows "cart is empty" / redirects to browse) captures the
code into `localStorage` immediately, and it's still there — and prefills the
`new-checkout` coupon field — once the customer comes back after adding items.

### Bug fixed: `/referral` was missing the site Navbar/Footer
`app/referral/layout.tsx` was modeled on `app/profile/layout.tsx`, which
redeclares its own `<html><body>{children}</body></html>`. In the App Router
only the true root layout (`app/layout.js`) may render `<html>`/`<body>` —
that's where `Navbar` and `Footer` actually live, wrapping `{children}`. A
nested route layout that also renders `<html>/<body>` replaces that tree for
its segment, so `/referral` rendered standalone with no header/footer. Fixed
by having `app/referral/layout.tsx` just return `children` (metadata export
unchanged). **Note:** `app/profile/layout.tsx` has this identical bug (it's
where the pattern was copied from) — not fixed here since it's pre-existing
and outside what was asked, but worth the same one-line fix if `/profile` has
the same missing-header/footer symptom.

### Verified
- `npm run build` compiles cleanly; `/referral` is listed as a route with no
  new errors (only the same pre-existing `exhaustive-deps` warning pattern
  already present on `/profile`, `/orders/[uuid]`, etc.).

### Pre-existing FE bug fixed: coupon discount preview used wrong type values
`NewCheckout.jsx`'s `applyCoupon()` treated coupon `type === 1` as percentage
and `type === 2` as fixed, for the **client-side discount preview only**. That
didn't match the `CouponType` enum (`0 = Percentage, 1 = FixedAmount`) that
`CouponController::validateCoupon` actually returns, so the "Coupon applied"
toast was showing the wrong preview amount for both coupon types (the actual
charged amount was always correct, since that's computed server-side).
Fixed to check `type === 0` for percentage / `type === 1` for fixed, matching
the backend enum. Verified with `npm run build`.
