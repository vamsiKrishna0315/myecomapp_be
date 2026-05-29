# Project AI Context (Single Source for ChatGPT)

Last updated: 2026-03-16
Project root: `c:/Users/ADMIN/Herd/laravel_project`

## 1) Purpose of this file

Use this as the single context file when asking ChatGPT for fixes, features, refactors, tests, or API work in this repository.

Priority of truth:
1. Real code (`app/`, `routes/`, `database/`) is highest truth.
2. Then config/package files (`composer.json`, `package.json`).
3. Then documentation files (`*.md`) because some docs are stale.

## 2) Current stack and package schema

### Backend
- PHP: `^8.4.0`
- Laravel: `^12.33.0`
- Filament: `^4.1.9`
- Filament Shield: `^4.0.2`
- JWT auth: `tymon/jwt-auth ^2.2.1`
- Gamification: `qcod/laravel-gamify ^1.0`

### Quality and tooling
- Pest: `^4.1.2`
- Pest Browser Plugin: `^4.1.1`
- Larastan: `^3.7.2`
- Pint: `^1.25.1`
- Rector: `^2.2.2`

### Frontend
- Vite: `^7.1.12`
- Tailwind CSS: `^4.1.16`
- `@tailwindcss/vite`: `^4.1.16`
- Prettier: `^3.6.2`

## 3) Architecture snapshot

### Main domains in codebase
- Customer API and JWT auth (v1 routes).
- Orders, order statuses, order tracking, cancellations, billing.
- Store/vendor workflows.
- Driver web app (Blade + PWA assets).
- Gamification for store vendors.
- Filament admin resources.

### Core folders
- `app/Models` - business entities.
- `app/Http/Controllers/Api/V1` - API controllers.
- `app/Observers` - event-driven business actions (points/badges logic included).
- `app/Gamify/Points` - gamification point classes.
- `routes/api.php` - customer and vendor APIs.
- `routes/web.php` - web routes including driver UI routes.
- `resources/views/driver` - driver PWA Blade UI.
- `database/migrations` - schema history.

## 4) Database schema summary (high level)

This is a practical domain schema summary (not an exhaustive column-level dump):

### Identity and users
- `users`: app users, roles, vendor data, and `reputation` points.
- `customers`: customer identity/auth profile.
- `drivers`: driver records.
- `otps`: OTP verification flow.

### Catalog and store
- `categories`, `products`, `product_cuts`, `cuttype_product`.
- `stores`, `store_contact_infos`, `flash_banners`, `banners`, `meta_tags`.

### Ordering
- `orders` (with UUID support), `order_items`, `order_billing`.
- `order_statuses`, `order_status_tracking`, `order_reviews`, `order_cancellations`.
- `store_vendor_orders` (vendor-order mapping and status).
- `cart_items`, `customer_favorite_items`.

### Promotions and engagement
- `coupons`, `feedback`, `contact_inquiries`, `why_us`.

### Gamification
- `reputations`, `badges`, `user_badges`.
- Rule tables: `gamification_point_rules`, `gamification_badge_rules`.

## 5) Implemented features (verified)

## 5.1 Customer API (JWT)
- Customer OTP endpoints exist.
- Customer register/login exist.
- Protected profile/dashboard/order/cart routes exist.
- Auth middleware used for protected customer routes.

## 5.2 Vendor gamification
- Vendor endpoints exist:
  - `GET /api/v1/vendor/gamification/profile`
  - `GET /api/v1/vendor/gamification/leaderboard`
  - `GET /api/v1/vendor/gamification/points`
  - `GET /api/v1/vendor/gamification/badges`
- Points classes are present in `app/Gamify/Points`.
- Observer awards points on vendor order create/complete.
- Badge awarding currently uses DB rules (`GamificationBadgeRule`) and `QCod\Gamify\Badge` records.

## 5.3 Driver web app (PWA UI layer)
- Driver web routes exist (`/driver/login`, `/driver/otp`, `/driver/forgot-password`, `/driver/dashboard`, `/driver/orders/{orderId}`, `/driver/profile`).
- DriverController methods return Blade views.
- Driver views exist in `resources/views/driver`.
- PWA files exist: `public/manifest.json`, `public/service-worker.js`.

## 6) Planned or partially implemented items

These are documented/planned and may need backend completion:

- Driver API integration for real data (many docs still mention mock-data-first flow).
- Push notifications, websockets, and richer offline sync for driver app.
- Additional customer features mentioned in docs (wishlist/media/social auth/etc.) are not guaranteed complete.
- Gamification enhancements (icons, notifications, analytics widgets) appear planned.

## 7) Verified mistakes, drift, and fixes

Use this section as immediate cleanup backlog.

1. README merge conflict markers still present.
- Evidence: `README.md` begins with `<<<<<<< HEAD` and ends with `>>>>>>> ...`.
- Risk: confusing onboarding and broken docs quality.
- Fix:
  - Resolve conflict in `README.md`.
  - Keep one final version only.

2. Placeholder artifact in routes file.
- Evidence: `routes/api.php` contains `// ...existing code...`.
- Risk: low runtime risk, high maintainability/noise risk.
- Fix:
  - Remove placeholder comment.

3. Documentation version drift.
- Evidence:
  - Driver docs mention Laravel 10+/Tailwind 3 CDN.
  - Actual project is Laravel 12 + Tailwind 4 + Vite.
- Risk: wrong implementation guidance from AI/engineers.
- Fix:
  - Update driver docs to current stack.
  - Explicitly document build/run commands for Vite/Tailwind 4.

4. Gamification docs drift vs implementation.
- Evidence:
  - Docs mention badge classes under `app/Gamify/Badges/*`.
  - Folder exists but is empty; runtime badge awarding uses DB rules.
- Risk: contributors may try to maintain non-existent class-based badge logic.
- Fix:
  - Either create and wire badge classes, or rewrite docs to DB-rule-first design.

5. Empty/no-op migration present.
- Evidence: `database/migrations/2025_12_06_163146_make_email_and_last_name_nullable_in_customers_table.php` has empty `up()` and `down()`.
- Risk: migration history noise and confusion.
- Fix:
  - Keep for historical integrity but document as no-op, or create follow-up migration comment/documentation entry.

## 8) Working commands (practical)

- Install: `composer install` and `npm install`
- Setup env: `php artisan key:generate`
- Migrate: `php artisan migrate`
- Run app: `php artisan serve`
- Frontend dev: `npm run dev`
- Build frontend: `npm run build`
- Run targeted tests: `php artisan test --filter=<name>`
- Format PHP: `vendor/bin/pint --dirty`

## 9) AI execution checklist (for ChatGPT)

When solving a task in this repo, follow this order:

1. Read affected controller/model/request/test/route files first.
2. Prefer existing conventions (Form Requests, Eloquent relations, API v1 patterns).
3. Add or update Pest tests for every behavior change.
4. Run minimal relevant tests.
5. Format with Pint.
6. If frontend changed, run Vite build/dev checks.
7. Update docs only when behavior changed.

## 10) Reusable prompt template for ChatGPT

Copy this prompt and fill placeholders:

```md
You are helping on a Laravel 12 + Filament 4 project.

Use this context as source of truth:
- PROJECT_AI_CONTEXT.md
- Related files: <list exact file paths>

Task:
<describe the bug/feature>

Requirements:
- Follow existing project conventions.
- Use Eloquent and Form Requests where appropriate.
- Write/update Pest tests.
- Include exact file-by-file patch plan.
- Mention migration impact and API contract impact.
- Provide rollback strategy.

Output format:
1) Root cause
2) Code changes by file
3) Tests added/updated
4) Commands to run
5) Risks and follow-ups
```

## 11) Confidence note

This file is built from direct code/docs inspection and is intended to reduce wrong assumptions by AI assistants.
When conflicts exist between docs and code, trust code first.
