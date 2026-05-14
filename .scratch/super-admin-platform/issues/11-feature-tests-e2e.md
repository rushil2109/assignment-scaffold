Status: ready-for-agent

# Feature tests: blackbox E2E flows

## What to build

Feature tests that exercise the full system through HTTP (Laravel's postJson), simulating what the marking harness does. No knowledge of internals — just request/response verification.

Scenarios to cover:
- Full lifecycle: createMember → addTransactions → setDailyUnitPrices → moveDayForward → getHoldings (verify calculated values)
- createMember idempotency (same userId twice returns same IDs)
- updateMember → verify change persists via subsequent read
- setInvestmentProfile → verify getInvestmentPortfolio reflects change
- getTransactionHistory with date filtering
- getHoldings with asOfDate
- resetSubjectState → verify createMember works again
- getRequestAudit → verify audit trail structure for a mutation
- listAuditEvents → verify ordering across multiple operations

## Acceptance criteria

- [ ] Each scenario has a dedicated test method
- [ ] Tests run via `make test` (phpunit Feature suite)
- [ ] Tests use real MySQL with RefreshDatabase trait
- [ ] Tests call bootstrap setup (system_state seeded)
- [ ] Full lifecycle test verifies holdings math end-to-end
- [ ] All responses validated against canonical contract shapes (camelCase, correct fields)

## Blocked by

- 07-public-api-read-endpoints
- 09-inspection-api
