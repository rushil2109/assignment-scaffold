## ADDED Requirements

### Requirement: addTransactions endpoint
The system SHALL expose `POST /mock/addTransactions` accepting `{ userId, accountId, transactions: [{type, amount, effectiveDate}] }` and returning `{ ok: true, addedCount }`.

#### Scenario: Add multiple transactions
- **WHEN** a POST is made with 3 transactions
- **THEN** all 3 are persisted with generated transactionIds and addedCount is 3

#### Scenario: Transactions visible via getTransactionHistory
- **WHEN** transactions are added via mock endpoint
- **THEN** they appear in AdminApiInterface::getTransactionHistory for that member

### Requirement: Server-generated transaction IDs
Each transaction SHALL receive a server-generated UUID as its transactionId.

#### Scenario: Unique IDs generated
- **WHEN** multiple transactions are added in one request
- **THEN** each has a distinct UUID transactionId

### Requirement: No audit trail for mock operations
Mock control endpoints SHALL NOT create audit_operations or audit_events.

#### Scenario: Audit tables unchanged
- **WHEN** addTransactions is called
- **THEN** audit_operations and audit_events tables have no new rows
