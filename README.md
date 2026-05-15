# Assignment Scaffold

This directory contains the candidate-facing materials for the engineering systems exercise.

## Contents

- [ASSIGNMENT.md](./ASSIGNMENT.md): the full assignment brief and HTTP contract
- [sample-frontend/](./sample-frontend/): a minimal standalone Vite UX used to interact with the API as it is implemented

## Sample Frontend

The sample frontend is a separate Vite application intended to run alongside the candidate's API on `http://localhost:9001`.

It provides:

- member creation
- a member workspace showing portfolio, transactions, and holdings
- simple update-member and set-investment-profile controls
- mock control actions for adding transactions, setting prices, moving the system day forward, and resetting user state
- an admin workspace for viewing audit events and request audits for known users

The frontend expects the API described in [ASSIGNMENT.md](./ASSIGNMENT.md).

# Super Admin — Superannuation Platform API

A Laravel 10 backend exposing three RPC-style HTTP API surfaces for superannuation member management, investment processing, and audit inspection.

## Quick Start

```bash
cd super-admin
docker compose up -d --build
```

The system is ready on `http://localhost:9001` once you see "Starting server on port 9001...".

## Database Lifecycle

```bash
make bootstrap    # run migrations and seed initial system state
make clean        # truncate all tables and reset system date to 2024-01-01
```

The entrypoint runs migrations automatically on container start, so `make bootstrap` is only needed to re-seed system state after a manual reset.

## API Surfaces

All endpoints accept `POST` with a JSON body and return JSON with `ok: boolean`.

### Public API — `/public/*`

| Endpoint | Description |
|----------|-------------|
| `POST /public/createMember` | Create a member with initial investment profile |
| `POST /public/updateMember` | Update member details (email, mobile, addresses) |
| `POST /public/setInvestmentProfile` | Change investment allocation percentages |
| `POST /public/getInvestmentPortfolio` | View current active allocations |
| `POST /public/getTransactionHistory` | View transaction history (optional date filter) |
| `POST /public/getHoldings` | View processed holdings with unit prices |

Mutating methods return an `operationId` for audit inspection.

### Mock Control API — `/mock/*`

| Endpoint | Description |
|----------|-------------|
| `POST /mock/addTransactions` | Inject transactions for a member's account |
| `POST /mock/setDailyUnitPrices` | Set unit prices by asset code for a date |
| `POST /mock/moveDayForward` | Advance system date and trigger holdings processing |
| `POST /mock/resetSubjectState` | Delete all data for a user (fresh start) |

### Inspection API — `/inspection/*`

| Endpoint | Description |
|----------|-------------|
| `POST /inspection/getRequestAudit` | Get full audit trail for an operation |
| `POST /inspection/listAuditEvents` | List all audit events for a user |

## Running Tests

```bash
make test                          # full test suite (92 tests)
make test-filter filter=ClassName  # run a specific test class or method
make pint                          # lint/format check
```

## Architecture

Single Laravel process with three route groups on port 9001.

```
┌─────────────────────────────────────────────────┐
│                 Laravel (port 9001)              │
├─────────────────────────────────────────────────┤
│                                                 │
│  /public/*  ──▶  PublicApiController            │
│                        │                        │
│                        ▼                        │
│               AdminApiInterface (seam)          │
│                        │                        │
│                        ▼                        │
│  /mock/*    ──▶  MockAdminApi (concrete impl)   │
│                                                 │
│  /inspection/* ──▶ InspectionController         │
│                                                 │
└─────────────────────────────────────────────────┘
```

The `AdminApiInterface` is the vendor boundary. The public API communicates through this interface via constructor injection. The mock control API drives the concrete `MockAdminApi` implementation directly.

## CORS

Configured to allow requests from `http://localhost:5173` (the sample frontend dev server). Methods: `POST`, `OPTIONS`.

## Tech Stack

- PHP 8.2, Laravel 10, MySQL 8.0
- Docker: `php:8.2-cli` + `composer:2`
- PHPUnit 10.1, Laravel Pint

## Asset Codes

Valid investment asset codes: `Cash`, `Conservative`, `Balanced`, `Growth`, `HighGrowth`

## Key Invariants

- Allocations must sum to exactly 100%
- `new_units = previous_units + allocated_cashflow / unit_price`
- `balance = units * unitPrice`
- Profile changes affect future processing only
- `moveDayForward` is guarded against double-processing
- `createMember` is idempotent on `userId`
