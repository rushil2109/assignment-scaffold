## Context

These endpoints complete the public API's 6-method surface. They follow the same pattern as mutation endpoints (resolve userId, call interface) but skip audit. The AdminApiInterface methods for these reads already exist from issue 02.

## Goals / Non-Goals

**Goals:**
- Three working read endpoints matching canonical contract shapes
- Correct camelCase key formatting in responses
- Date filtering on transaction history
- asOfDate semantics on holdings (latest if omitted, empty if no data)
- Deterministic ordering

**Non-Goals:**
- Pagination (not in spec)
- Caching
- Audit trail (reads don't audit)

## Decisions

**No audit for reads**
Read operations are not logged. Rationale: spec explicitly states no audit trail for read operations; keeps audit focused on mutations.

**Transaction ordering: effectiveDate asc, id asc**
Deterministic sort even when multiple transactions share a date. Rationale: spec requires deterministic ordering; id as tiebreaker is stable.

**getHoldings with no asOfDate returns latest processed day**
Query the max effective_date in holdings for that account. If no holdings exist at all, return empty array. Rationale: spec defines this behavior explicitly.

**getHoldings with asOfDate that has no data returns empty array**
Not an error — just no data for that date. Return `{ ok: true, holdings: [] }`. Rationale: spec says empty results are valid, not errors.

**Member not found returns error**
All three endpoints check userId resolution first. If member doesn't exist, return `{ ok: false, error }`. Rationale: consistent with mutation endpoints.

## Risks / Trade-offs

- [No pagination] → Large transaction histories could be slow. Acceptable at exercise scale.
- [Latest date logic] → If holdings table is empty for a member, MAX returns null. Handle gracefully with empty array.
