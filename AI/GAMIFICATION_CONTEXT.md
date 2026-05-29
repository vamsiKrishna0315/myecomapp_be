# Gamification Context

Last updated: 2026-03-16

## Package

- qcod/laravel-gamify ^1.0

## Functional Scope

Gamification is targeted at store vendors and includes:
- Reputation points
- Badge awarding
- Leaderboard and history endpoints

## API Endpoints

Under /api/v1/vendor/gamification:
- GET /profile
- GET /leaderboard
- GET /points
- GET /badges

Controller:
- app/Http/Controllers/Api/V1/VendorGamificationController.php

## Awarding Logic

Observer:
- app/Observers/StoreVendorOrdersObserver.php

Point classes:
- app/Gamify/Points/OrderCreatedPoint.php
- app/Gamify/Points/OrderCompletedPoint.php
- app/Gamify/Points/HighValueOrderPoint.php
- app/Gamify/Points/DailyStreakPoint.php
- app/Gamify/Points/OrderStatusPoint.php

Current behavior:
- Order creation can award creation points.
- Order completion can award completion points.
- High-value orders can add bonus points.
- Badge sync is driven by gamification badge rule records from database.

## Data Model Touchpoints

- users.reputation
- reputations
- badges
- user_badges
- gamification_point_rules
- gamification_badge_rules
- store_vendor_orders

## Documentation Drift to Watch

- Some docs mention class-based badges in app/Gamify/Badges as active source.
- Current observed runtime path is DB-rule-based badge qualification.

## AI Checklist for Gamification Changes

1. Verify vendor role guard and authorization first.
2. Confirm event trigger path in observer before editing points logic.
3. Keep points idempotent around status transitions.
4. Ensure badge assignment does not duplicate pivot rows.
5. Add tests for create, complete, and high-value order cases.
