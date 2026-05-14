## ADDED Requirements

### Requirement: Bootstrap command creates database schema
The system SHALL provide `php artisan app:bootstrap` that runs all migrations and seeds the system_state table with current_date = 2024-01-01.

#### Scenario: Fresh database bootstrap
- **WHEN** `php artisan app:bootstrap` is run on a fresh database
- **THEN** all 9 tables are created and system_state contains one row with current_date = 2024-01-01

#### Scenario: Idempotent bootstrap
- **WHEN** `php artisan app:bootstrap` is run a second time
- **THEN** the command completes without error and the database state is unchanged

### Requirement: Clean command resets all data
The system SHALL provide `php artisan app:clean` that disables FK checks, truncates all 9 tables, re-enables FK checks, and inserts the system_state row with current_date = 2024-01-01.

#### Scenario: Clean after populated database
- **WHEN** `php artisan app:clean` is run on a database with member/transaction/holdings data
- **THEN** all tables are empty except system_state which has one row with current_date = 2024-01-01

#### Scenario: Idempotent clean
- **WHEN** `php artisan app:clean` is run on an already-clean database
- **THEN** the command completes without error and system_state has current_date = 2024-01-01

#### Scenario: Clean preserves schema
- **WHEN** `php artisan app:clean` is run
- **THEN** all table structures remain intact (only data is removed)
