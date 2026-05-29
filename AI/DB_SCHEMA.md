# Database Schema Context

Last updated: 2026-03-16

This is a high-level schema map for implementation planning.
Use migration files for exact column definitions and constraints.

## Core Identity

- users
- customers
- drivers
- otps

## Catalog and Store

- categories
- products
- product_cuts
- cuttype_product
- stores
- store_contact_infos
- banners
- flash_banners
- meta_tags

## Orders and Checkout

- orders (with UUID support)
- order_items
- order_billing
- order_statuses
- order_status_tracking
- order_reviews
- order_cancellations
- store_vendor_orders
- cart_items
- customer_favorite_items

## Engagement and Marketing

- coupons
- feedback
- contact_inquiries
- why_us

## Gamification

- reputations
- badges
- user_badges
- gamification_point_rules
- gamification_badge_rules
- users.reputation column

## Migration Notes

- Migration history is extensive and active through 2026.
- There is one no-op migration file:
  - database/migrations/2025_12_06_163146_make_email_and_last_name_nullable_in_customers_table.php
- Related follow-up migration performs nullable changes:
  - database/migrations/2025_12_06_163152_make_email_and_last_name_nullable_in_customers_table.php

## Schema-Safe Change Rules

1. When altering columns, preserve full previous attributes to avoid accidental drops.
2. Prefer additive migrations for risky changes.
3. Write tests that assert behavior after schema changes.
4. Validate factory and seeder compatibility after migrations.
5. Keep model casts and relationships aligned with migration updates.
