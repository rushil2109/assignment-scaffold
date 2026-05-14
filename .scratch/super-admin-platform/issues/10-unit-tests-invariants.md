Status: ready-for-agent

# Unit tests: invariants

## What to build

Unit tests that prove key invariants by calling MockAdminApi methods directly (no HTTP). Test the math and domain logic in isolation.

Invariants to cover:
- Allocation cash flow split sums exactly to net cash flow (rounding rule)
- `new_units = previous_units + allocated_cashflow / unit_price`
- `balance = units * unitPrice`
- Member creation does not create duplicates (idempotent on userId)
- Investment profile allocations validated (sum 100.00, valid codes, no dupes, 2dp)
- Profile change affects future days only (past holdings unchanged)
- moveDayForward is guarded against double-processing same day
- moveDayForward is deterministic (same inputs → same outputs)

## Acceptance criteria

- [ ] Each invariant has at least one dedicated test
- [ ] Tests run via `make test` (phpunit Unit suite)
- [ ] Tests use real MySQL with RefreshDatabase trait
- [ ] Allocation rounding test uses fractional percentages (e.g. 33.33/33.33/33.34)
- [ ] Profile change test processes days before and after change, verifies past holdings untouched

## Blocked by

- 06-mock-control-move-day-forward
