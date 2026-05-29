# API Context

Last updated: 2026-03-16

## API Layout

Primary routes file: routes/api.php

Main groups:
- /api/v1/customer
- /api/v1/vendor

## Customer API (v1/customer)

Public endpoints:
- POST /send-otp
- POST /verify-otp
- POST /forgot-password
- POST /register
- POST /login
- GET /products
- POST /category
- GET /order-statuses
- GET /site/site-data
- GET /site/store-contact
- POST /site/contact-inquiry
- GET /products/{product}

Protected endpoints (auth:customer-api + throttle:60,1):
- POST /logout
- POST /refresh
- GET /profile
- PUT /profile
- GET /addresses
- GET /contact-inquiries
- GET /dashboard
- GET /orders
- POST /order
- GET /order/{uuid}
- GET /orders/{uuid}/track
- PUT /order/{uuid}/cancel
- POST /order-statuses/{uuid?}/remaining
- PUT /order/{uuid}/status/next-step
- GET /cart
- POST /cart
- PATCH /cart/{id}
- DELETE /cart/{id}

## Vendor API (v1/vendor)

Middleware:
- auth:api
- throttle:60,1

Gamification endpoints:
- GET /gamification/profile
- GET /gamification/leaderboard
- GET /gamification/points
- GET /gamification/badges

## Auth Notes

- Customer API protected routes use auth:customer-api.
- Vendor gamification routes use auth:api.
- JWT package installed: tymon/jwt-auth.

## Known API Documentation Drift

- Some docs still describe planned or sample driver endpoints not fully wired as production backend contracts.
- Keep route-level truth from routes/api.php as canonical.

## API Work Checklist

1. Confirm route and middleware in routes/api.php.
2. Verify controller method signature and response shape.
3. Use Form Request validation for new or changed inputs.
4. Add or update Pest tests for success, validation, and auth-failure cases.
5. Re-check rate limit and guard consistency.
