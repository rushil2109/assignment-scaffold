Status: ready-for-agent

# Inspection API: getRequestAudit + listAuditEvents

## What to build

Implement `POST /inspection/getRequestAudit` and `POST /inspection/listAuditEvents`.

getRequestAudit: given userId + operationId, return the full RequestAudit object (userId, operationId, operation, status, events[]). Events ordered by id (insertion order).

listAuditEvents: given userId, return all audit events across all operations for that user, ordered by id. Flat list (not grouped by operation).

## Acceptance criteria

- [ ] `POST /inspection/getRequestAudit` returns `{ ok, audit: RequestAudit }` matching canonical contract
- [ ] Returns `{ ok: false, error }` if operationId not found
- [ ] `POST /inspection/listAuditEvents` returns `{ ok, events: AuditEvent[] }` ordered by sequence
- [ ] Events include `at` (IsoDateTime), `type`, `details`
- [ ] Empty results return `{ ok: true, events: [] }` or `{ ok: true, audit: null }`

## Blocked by

- 03-public-api-create-member-audit
