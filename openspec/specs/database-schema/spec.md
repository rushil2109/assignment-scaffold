## ADDED Requirements

### Requirement: Members table
The system SHALL store members with columns: `id` (CHAR(36) PK), `user_id` (VARCHAR unique), `admin_id` (CHAR(36) unique), `email` (VARCHAR nullable), `mobile` (VARCHAR nullable), `preferred_name` (VARCHAR nullable), `residential_address` (JSON nullable), `postal_address` (JSON nullable), `created_at`, `updated_at`.

#### Scenario: Member created with unique user_id
- **WHEN** a member record is inserted with a user_id
- **THEN** the record persists with a UUID id and the user_id is enforced unique

#### Scenario: Duplicate user_id rejected
- **WHEN** a second member record is inserted with the same user_id
- **THEN** the database rejects the insert with a unique constraint violation

### Requirement: Accounts table
The system SHALL store accounts with columns: `id` (CHAR(36) PK), `member_id` (CHAR(36) FK → members.id ON DELETE CASCADE), `account_id` (CHAR(36) unique), `created_at`, `updated_at`. Each member has exactly one account.

#### Scenario: Account linked to member
- **WHEN** an account is created referencing a member_id
- **THEN** the account is persisted and queryable by member_id

#### Scenario: Member deletion cascades to account
- **WHEN** a member is deleted
- **THEN** their account is also deleted via cascade

### Requirement: Investment profiles table
The system SHALL store investment profiles with columns: `id` (BIGINT auto PK), `account_id` (CHAR(36) FK → accounts.id ON DELETE CASCADE), `asset_code` (VARCHAR), `percentage` (DECIMAL(5,2)), `is_current` (BOOLEAN default true), `effective_from` (DATE), `created_at`. Composite index on (account_id, is_current).

#### Scenario: Multiple profiles for same account
- **WHEN** a new profile is set for an account
- **THEN** old rows have is_current=false, new rows have is_current=true

#### Scenario: Account deletion cascades to profiles
- **WHEN** an account is deleted
- **THEN** all its investment profiles are also deleted

### Requirement: Transactions table
The system SHALL store transactions with columns: `id` (CHAR(36) PK), `account_id` (CHAR(36) FK → accounts.id ON DELETE CASCADE), `type` (VARCHAR — contribution/withdrawal/fee), `amount` (DECIMAL(14,2)), `effective_date` (DATE), `created_at`. Index on (account_id, effective_date).

#### Scenario: Transaction persisted with effective date
- **WHEN** a transaction is inserted
- **THEN** it is queryable by account_id and effective_date

#### Scenario: Account deletion cascades to transactions
- **WHEN** an account is deleted
- **THEN** all its transactions are also deleted

### Requirement: Unit prices table
The system SHALL store unit prices with columns: `id` (BIGINT auto PK), `asset_code` (VARCHAR), `date` (DATE), `price` (DECIMAL(10,6)), `created_at`. Unique constraint on (asset_code, date).

#### Scenario: Price upserted for asset and date
- **WHEN** a price is set for an asset_code + date that already exists
- **THEN** the existing row is updated (upsert behavior)

#### Scenario: Unique price per asset per day
- **WHEN** querying unit_prices for a specific asset_code and date
- **THEN** at most one row is returned

### Requirement: Holdings table
The system SHALL store holdings snapshots with columns: `id` (BIGINT auto PK), `account_id` (CHAR(36) FK → accounts.id ON DELETE CASCADE), `asset_code` (VARCHAR), `units` (DECIMAL(16,6)), `unit_price` (DECIMAL(10,6)), `balance` (DECIMAL(14,2)), `effective_date` (DATE), `created_at`. Unique constraint on (account_id, asset_code, effective_date).

#### Scenario: Daily snapshot persisted
- **WHEN** holdings are calculated for a day
- **THEN** one row per asset_code per account for that effective_date is stored

#### Scenario: Account deletion cascades to holdings
- **WHEN** an account is deleted
- **THEN** all its holdings snapshots are also deleted

### Requirement: Audit operations table
The system SHALL store audit operations with columns: `id` (CHAR(36) PK), `user_id` (VARCHAR), `operation` (VARCHAR), `status` (VARCHAR — success/failed), `created_at`. Index on (user_id). This table does NOT cascade on member deletion.

#### Scenario: Audit operation persists independently of member
- **WHEN** a member is deleted
- **THEN** audit_operations for that user_id remain intact

### Requirement: Audit events table
The system SHALL store audit events with columns: `id` (BIGINT auto PK), `operation_id` (CHAR(36) FK → audit_operations.id), `type` (VARCHAR), `details` (JSON nullable), `created_at`. Index on (operation_id). This table does NOT cascade on member deletion.

#### Scenario: Events linked to operation
- **WHEN** an audit event is inserted referencing an operation_id
- **THEN** it is queryable by operation_id in insertion order

### Requirement: System state table
The system SHALL store system state with columns: `id` (INT PK default 1), `current_date` (DATE NOT NULL), `created_at`, `updated_at`. Only one row ever exists (id=1).

#### Scenario: Single row enforced
- **WHEN** system_state is queried
- **THEN** exactly one row with id=1 is returned

#### Scenario: Date advances
- **WHEN** current_date is updated
- **THEN** the new date is persisted and immediately queryable
