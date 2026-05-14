## Context

The Laravel 10 scaffold has no database schema. All platform features (member management, transactions, holdings, audit) require these 9 tables. The Docker Compose environment provides MySQL 8.0 on port 3307 (internal 3306). The `Makefile` already delegates `bootstrap` and `clean` targets to artisan commands that don't yet exist.

## Goals / Non-Goals

**Goals:**
- Single migration file defining all 9 tables with proper types, constraints, and indexes
- `app:bootstrap` command that idempotently prepares the database
- `app:clean` command that idempotently resets all data
- Schema supports all downstream features without future migrations needed

**Non-Goals:**
- Seeding test data (that's mock control's job)
- Multiple migrations or versioned schema changes
- Model creation (may be done here for convenience but not required)

## Decisions

**Single migration file over multiple files**
All 9 tables are created in one migration. Rationale: the schema is designed holistically, there's no incremental evolution, and a single file is easier to reason about for an exercise.

**UUID primary keys stored as CHAR(36)**
All entity IDs (memberId, accountId, adminId, operationId, transactionId) are UUIDs stored as `CHAR(36)`. Rationale: IDs are generated application-side, must be globally unique, and appear in API responses. No auto-increment needed.

**system_state as a single-row table**
`system_state` holds `current_date` (DATE) representing the simulated clock. Only one row ever exists (id=1). `moveDayForward` advances this. Rationale: simpler than config or cache — queryable, transactional, visible.

**investment_profiles append-only with is_current flag**
Profiles are never deleted or updated in place. A new row is inserted with `is_current = true` and the old row flipped to `is_current = false`. Rationale: historical profiles must be preserved for audit and holdings integrity.

**holdings table stores daily snapshots**
One row per account × asset_code × date. Rationale: enables point-in-time queries without recomputation.

**Cascade deletes on member FK chain**
`members → accounts → investment_profiles/transactions/holdings` all cascade. Rationale: `resetSubjectState` needs to delete a member and have everything clean up. Audit tables do NOT cascade (they survive member deletion).

## Risks / Trade-offs

- [Single migration] → If schema needs tweaking, must edit one large file. Mitigated by exercise scope being fixed.
- [CHAR(36) UUIDs] → Slightly larger indexes than binary(16). Acceptable for exercise scale; readable in debugging.
- [Cascade deletes] → Accidental member deletion cascades widely. Mitigated by no delete endpoint in public API; only mock control uses this path.
