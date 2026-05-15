## Why

The holdings calculation has critical mathematical invariants that must be proven correct. Unit tests calling MockAdminApi directly (no HTTP layer) verify the domain logic in isolation. These are the safety net for the most complex algorithm in the system.

## What Changes

- Unit tests proving allocation cash flow split sums exactly to net cash flow
- Unit tests proving `new_units = previous_units + allocated_cashflow / unit_price`
- Unit tests proving `balance = units * unitPrice`
- Test idempotency of member creation
- Test investment profile allocation validation
- Test profile change affects future only (past holdings unchanged)
- Test moveDayForward double-processing guard
- Test determinism (same inputs → same outputs)
- All tests use real MySQL with RefreshDatabase trait

## Capabilities

### New Capabilities
- `unit-tests-invariants`: PHPUnit Unit suite testing domain logic invariants directly

### Modified Capabilities

(none)

## Impact

- Creates test files in `tests/Unit/`
- No changes to application code — read-only verification
- Exercises MockAdminApi and moveDayForward logic directly
