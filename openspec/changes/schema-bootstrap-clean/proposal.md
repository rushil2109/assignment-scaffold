## Why

The platform has no persistence layer. All other features (Admin API, Public API, Mock Control, Inspection) depend on a relational schema being in place. Bootstrap and clean commands are required by the marking harness to prepare and reset the database deterministically.

## What Changes

- Add a single Laravel migration creating 9 tables: `members`, `accounts`, `investment_profiles`, `transactions`, `unit_prices`, `holdings`, `audit_operations`, `audit_events`, `system_state`
- Foreign key cascade deletes on all member-dependent tables
- Appropriate indexes for query patterns (lookups by userId, adminId, accountId, date ranges)
- `php artisan app:bootstrap` command — runs migrations, seeds `system_state` with `current_date = 2024-01-01`
- `php artisan app:clean` command — disables FK checks, truncates all tables, re-seeds `system_state`
- Both commands are idempotent (safe to run multiple times without error)

## Capabilities

### New Capabilities
- `database-schema`: Full relational schema for the superannuation platform (9 tables with types, constraints, FKs, indexes)
- `bootstrap-clean-commands`: Artisan commands for database lifecycle management (bootstrap + clean)

### Modified Capabilities

(none — no existing capabilities)

## Impact

- Creates `database/migrations/` file (single migration)
- Creates `app/Console/Commands/BootstrapCommand.php` and `app/Console/Commands/CleanCommand.php`
- `make bootstrap` and `make clean` targets already exist in Docker workflow — these commands fulfil them
- All subsequent features depend on this schema being stable
