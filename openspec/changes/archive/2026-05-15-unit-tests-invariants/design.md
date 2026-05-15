## Context

These tests verify invariants at the service/model layer, not at the HTTP layer. They instantiate MockAdminApi directly or call moveDayForward processing logic directly. Real MySQL is used (RefreshDatabase trait) to ensure queries match production behavior.

## Goals / Non-Goals

**Goals:**
- Prove mathematical invariants hold for edge cases
- Test rounding with fractional percentages (e.g., 33.33/33.33/33.34)
- Verify idempotency and guard behaviors
- Run as part of `make test` under the Unit suite

**Non-Goals:**
- Testing HTTP layer (that's Feature tests, issue 11)
- Testing response formatting/camelCase (HTTP concern)
- Full integration scenarios (Feature test scope)

## Decisions

**Direct method calls, not HTTP**
Tests call MockAdminApi methods and processing logic directly. Rationale: unit tests should isolate domain logic from HTTP framework.

**RefreshDatabase trait for real MySQL**
Tests need actual database behavior (FK constraints, unique indexes, transactions). Rationale: mocks would miss SQL edge cases.

**One test class per invariant group**
Separate test classes for: allocation math, holdings calculation, member idempotency, profile validation, profile change isolation, double-processing guard. Rationale: clear organization, focused test runs.

**Seed system_state in setUp**
Each test class seeds system_state row in setUp. Rationale: required for moveDayForward to function.

## Risks / Trade-offs

- [Real MySQL] → Slower than pure unit tests. Acceptable: invariant tests need DB accuracy.
- [Direct method calls] → Tests may break if internal method signatures change. Acceptable: these are testing the core engine, which is stable.
