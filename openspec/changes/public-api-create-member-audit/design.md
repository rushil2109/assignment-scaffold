## Context

This is the first HTTP endpoint to go live. It establishes conventions for all public endpoints: RPC-style POST, camelCase JSON, ok/error response shape, operationId in responses, ID mapping between platform and admin layers.

## Goals / Non-Goals

**Goals:**
- Working `POST /public/createMember` matching the canonical contract
- Reusable AuditService for all future mutation endpoints
- Idempotency on userId (no duplicate members, no duplicate audit)
- Validation of investment allocations with clear error messages

**Non-Goals:**
- Other public endpoints (updateMember, setInvestmentProfile — separate issues)
- Mock control routes (separate issue)
- Inspection API (separate issue)

## Decisions

**Controller generates all platform IDs**
memberId, accountId, operationId are generated at the controller layer before calling AdminApiInterface. Rationale: the public API owns these identifiers; the admin boundary only knows about adminId.

**ID mapping via members table**
The members table already has `user_id`, `id` (memberId), and `admin_id`. The accounts table has `account_id`. No separate mapping table needed — query members by user_id to resolve adminId. Rationale: simplest approach given existing schema.

**AuditService as a stateless service class**
Methods: `startOperation(userId, operation): operationId`, `recordEvent(operationId, type, details)`, `completeOperation(operationId, status)`. Injected via constructor. Rationale: keeps audit logic out of controllers.

**Idempotency check before any write**
First thing in createMember: check if userId already exists. If yes, return existing IDs immediately. No audit, no admin API call. Rationale: simplest idempotency model — first-write-wins.

**Validation before any side effects**
Validate allocations (sum, codes, duplicates, decimal places) before calling admin API or creating audit. Return `{ ok: false, error }` immediately on failure. Rationale: no partial state on validation failure.

## Risks / Trade-offs

- [No DB transaction wrapping] → If audit write fails after member creation, orphaned member. Mitigated: wrap in DB::transaction.
- [Idempotency on userId only] → If first request partially failed (member created, profile not set), retry returns stale data. Mitigated: wrap entire flow in transaction so it's all-or-nothing.
