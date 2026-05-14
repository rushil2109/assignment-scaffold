# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project

Superannuation platform exercise — a Laravel 10 backend exposing three RPC-style HTTP API surfaces on port 9001:

- **Public API** (`POST /public/*`) — member-facing CRUD and reads (6 methods)
- **Mock Control API** (`POST /mock/*`) — test harness injects transactions, prices, advances time (4 methods)
- **Inspection API** (`POST /inspection/*`) — audit trail queries (2 methods)

Behind the Public API sits an **Admin API boundary** — a PHP interface (not HTTP) representing vendor communication. The mock control API drives a concrete mock implementation of that interface. Data flows through the same boundary regardless of entry point.

## Commands

All commands run inside the Docker container via `make` targets from `super-admin/`:

```bash
docker compose up --build          # start system (app on :9001, mysql on :3307)
make bootstrap                     # prepare database (runs php artisan app:bootstrap)
make clean                         # reset database (runs php artisan app:clean)
make test                          # run full test suite
make pint                          # lint/format with Laravel Pint
```

Run a single test:
```bash
docker compose exec app php artisan test --filter=TestClassName
docker compose exec app php artisan test --filter=test_method_name
```

Run a specific test suite:
```bash
docker compose exec app php artisan test --testsuite=Unit
docker compose exec app php artisan test --testsuite=Feature
```

## Development Setup

- Volume mount maps local `super-admin/` into the container — code changes reflect immediately without rebuild
- Rebuild only needed when `composer.json` or `Dockerfile` changes
- Frontend (sample-frontend/) runs separately on :5173 with Vite proxy to :9001

## Architecture Rules

**All JSON uses camelCase keys.** The spec contract is TypeScript-style camelCase throughout. Laravel's snake_case conventions do not apply to API responses.

**RPC-style, not REST.** Every endpoint is `POST`, accepts a JSON body, returns a JSON object with `ok: boolean` and either data or `error`.

**Admin API boundary is an interface, not HTTP.** The system binds a PHP interface (e.g., `AdminApiInterface`) in the service container. The mock implementation is the concrete class. Public API controllers receive this via constructor injection — never call the mock directly.

**ID mapping.** The admin-side has its own internal identifier (`adminId`). The public API never exposes it. A mapping table translates between `userId`/`memberId` and `adminId`.

**Deterministic processing.** `moveDayForward` triggers daily holdings calculation:
1. Get active investment profile for each account
2. Collect transactions effective that day
3. Compute net daily cash flow
4. Allocate cash flow by profile percentages across asset classes
5. Divide by unit prices to get units
6. Persist holdings snapshot

**Audit trail.** Every public mutation returns an `operationId`. The audit system logs sequenced events queryable via the inspection API. Audit is a platform concern, not a vendor concern.

**No duplicate members.** `createMember` must be idempotent on `userId`.

**Investment profile changes affect future only.** Past holdings snapshots are never rewritten.

## Implementation Phases

Phase 1: Database schema, models, Admin API interface + mock implementation, bootstrap/clean commands
Phase 2: Public API (6 endpoints) + Mock Control API (4 endpoints) with audit trail
Phase 3: Inspection API (2 endpoints) + invariant tests + E2E verification

After each phase, test the service against the sample frontend and curl.

## Asset Codes

The only valid investment asset codes: `Cash`, `Conservative`, `Balanced`, `Growth`, `HighGrowth`

## Tech Stack

- PHP 8.2, Laravel 10, MySQL 8.0
- PHPUnit 10.1 (suites: Unit, Feature)
- Laravel Pint (preset: laravel)
- Docker: php:8.2-cli image + composer:2

## Key Constraints

- All three API surfaces on a single port (9001), single Laravel process with three route groups
- Constructor injection for the Admin API boundary — no Facades for the boundary
- The marking harness expects: `docker compose up --build` then system ready on :9001
- Allocations must sum to 100% (document rounding rule)
- `moveDayForward` must be guarded against double-processing the same day
- `balance = units * unitPrice` (invariant)
- `new_units = previous_units + allocated_cashflow / unit_price` (invariant)
