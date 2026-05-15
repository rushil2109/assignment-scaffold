## Context

The schema already has CASCADE deletes on the member → account → profiles/transactions/holdings chain. Audit tables deliberately have no FK cascade to members. This means a simple `DELETE FROM members WHERE user_id = ?` cascades everything vendor-side while leaving audit intact.

## Goals / Non-Goals

**Goals:**
- Single DELETE statement triggers full vendor-side cleanup via cascades
- Audit rows (audit_operations, audit_events) preserved
- createMember works again for same userId after reset
- Non-existent userId is a no-op (not an error)

**Non-Goals:**
- Resetting audit data (explicitly preserved)
- Resetting system_state (clock is global, not per-member)
- Partial reset (profiles only, transactions only)

## Decisions

**Use FK cascades, don't manually delete**
Delete the member row; let cascades handle accounts, profiles, transactions, holdings. Rationale: simpler, less error-prone, consistent with schema design intent.

**Non-existent userId returns ok: true**
No need to check existence first. `DELETE ... WHERE user_id = ?` with 0 affected rows is fine. Rationale: idempotent design — calling reset twice is safe.

**No audit trail for mock operations**
Consistent with other mock endpoints (addTransactions, setDailyUnitPrices, moveDayForward).

## Risks / Trade-offs

- [CASCADE dependency] → If schema cascades are misconfigured, orphaned rows remain. Mitigated: verified in issue 01 schema design.
- [Global system_state] → After reset, moveDayForward still starts from current system date, not member creation date. This is by design — clock is global.
