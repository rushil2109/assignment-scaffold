## ADDED Requirements

### Requirement: Full lifecycle test
Tests SHALL verify the complete pipeline: createMember → addTransactions → setDailyUnitPrices → moveDayForward → getHoldings with exact value assertions.

#### Scenario: End-to-end holdings calculation
- **WHEN** a member is created, transactions added, prices set, and day advanced
- **THEN** getHoldings returns mathematically correct units and balance values

### Requirement: createMember idempotency test
Tests SHALL verify duplicate userId returns same IDs without error.

#### Scenario: Same userId twice
- **WHEN** createMember is called twice with identical userId
- **THEN** both responses have ok: true with matching memberId and accountId

### Requirement: updateMember persistence test
Tests SHALL verify updates are reflected in subsequent reads.

#### Scenario: Update then read
- **WHEN** updateMember changes email, then member data is read
- **THEN** the new email is persisted (verifiable via getHoldings or another endpoint that returns member context)

### Requirement: setInvestmentProfile reflected in portfolio
Tests SHALL verify profile changes are visible via getInvestmentPortfolio.

#### Scenario: Change profile then read
- **WHEN** setInvestmentProfile is called with new allocations
- **THEN** getInvestmentPortfolio returns the new allocations

### Requirement: Transaction history date filtering
Tests SHALL verify fromDate and toDate filtering works correctly.

#### Scenario: Filtered results match expected range
- **WHEN** transactions exist across multiple dates and getTransactionHistory is filtered
- **THEN** only transactions within the inclusive range are returned

### Requirement: Holdings asOfDate semantics
Tests SHALL verify asOfDate returns correct historical data and omitted returns latest.

#### Scenario: Historical vs latest
- **WHEN** multiple days have been processed
- **THEN** asOfDate returns that day's snapshot, and omitted returns the most recent

### Requirement: resetSubjectState enables re-creation
Tests SHALL verify the full reset → recreate cycle.

#### Scenario: Reset and recreate
- **WHEN** a member is created, reset, then created again
- **THEN** the second creation succeeds with new IDs

### Requirement: Audit trail structure verification
Tests SHALL verify getRequestAudit returns correct structure after mutations.

#### Scenario: Audit after createMember
- **WHEN** createMember is called and getRequestAudit is queried with the operationId
- **THEN** the audit contains operation name, status: success, and events with correct types

### Requirement: listAuditEvents ordering
Tests SHALL verify events are ordered by sequence across multiple operations.

#### Scenario: Multiple operations produce ordered events
- **WHEN** createMember then updateMember are called, then listAuditEvents is queried
- **THEN** events from both operations appear in insertion order

### Requirement: All responses use camelCase
Tests SHALL assert specific camelCase key names in every response.

#### Scenario: Key validation
- **WHEN** any endpoint responds
- **THEN** response keys like memberId, accountId, operationId, assetCode, effectiveDate are camelCase (not snake_case)
