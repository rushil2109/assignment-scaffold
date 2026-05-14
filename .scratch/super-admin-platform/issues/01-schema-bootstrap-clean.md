Status: ready-for-agent

# Schema, bootstrap, and clean commands

## What to build

Create the full database schema (9 tables: members, accounts, investment_profiles, transactions, unit_prices, holdings, audit_operations, audit_events, system_state) as a single Laravel migration. Implement `php artisan app:bootstrap` (runs migrations, inserts system_state row with current_date = 2024-01-01) and `php artisan app:clean` (disables FK checks, truncates all tables, re-inserts system_state row).

Both commands must be idempotent — safe to run multiple times.

## Acceptance criteria

- [ ] Migration creates all 9 tables with correct columns, types, FKs, and indexes
- [ ] `make bootstrap` prepares the database from scratch
- [ ] `make clean` resets all data and restores system_state to 2024-01-01
- [ ] Both commands work when run repeatedly without error
- [ ] FK cascade deletes configured on member-dependent tables

## Blocked by

None - can start immediately
