## Context

The inspection API is a third route group alongside public and mock. It provides read-only access to the audit trail. It doesn't require AdminApiInterface — it queries audit tables directly. No audit trail is written for inspection queries.

## Goals / Non-Goals

**Goals:**
- Two endpoints matching canonical contract shapes
- Events ordered by id (insertion order = sequence)
- Proper error handling for missing operations
- camelCase responses

**Non-Goals:**
- Pagination
- Date filtering on events
- Write operations
- Audit trail for inspection queries

## Decisions

**Separate InspectionController**
Not on PublicApiController or MockControlController. Rationale: distinct route prefix, distinct responsibility, clean separation.

**Events ordered by id, not created_at**
Auto-increment id provides guaranteed insertion order. created_at could have ties. Rationale: deterministic ordering.

**getRequestAudit returns null/error for missing operationId**
If the operationId doesn't exist for that userId, return `{ ok: false, error }`. Rationale: spec says return error if not found.

**listAuditEvents returns flat list**
Not grouped by operation — just all events for that user ordered by id. Rationale: spec explicitly says flat list.

**`at` field is created_at formatted as ISO 8601**
The `at` field in events maps to `created_at` timestamp, formatted as `Y-m-d\TH:i:s\Z` or similar ISO string. Rationale: canonical contract uses `at` for the timestamp.

## Risks / Trade-offs

- [No userId validation] → listAuditEvents for non-existent user returns empty array. This is valid behavior, not an error.
- [Flat event list] → For users with many operations, this could be large. Acceptable at exercise scale.
