## Context

moveDayForward is the "settlement engine" — it turns raw transactions into portfolio holdings. It runs once per simulated day, processing all eligible accounts. The algorithm must be deterministic: same inputs always produce same outputs, regardless of call order.

## Goals / Non-Goals

**Goals:**
- Correct daily holdings calculation matching the invariants
- Deterministic output for any given set of inputs
- Guard against double-processing
- Support multi-day advancement (days > 1 loops day-by-day)
- Unit price carry-forward logic

**Non-Goals:**
- Performance optimization (exercise scale is small)
- Parallel processing of accounts
- Partial-day processing

## Decisions

**Process day-by-day in a loop**
Even when days > 1, process sequentially: day N must complete before day N+1 (because day N+1's "previous units" depend on day N's results). Rationale: correctness over performance.

**Eligible accounts: have prior holdings OR transactions on that day**
An account enters the processing pipeline if it has at least one holdings row in history (carry forward) or has transactions effective on the current processing day (new activity). Rationale: avoids processing dormant accounts with no history.

**Rounding rule: floor each allocation to 2dp, remainder to last asset**
Given net cash flow C and N allocations, compute `floor(C * percentage / 100, 2)` for first N-1 assets. Last asset gets `C - sum(first N-1 allocations)`. This ensures `sum(allocations) == C` exactly. Rationale: prevents rounding drift while keeping a simple, deterministic rule.

**Unit price carry-forward**
If no price exists for an asset on a given day, use the most recent prior price. If no price has ever been set, use 1.0. Rationale: markets don't always have daily pricing; carry-forward is standard practice.

**Double-processing guard**
Before processing day D, check if D <= system_state.current_date. If so, skip. Only process days strictly after current_date. After processing, advance current_date. Rationale: prevents duplicate holdings rows on re-invocation.

**Holdings snapshot is upsert-like (but should not exist)**
Since we guard against double-processing, we should never hit a duplicate (account_id, asset_code, effective_date). Use insert (not upsert) and let the unique constraint catch bugs. Rationale: fail-fast on invariant violation.

## Risks / Trade-offs

- [Sequential day processing] → Slow for large `days` values. Acceptable at exercise scale.
- [Floor rounding + remainder to last] → "Last asset" depends on ordering. Use sorted asset_code order for determinism.
- [Carry-forward default 1.0] → If harness never sets prices and expects 0, this breaks. But spec says default is 1.0.
