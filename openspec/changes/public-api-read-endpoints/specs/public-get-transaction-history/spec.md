## ADDED Requirements

### Requirement: getTransactionHistory endpoint
The system SHALL expose `POST /public/getTransactionHistory` accepting `{ userId, memberId, accountId, fromDate?, toDate? }` (userId, memberId, accountId required) and returning `{ ok: true, transactions: [{transactionId, type, amount, effectiveDate}] }`, matching the canonical `GetTransactionHistoryInput`/`GetTransactionHistoryOutput`.

#### Scenario: All transactions (no filter)
- **WHEN** getTransactionHistory is called without date filters
- **THEN** all transactions for the member are returned ordered by effectiveDate asc, id asc

#### Scenario: Filter by fromDate only
- **WHEN** getTransactionHistory is called with fromDate
- **THEN** only transactions with effectiveDate >= fromDate are returned

#### Scenario: Filter by toDate only
- **WHEN** getTransactionHistory is called with toDate
- **THEN** only transactions with effectiveDate <= toDate are returned

#### Scenario: Filter by both dates
- **WHEN** getTransactionHistory is called with fromDate and toDate
- **THEN** only transactions within the inclusive range are returned

#### Scenario: No transactions in range
- **WHEN** date filter matches no transactions
- **THEN** the system returns `{ ok: true, transactions: [] }`

#### Scenario: Member not found
- **WHEN** getTransactionHistory is called with a userId that doesn't exist
- **THEN** the system returns `{ ok: false, error: "..." }`

#### Scenario: Missing memberId or accountId
- **WHEN** getTransactionHistory is called without memberId or accountId
- **THEN** the system returns `{ ok: false, error: "..." }`

### Requirement: Deterministic ordering
Transactions SHALL be ordered by effectiveDate ascending, then by id ascending as tiebreaker.

#### Scenario: Multiple transactions same date
- **WHEN** two transactions exist for the same effectiveDate
- **THEN** they are ordered by id ascending (insertion order preserved)
