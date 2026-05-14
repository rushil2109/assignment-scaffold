## Why

The test harness needs to reset a single member's state without affecting other members or wiping the entire database. This allows re-running createMember for the same userId in test scenarios. Audit records must be preserved for inspection after reset.

## What Changes

- Implement `POST /mock/resetSubjectState` accepting `{ userId }` and returning `{ ok: true }`
- Delete all vendor-side data for that userId: member, account, investment profiles, transactions, holdings
- Preserve audit_operations and audit_events for that userId (they survive reset)
- After reset, createMember with same userId succeeds as if the member never existed
- Non-existent userId is not an error (returns ok: true)

## Capabilities

### New Capabilities
- `mock-reset-subject-state`: Endpoint to delete a single member's vendor-side data while preserving audit

### Modified Capabilities

(none)

## Impact

- Adds resetSubjectState method to MockControlController
- Adds `POST /mock/resetSubjectState` route
- Relies on FK cascade deletes (deleting member cascades to account → profiles/transactions/holdings)
