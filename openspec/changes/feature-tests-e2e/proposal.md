## Why

Feature tests exercise the entire system as a black box through HTTP, simulating exactly what the marking harness does. They verify the full vertical slice: routing → controller → service → database → response formatting. These catch integration bugs that unit tests miss.

## What Changes

- Full lifecycle test: createMember → addTransactions → setDailyUnitPrices → moveDayForward → getHoldings (verify calculated values)
- createMember idempotency test (same userId twice)
- updateMember + verify persistence via read
- setInvestmentProfile + verify via getInvestmentPortfolio
- getTransactionHistory with date filtering
- getHoldings with asOfDate
- resetSubjectState + verify createMember works again
- getRequestAudit structure verification
- listAuditEvents ordering across operations
- All tests use Laravel's postJson (no external HTTP client)

## Capabilities

### New Capabilities
- `feature-tests-e2e`: PHPUnit Feature suite with blackbox HTTP tests covering all endpoints

### Modified Capabilities

(none)

## Impact

- Creates test files in `tests/Feature/`
- No changes to application code
- Depends on all endpoints being implemented (issues 03-09)
