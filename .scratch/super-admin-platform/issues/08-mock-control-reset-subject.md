Status: ready-for-agent

# Mock Control: resetSubjectState

## What to build

Implement `POST /mock/resetSubjectState`. Deletes all vendor-side data for the given userId (member, account, profiles, transactions, holdings) but preserves audit operations and events. After reset, the harness can call createMember again for that userId.

## Acceptance criteria

- [ ] `POST /mock/resetSubjectState` accepts `{ userId }`, returns `{ ok: true }`
- [ ] Deletes: member, account, investment profiles, transactions, holdings
- [ ] Preserves: audit_operations and audit_events for that userId
- [ ] After reset, createMember with same userId succeeds (creates fresh member)
- [ ] Resetting a non-existent userId returns `{ ok: true }` (no error)

## Blocked by

- 03-public-api-create-member-audit
