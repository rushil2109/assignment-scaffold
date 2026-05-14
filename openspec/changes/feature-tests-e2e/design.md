## Context

These tests mirror what the marking harness does: POST JSON requests and verify response shapes. They know nothing about internals — only the API contract. They validate camelCase keys, correct response structures, and calculated values.

## Goals / Non-Goals

**Goals:**
- Complete blackbox coverage of all 12 endpoints
- Verify response shapes match canonical contract
- Verify calculated holdings values are mathematically correct
- Verify audit trail structure
- All tests pass via `make test`

**Non-Goals:**
- Testing internal method calls (unit test scope)
- Performance benchmarks
- Negative testing beyond basic error cases (harness tests will be more exhaustive)

## Decisions

**One test class per major flow**
Group related assertions into scenario-based test classes rather than one-test-per-endpoint. Rationale: flows often depend on prior state (create before read).

**RefreshDatabase + system_state seed in setUp**
Each test class starts with clean DB and seeded system_state. Rationale: isolated, deterministic tests.

**Use postJson helper**
Laravel's `$this->postJson('/path', $data)` for all requests. Rationale: in-process testing, no external HTTP needed.

**Full lifecycle test verifies holdings math**
The primary integration test sets up known inputs (transactions, prices, profile) and asserts exact holdings output. Rationale: proves the complete pipeline produces correct numbers.

**Validate all response keys are camelCase**
Assert specific keys exist (not just values). Rationale: contract verification — snake_case would be a bug.

## Risks / Trade-offs

- [Depends on all endpoints] → Can't run until all issues complete. Acceptable: this is the final validation layer.
- [Stateful test ordering within a class] → Some tests depend on prior test state. Use `@depends` annotation or order within a single test method. Prefer single-method scenarios.
