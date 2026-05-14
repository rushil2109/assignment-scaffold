Status: ready-for-agent

# Public API: createMember + audit trail

## What to build

Implement `POST /public/createMember` end-to-end. Controller generates memberId, accountId, operationId (UUIDs), calls AdminApiInterface to create the member, sets the initial investment profile, writes audit trail (one event of type "member_created" to audit_operations + audit_events), and returns the response.

Implement AuditService with methods to start an operation, record an event, and mark complete. createMember must be idempotent on userId — second call returns existing memberId/accountId/operationId without error or new audit.

## Acceptance criteria

- [ ] `POST /public/createMember` accepts canonical contract input, returns `{ ok, memberId, accountId, operationId }`
- [ ] All JSON keys are camelCase
- [ ] Duplicate userId returns existing IDs (idempotent), no new audit
- [ ] AuditService writes to audit_operations (status: success) and audit_events (type: member_created)
- [ ] Initial investment profile is validated (sum to 100.00, valid asset codes, no duplicates, percentages up to 2dp)
- [ ] Invalid allocations return `{ ok: false, error: "..." }`

## Blocked by

- 02-admin-api-interface-and-mock
