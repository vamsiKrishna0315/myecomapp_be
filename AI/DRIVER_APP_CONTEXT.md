# Driver App Context

Last updated: 2026-03-16

## Scope

Driver app is implemented as Laravel Blade pages with PWA assets.
UI routes are in web routes, and backend API integration is partly documented as planned.

## Route and Controller Map

Routes are defined under /driver in routes/web.php.
Controller: app/Http/Controllers/DriverController.php

Available pages:
- /driver/login
- /driver/otp
- /driver/forgot-password
- /driver/dashboard
- /driver/orders/{orderId}
- /driver/profile

## View Files

Location: resources/views/driver
- layout.blade.php
- login.blade.php
- otp.blade.php
- forgot-password.blade.php
- dashboard.blade.php
- order-detail.blade.php
- profile.blade.php

## PWA Assets

- public/manifest.json
- public/service-worker.js

## Current State

Implemented:
- Full page flow and Blade view rendering
- Driver route wiring in web routes
- PWA scaffold files present

Planned or partial:
- Live backend driver API integration
- Full production auth/token flow for driver endpoints
- Advanced realtime and notification features

## Important Drift

Some driver docs mention older frontend assumptions (Tailwind CDN style and older Laravel references).
Treat current project stack (Laravel 12 + Tailwind 4 + Vite) as canonical.

## AI Checklist for Driver Tasks

1. Confirm whether requested work is UI-only or API-backed.
2. If API-backed, define contract first and map to existing API versioning style.
3. Keep route names and Blade structure consistent.
4. If JS behavior changes, ensure PWA behavior remains stable.
5. Add tests where feasible for route/controller behavior.
