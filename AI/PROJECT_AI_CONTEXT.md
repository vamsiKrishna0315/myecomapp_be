# Project AI Context

Last updated: 2026-03-16
Project root: c:/Users/ADMIN/Herd/laravel_project

## Purpose

This folder is the AI-ready context pack for fast and accurate implementation support.

Read order:
1. AI/PROJECT_AI_CONTEXT.md
2. AI/API_CONTEXT.md
3. AI/DB_SCHEMA.md
4. AI/DRIVER_APP_CONTEXT.md
5. AI/GAMIFICATION_CONTEXT.md

Priority of truth:
1. Source code in app, routes, database
2. Configuration in composer.json and package.json
3. Documentation files, because some are stale

## Stack Snapshot

Backend:
- PHP ^8.4.0
- Laravel ^12.33.0
- Filament ^4.1.9
- Filament Shield ^4.0.2
- tymon/jwt-auth ^2.2.1
- qcod/laravel-gamify ^1.0

Quality:
- Pest ^4.1.2
- larastan/larastan ^3.7.2
- laravel/pint ^1.25.1
- rector/rector ^2.2.2

Frontend:
- Vite ^7.1.12
- Tailwind CSS ^4.1.16
- @tailwindcss/vite ^4.1.16
- Prettier ^3.6.2

## Domain Map

- Customer JWT API and OTP
- Orders, statuses, tracking, billing, cancellations
- Store/vendor workflows
- Driver web app (Blade + PWA)
- Vendor gamification
- Filament admin resources

## Verified Issues Backlog

1. README.md has unresolved merge conflict markers.
2. routes/api.php still contains placeholder comment text.
3. Driver docs mention older stack (Laravel 10 and Tailwind 3), but project uses Laravel 12 and Tailwind 4.
4. Gamification docs mention badge classes, but badge awarding currently uses DB rules.
5. One migration is a no-op and should be documented as intentional.

## Execution Rules for AI

- Prefer existing Laravel conventions and route patterns.
- Use Form Requests for validation in new endpoint work.
- Add or update Pest tests for behavior changes.
- Run focused tests first, then broader suite if needed.
- Run formatting after changes.

## Prompt Starter

Use this input format when asking any AI assistant:

Task:
- <clear bug or feature statement>

Context files:
- AI/PROJECT_AI_CONTEXT.md
- AI/API_CONTEXT.md
- AI/DB_SCHEMA.md
- AI/DRIVER_APP_CONTEXT.md
- AI/GAMIFICATION_CONTEXT.md

Output required:
1. Root cause
2. File-by-file changes
3. Tests added or updated
4. Commands to run
5. Risks and rollback plan
